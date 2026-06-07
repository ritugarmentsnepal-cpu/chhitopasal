<?php
$directory = new RecursiveDirectoryIterator('resources/views');
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($regex as $file) {
    $filePath = $file[0];
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $newContent = preg_replace('/dark:[a-zA-Z0-9\-\/\[\]\.:]+/i', '', $content);
        $newContent = str_replace('  ', ' ', $newContent);
        if ($newContent !== $content) {
            file_put_contents($filePath, $newContent);
            echo "Cleaned $filePath\n";
        }
    }
}
