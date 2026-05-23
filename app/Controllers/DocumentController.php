<?php
// E:\call_center\app\Controllers\DocumentController.php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Contract, Invoice, Deal, Message, PartnerDocument};

class DocumentController extends Controller
{
    // ── Contracts ─────────────────────────────────────────────────────────────

    public function uploadContract(string $dealId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $deal = (new Deal())->find((int)$dealId);
        if (!$deal) { $this->redirect(APP_URL . '/admin/deals'); return; }

        if (empty($_FILES['contract_file']['name'])) {
            Session::flash('error', 'No file selected.');
            $this->redirect(APP_URL . '/admin/deals/' . $dealId);
            return;
        }

        $file  = $_FILES['contract_file'];
        $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed)) {
            Session::flash('error', 'Invalid file type. Allowed: PDF, DOC, DOCX.');
            $this->redirect(APP_URL . '/admin/deals/' . $dealId);
            return;
        }
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            Session::flash('error', 'File too large.');
            $this->redirect(APP_URL . '/admin/deals/' . $dealId);
            return;
        }

        $uploadDir = UPLOAD_PATH . '/contracts/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = uniqid('contract_') . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            Session::flash('error', 'Failed to upload file.');
            $this->redirect(APP_URL . '/admin/deals/' . $dealId);
            return;
        }

        (new Contract())->create([
            'deal_id'       => (int)$dealId,
            'filename'      => $filename,
            'original_name' => basename($file['name']),
            'uploaded_by'   => Auth::id(),
            'notes'         => trim($_POST['notes'] ?? ''),
        ]);

        Session::flash('success', 'Contract uploaded successfully.');
        $this->redirect(APP_URL . '/admin/deals/' . $dealId);
    }

    public function downloadContract(string $id): void
    {
        Auth::requireLogin();
        $contract = (new Contract())->findWithUploader((int)$id);
        if (!$contract) { $this->redirect(APP_URL . '/admin/deals'); return; }

        $path = UPLOAD_PATH . '/contracts/' . $contract['filename'];
        if (!file_exists($path)) {
            Session::flash('error', 'File not found on server.');
            $this->back();
            return;
        }

        $mime = match(strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $contract['original_name'] . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private');
        readfile($path);
        exit;
    }

    public function deleteContract(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model    = new Contract();
        $contract = $model->findWithUploader((int)$id);
        if (!$contract) { $this->back(); return; }

        $path = UPLOAD_PATH . '/contracts/' . $contract['filename'];
        if (file_exists($path)) unlink($path);

        $model->delete((int)$id);
        Session::flash('success', 'Contract deleted.');
        $this->redirect(APP_URL . '/admin/deals/' . $contract['deal_id']);
    }

    // ── Invoices ──────────────────────────────────────────────────────────────

    public function uploadInvoice(string $dealId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $deal = (new Deal())->find((int)$dealId);
        if (!$deal) { $this->redirect(APP_URL . '/admin/deals'); return; }

        $amount    = (float)($_POST['amount'] ?? 0);
        $vatRate   = (float)($_POST['vat_rate'] ?? 24);
        $vatAmount = round($amount * $vatRate / 100, 2);
        $total     = round($amount + $vatAmount, 2);

        $data = [
            'deal_id'       => (int)$dealId,
            'invoice_no'    => trim($_POST['invoice_no'] ?? ''),
            'amount'        => $amount,
            'vat_rate'      => $vatRate,
            'vat_amount'    => $vatAmount,
            'total_amount'  => $total,
            'status'        => $_POST['status'] ?? 'draft',
            'issued_at'     => $_POST['issued_at'] ?: null,
            'due_at'        => $_POST['due_at'] ?: null,
            'notes'         => trim($_POST['notes'] ?? ''),
            'uploaded_by'   => Auth::id(),
        ];

        // Handle optional file upload
        if (!empty($_FILES['invoice_file']['name'])) {
            $file  = $_FILES['invoice_file'];
            $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'doc', 'docx']) && $file['size'] <= UPLOAD_MAX_SIZE) {
                $uploadDir = UPLOAD_PATH . '/invoices/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('inv_') . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $data['filename']      = $filename;
                    $data['original_name'] = basename($file['name']);
                }
            }
        }

        (new Invoice())->create($data);
        Session::flash('success', 'Invoice created.');
        $this->redirect(APP_URL . '/admin/deals/' . $dealId);
    }

    public function downloadInvoice(string $id): void
    {
        Auth::requireLogin();
        $invoice = (new Invoice())->findWithDetails((int)$id);
        if (!$invoice || empty($invoice['filename'])) {
            Session::flash('error', 'File not found.');
            $this->back();
            return;
        }

        $path = UPLOAD_PATH . '/invoices/' . $invoice['filename'];
        if (!file_exists($path)) {
            Session::flash('error', 'File not found on server.');
            $this->back();
            return;
        }

        $mime = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf'
            ? 'application/pdf'
            : 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . ($invoice['original_name'] ?? 'invoice.pdf') . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private');
        readfile($path);
        exit;
    }

    public function adminInvoices(): void
    {
        Auth::requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];
        $model  = new Invoice();
        $result = $model->listAll($filters, $page, 20);
        $stats  = $model->totalStats();
        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('admin.financial.invoices', $result + [
            'title'   => 'Invoices',
            'filters' => $filters,
            'stats'   => $stats,
            'unread'  => $unread,
        ]);
    }

    public function callerInvoices(): void
    {
        Auth::requireCaller();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = ['caller_id' => Auth::id(), 'status' => $_GET['status'] ?? ''];
        $model   = new Invoice();
        $result  = $model->listAll($filters, $page, 20);
        $unread  = (new Message())->unreadCount(Auth::id());

        $this->view('caller.invoices.index', $result + [
            'title'   => 'My Invoices',
            'filters' => $filters,
            'unread'  => $unread,
        ]);
    }

    public function markInvoicePaid(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $invoice = (new Invoice())->findWithDetails((int)$id);
        if (!$invoice) { $this->back(); return; }

        (new Invoice())->markPaid((int)$id);
        Session::flash('success', 'Invoice marked as paid.');
        $this->redirect(APP_URL . '/admin/deals/' . $invoice['deal_id']);
    }

    // ── Partner Documents (admin) ─────────────────────────────────────────────

    public function uploadPartnerDoc(string $partnerId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $docType = $_POST['doc_type'] ?? 'contract';
        if (!in_array($docType, ['contract', 'client_invoice'])) {
            Session::flash('error', 'Μη έγκυρος τύπος εγγράφου.');
            $this->redirect(APP_URL . '/admin/partners/' . $partnerId . '/edit');
            return;
        }

        if (empty($_FILES['doc_file']['name'])) {
            Session::flash('error', 'Δεν επιλέχθηκε αρχείο.');
            $this->redirect(APP_URL . '/admin/partners/' . $partnerId . '/edit');
            return;
        }

        $file    = $_FILES['doc_file'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed) || $file['size'] > UPLOAD_MAX_SIZE) {
            Session::flash('error', 'Μη έγκυρος τύπος ή μέγεθος αρχείου (PDF, DOC, DOCX, έως 10MB).');
            $this->redirect(APP_URL . '/admin/partners/' . $partnerId . '/edit');
            return;
        }

        $uploadDir = UPLOAD_PATH . '/partner_docs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $prefix   = $docType === 'contract' ? 'pcon_' : 'pclinv_';
        $filename = uniqid($prefix) . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            Session::flash('error', 'Αποτυχία μεταφόρτωσης αρχείου.');
            $this->redirect(APP_URL . '/admin/partners/' . $partnerId . '/edit');
            return;
        }

        (new PartnerDocument())->create([
            'partner_id'    => (int)$partnerId,
            'doc_type'      => $docType,
            'filename'      => $filename,
            'original_name' => basename($file['name']),
            'title'         => trim($_POST['title'] ?? ''),
            'amount'        => !empty($_POST['amount']) ? (float)$_POST['amount'] : null,
            'notes'         => trim($_POST['notes'] ?? ''),
            'uploaded_by'   => Auth::id(),
        ]);

        Session::flash('success', 'Το έγγραφο μεταφορτώθηκε επιτυχώς.');
        $this->redirect(APP_URL . '/admin/partners/' . $partnerId . '/edit');
    }

    public function deletePartnerDoc(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new PartnerDocument();
        $doc   = $model->find((int)$id);
        if (!$doc) { $this->back(); return; }

        $path = UPLOAD_PATH . '/partner_docs/' . $doc['filename'];
        if (file_exists($path)) unlink($path);

        $model->delete((int)$id);
        Session::flash('success', 'Το έγγραφο διαγράφηκε.');
        $this->redirect(APP_URL . '/admin/partners/' . $doc['partner_id'] . '/edit');
    }

    public function updateInvoice(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $invoice = (new Invoice())->find((int)$id);
        if (!$invoice) { $this->back(); return; }

        $amount    = (float)($_POST['amount'] ?? $invoice['amount']);
        $vatRate   = (float)($_POST['vat_rate'] ?? $invoice['vat_rate']);
        $vatAmount = round($amount * $vatRate / 100, 2);
        $total     = round($amount + $vatAmount, 2);

        $data = [
            'invoice_no'   => trim($_POST['invoice_no'] ?? ''),
            'amount'       => $amount,
            'vat_rate'     => $vatRate,
            'vat_amount'   => $vatAmount,
            'total_amount' => $total,
            'status'       => $_POST['status'] ?? $invoice['status'],
            'issued_at'    => $_POST['issued_at'] ?: null,
            'due_at'       => $_POST['due_at'] ?: null,
            'notes'        => trim($_POST['notes'] ?? ''),
        ];
        if ($data['status'] === 'paid' && !$invoice['paid_at']) {
            $data['paid_at'] = date('Y-m-d');
        }

        (new Invoice())->update((int)$id, $data);
        Session::flash('success', 'Invoice updated.');
        $this->redirect(APP_URL . '/admin/deals/' . $invoice['deal_id']);
    }
}
