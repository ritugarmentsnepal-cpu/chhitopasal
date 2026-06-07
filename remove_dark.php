<?php
$files = array_merge(
    glob('resources/views/layouts/*.blade.php'),
    ['resources/views/dashboard.blade.php']
);

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = preg_replace('/dark:[a-zA-Z0-9\-\/\[\]\.:]+/i', '', $content);
        $content = str_replace('  ', ' ', $content);
        file_put_contents($file, $content);
        echo "Cleaned $file\n";
    }
}
