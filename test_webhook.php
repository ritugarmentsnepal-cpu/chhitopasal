<?php

$ch = curl_init('https://chhitopasal.com/webhook/pathao');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['consignment_id' => '12345', 'order_status' => 'On_Hold', 'reason' => 'Customer address not found']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
echo $response;
