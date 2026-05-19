<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Business, User, Message};
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Reads only a specific row range so we don't load all 15 000+ rows into
 * memory just to show a preview.
 */
class RowRangeFilter implements IReadFilter
{
    public function __construct(
        private int $startRow = 1,
        private int $endRow   = 1
    ) {}

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}

class ImportController extends Controller
{
    /** Greek → field mapping (covers common Greek business registry exports) */
    private const GREEK_MAP = [
        'company_name' => [
            'επωνυμια','επωνυμία','εταιρεια','εταιρεία','επιχειρηση','επιχείρηση',
            'company','company name','business','business name','name',
        ],
        'contact_name' => [
            'επαφη','επαφή','υπευθυνος','υπεύθυνος','contact','contact name','person',
        ],
        'email'   => ['email','e-mail','mail','ηλεκτρονικο ταχυδρομειο','ηλεκτρονικό ταχυδρομείο'],
        'phone'   => ['τηλεφωνο','τηλέφωνο','τηλ','phone','tel','telephone','mobile'],
        'website' => ['website','web','url','ιστοτοπος','ιστότοπος','ιστοσελιδα','ιστοσελίδα'],
        'address' => ['διευθυνση','διεύθυνση','address','street','οδος','οδός'],
        'city'    => ['πολη','πόλη','city','town','δημος','δήμος','νομος','νομός'],
        'country' => ['χωρα','χώρα','country'],
        'category'=> [
            'κατηγορια','κατηγορία','αντικειμενο','αντικείμενο',
            'αντικειμενο δραστηριοτητας','αντικείμενο δραστηριότητας',
            'category','industry','sector','type','κλαδος','κλάδος',
            'αντικειμενο δραστηριοτητας ',
        ],
        'notes'   => ['σημειωσεις','σημειώσεις','notes','remarks','παρατηρησεις','παρατηρήσεις',
                      'νομικη μορφη','νομική μορφή','κατασταση','κατάσταση',
                      'γεμη','γ.ε.μη.','αφμ','α.φ.μ.','ημ/νια συστασης','ημ/νια εγγραφης στο επιμελητηριο'],
    ];

    public function index(): void
    {
        Auth::requireAdmin();
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.import.index', ['title' => 'Import Businesses', 'unread' => $unread]);
    }

    public function preview(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        if (empty($_FILES['excel_file']['name'])) {
            Session::flash('error', 'Please select a file.');
            $this->redirect(APP_URL . '/admin/import');
            return;
        }

        $file = $_FILES['excel_file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            Session::flash('error', 'Only Excel (.xlsx, .xls) and CSV files are accepted.');
            $this->redirect(APP_URL . '/admin/import');
            return;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Upload error code ' . $file['error'] . '. Check upload_max_filesize in php.ini.');
            $this->redirect(APP_URL . '/admin/import');
            return;
        }

        // Save uploaded file
        $tmpName = uniqid('import_') . '.' . $ext;
        $tmpPath = UPLOAD_PATH . '/imports/' . $tmpName;
        move_uploaded_file($file['tmp_name'], $tmpPath);

        try {
            // Read only the header row to detect columns
            $colMap      = $this->detectColumns($tmpPath, $ext);
            $totalRows   = $this->countDataRows($tmpPath, $ext);

            // Read first 200 data rows for preview (rows 2–201)
            $previewRows = $this->readRows($tmpPath, $ext, 2, 201);
            $preview     = [];

            foreach ($previewRows as $i => $row) {
                if ($this->isEmptyRow($row)) continue;
                $record    = $this->mapRow($row, $colMap);
                $rowErrors = [];
                if (empty($record['company_name'])) $rowErrors[] = 'Missing company name';
                $preview[] = ['row' => $i + 2, 'data' => $record, 'errors' => $rowErrors];
            }

        } catch (\Throwable $e) {
            @unlink($tmpPath);
            Session::flash('error', 'Could not parse file: ' . $e->getMessage());
            $this->redirect(APP_URL . '/admin/import');
            return;
        }

        $_SESSION['import_file']    = $tmpName;
        $_SESSION['import_col_map'] = $colMap;
        $_SESSION['import_total']   = $totalRows;

        $callers = (new User())->callers();
        $unread  = (new Message())->unreadCount(Auth::id());

        $this->view('admin.import.preview', [
            'title'     => 'Import Preview',
            'preview'   => $preview,
            'totalRows' => $totalRows,
            'callers'   => $callers,
            'unread'    => $unread,
            'colMap'    => $colMap,
        ]);
    }

    public function import(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $tmpName  = $_SESSION['import_file']    ?? null;
        $colMap   = $_SESSION['import_col_map'] ?? null;
        $total    = $_SESSION['import_total']   ?? 0;

        if (!$tmpName || !$colMap) {
            Session::flash('error', 'Session expired. Please re-upload the file.');
            $this->redirect(APP_URL . '/admin/import');
            return;
        }

        $tmpPath  = UPLOAD_PATH . '/imports/' . $tmpName;
        $callerId = !empty($_POST['caller_id']) ? (int)$_POST['caller_id'] : null;
        $ext      = strtolower(pathinfo($tmpName, PATHINFO_EXTENSION));

        $model    = new Business();
        $db       = \Database::getInstance();
        $imported = 0;
        $skipped  = 0;
        $batchSize = 500;    // commit every 500 rows

        // Process in batches to avoid memory exhaustion on large files
        $startRow = 2;       // row 1 = header
        $db->beginTransaction();

        try {
            while (true) {
                $endRow = $startRow + $batchSize - 1;
                $rows   = $this->readRows($tmpPath, $ext, $startRow, $endRow);

                if (empty($rows)) break;

                foreach ($rows as $row) {
                    if ($this->isEmptyRow($row)) { $skipped++; continue; }

                    $record = $this->mapRow($row, $colMap);
                    if (empty($record['company_name'])) { $skipped++; continue; }

                    $record['imported_from'] = $tmpName;
                    $record['created_by']    = Auth::id();
                    $record['status']        = 'new';

                    $bid = $model->create($record);
                    $imported++;

                    if ($callerId) {
                        $model->bulkAssign([$bid], $callerId, Auth::id());
                    }
                }

                $startRow += $batchSize;

                // Commit every batch to avoid long transactions
                $db->commit();
                $db->beginTransaction();
            }

            $db->commit();

        } catch (\Throwable $e) {
            $db->rollBack();
            Session::flash('error', 'Import failed at row ~' . $startRow . ': ' . $e->getMessage());
            $this->redirect(APP_URL . '/admin/import');
            return;
        }

        unset($_SESSION['import_file'], $_SESSION['import_col_map'], $_SESSION['import_total']);
        @unlink($tmpPath);

        Session::flash('success', "Import complete! {$imported} businesses imported, {$skipped} rows skipped.");
        $this->redirect(APP_URL . '/admin/businesses');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Read only rows $startRow to $endRow (1-indexed).
     * Uses a ReadFilter so PHPSpreadsheet never allocates the full sheet.
     */
    private function readRows(string $path, string $ext, int $startRow, int $endRow): array
    {
        if ($ext === 'csv') {
            return $this->readCsvRows($path, $startRow, $endRow);
        }

        $readerType = match($ext) { 'xlsx' => 'Xlsx', 'xls' => 'Xls', default => 'Xlsx' };
        $reader     = IOFactory::createReader($readerType);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter(new RowRangeFilter($startRow, $endRow));

        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        // Remove the header row (row 1) if it was included in the range
        if ($startRow === 1 && isset($rows[1])) {
            unset($rows[1]);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return array_values($rows);
    }

    private function readCsvRows(string $path, int $startRow, int $endRow): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        $line   = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if ($line < $startRow) continue;
            if ($line > $endRow)  break;
            // Convert to A/B/C keyed array to match spreadsheet format
            $keyed = [];
            foreach ($row as $i => $val) {
                $keyed[chr(65 + $i)] = $val;
            }
            $rows[] = $keyed;
        }
        fclose($handle);
        return $rows;
    }

    /**
     * Read just row 1 to detect column headers.
     */
    private function detectColumns(string $path, string $ext): array
    {
        $headerRows = $this->readRows($path, $ext, 1, 1);
        // readRows skips row 1 when startRow=1... so we need special handling
        // Use a different approach for the header
        if ($ext === 'csv') {
            $handle = fopen($path, 'r');
            $row    = fgetcsv($handle);
            fclose($handle);
            $header = [];
            foreach ($row as $i => $val) { $header[chr(65+$i)] = $val; }
        } else {
            $readerType = match($ext) { 'xlsx' => 'Xlsx', 'xls' => 'Xls', default => 'Xlsx' };
            $reader     = IOFactory::createReader($readerType);
            $reader->setReadDataOnly(true);
            $reader->setReadFilter(new RowRangeFilter(1, 1));
            $spreadsheet = $reader->load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);
            $header      = $rows[1] ?? [];
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }

        return $this->mapColumns($header);
    }

    /**
     * Count total data rows without loading any cell content.
     */
    private function countDataRows(string $path, string $ext): int
    {
        if ($ext === 'csv') {
            $lines = 0;
            $handle = fopen($path, 'r');
            while (fgets($handle)) $lines++;
            fclose($handle);
            return max(0, $lines - 1); // minus header
        }

        $readerType = match($ext) { 'xlsx' => 'Xlsx', 'xls' => 'Xls', default => 'Xlsx' };
        $reader     = IOFactory::createReader($readerType);
        $reader->setReadDataOnly(true);
        // Read only column A to count rows (fast)
        $reader->setReadFilter(new class implements IReadFilter {
            public function readCell(string $col, int $row, string $ws = ''): bool {
                return $col === 'A';
            }
        });
        $spreadsheet = $reader->load($path);
        $count = $spreadsheet->getActiveSheet()->getHighestRow();
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        return max(0, $count - 1); // minus header
    }

    /**
     * Map column headers (supports English and Greek) to field names.
     */
    private function mapColumns(array $headers): array
    {
        $map = [];
        foreach ($headers as $col => $rawHeader) {
            // Normalise: lowercase, trim, collapse spaces
            $h = mb_strtolower(trim((string)$rawHeader), 'UTF-8');
            $h = preg_replace('/\s+/', ' ', $h);

            foreach (self::GREEK_MAP as $field => $aliases) {
                if (in_array($h, $aliases, true) && !isset($map[$field])) {
                    $map[$field] = $col;
                    break;
                }
            }
        }
        return $map;
    }

    private function mapRow(array $row, array $colMap): array
    {
        $record = [];
        foreach ($colMap as $field => $col) {
            $record[$field] = trim((string)($row[$col] ?? ''));
        }
        return $record;
    }

    private function isEmptyRow(array $row): bool
    {
        return !array_filter($row, fn($v) => trim((string)$v) !== '');
    }
}
