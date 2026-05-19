<?php
echo 'PHP: ' . PHP_VERSION . PHP_EOL;
$needed = ['zip','xml','gd','mbstring','fileinfo','zlib','simplexml','dom'];
foreach ($needed as $ext) {
    echo str_pad($ext, 12) . ': ' . (extension_loaded($ext) ? 'OK' : '*** MISSING ***') . PHP_EOL;
}
echo PHP_EOL;
echo 'upload_max_filesize : ' . ini_get('upload_max_filesize') . PHP_EOL;
echo 'post_max_size       : ' . ini_get('post_max_size')       . PHP_EOL;
echo 'max_execution_time  : ' . ini_get('max_execution_time')  . PHP_EOL;
echo 'max_input_time      : ' . ini_get('max_input_time')      . PHP_EOL;
echo 'memory_limit        : ' . ini_get('memory_limit')        . PHP_EOL;
echo PHP_EOL;

// Try to load the user's actual Excel file
$file = 'C:/Users/PC/Downloads/ΕΠΙΧΕΙΡΗΣΕΙΣ (7).xlsx';
if (file_exists($file)) {
    echo 'Excel file found: YES (' . round(filesize($file)/1024, 1) . ' KB)' . PHP_EOL;
} else {
    echo 'Excel file found: NO at ' . $file . PHP_EOL;
    // Try to list Downloads
    $dl = 'C:/Users/PC/Downloads/';
    $files = glob($dl . '*.xlsx');
    echo 'xlsx files in Downloads:' . PHP_EOL;
    foreach ($files as $f) { echo '  ' . basename($f) . PHP_EOL; }
}
