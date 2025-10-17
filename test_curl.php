<?php
// test-curl.php
$ch = curl_init('https://newsapi.org');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
echo 'cURL errno: ' . curl_errno($ch) . "<br>";
echo 'cURL error: ' . curl_error($ch) . "<br>";
curl_close($ch);
?>
