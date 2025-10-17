<?php
$apiKey = 'edb9248c31ad4f2e9f3c892ffc90bc52';
$url = 'https://newsapi.org/v2/top-headlines?country=id&pageSize=3&apiKey=' . $apiKey;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'User-Agent: PHPTestClient/1.0',
    ],
]);
$result = curl_exec($ch);
$error = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error) {
    echo "❌ CURL Error: " . $error;
} else {
    echo "✅ HTTP Code: $code<br><br>";
    echo "<pre>";
    print_r(json_decode($result, true));
    echo "</pre>";
}
?>
