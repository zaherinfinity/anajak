<?php
$proxyUrl = 'https://www.s-server.ltd/khqr/';

$single = '274e69f1e1807ff9df1e61dc8f7a5e79';
$bulk = '8277e0910d750195b448797616e091ad:e300e2a081762cbe14a31fdd467b41e5:d926d7bb9ccf46fc04a61bd65d87b9b3'; // Bulk split by ':' with a limit of 10 per request


$reqData = ['md5' => $bulk];

$ch = curl_init($proxyUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($reqData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json, text/plain, */*',
        'Accept-Language: en-US,en;q=0.9',
        'Accept-Encoding: gzip, deflate, br',
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'sec-ch-ua: "Google Chrome";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "macOS"',
        'sec-fetch-dest: empty',
        'sec-fetch-mode: cors',
        'sec-fetch-site: same-origin'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_ENCODING => '',
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch), 'code' => curl_errno($ch)]);
    curl_close($ch);
    exit(1);
}

curl_close($ch);

header('Content-Type: application/json');
http_response_code($httpCode);
echo $response;
