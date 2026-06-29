<?php
$lines = file('storage/logs/laravel.log');
$lastLines = array_slice($lines, -100);
foreach($lastLines as $line) {
    echo $line;
}
