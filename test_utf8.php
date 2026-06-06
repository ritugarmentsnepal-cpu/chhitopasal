<?php
$invalidUtf8 = "a" . chr(0x80) . "b";

echo "Original json_encode: " . json_encode($invalidUtf8) . "\n";
echo "json_last_error_msg: " . json_last_error_msg() . "\n";

$mb = mb_convert_encoding($invalidUtf8, 'UTF-8', 'UTF-8');
echo "MB json_encode: " . json_encode($mb) . "\n";
echo "MB error: " . json_last_error_msg() . "\n";

$iconv = iconv('UTF-8', 'UTF-8//IGNORE', $invalidUtf8);
echo "ICONV json_encode: " . json_encode($iconv) . "\n";
echo "ICONV error: " . json_last_error_msg() . "\n";

$jsonOptions = json_encode($invalidUtf8, JSON_INVALID_UTF8_SUBSTITUTE);
echo "JSON_SUBSTITUTE json_encode: " . $jsonOptions . "\n";
echo "JSON_SUBSTITUTE error: " . json_last_error_msg() . "\n";
