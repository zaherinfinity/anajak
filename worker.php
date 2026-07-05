<?php
// worker.php – Proxy to KHQR API with JSON error wrapping
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$proxyUrl = 'https://www.s-server.ltd/khqr/';

// Get input from frontend
$input = json_decode(file_get_contents('php://input'), true);
$md5 = $input['md5'] ?? '';

if (empty($md5)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing md5 parameter']);
    exit;
}

$reqData = ['md5' => $md5];

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
    CURLOPT_TIMEOUT => 10,
    CURLOPT_ENCODING => '',
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);
curl_close($ch);

// If cURL error, return JSON error
if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => "cURL error: $curlError", 'code' => $curlErrno]);
    exit;
}

// If response is empty, return error
if (empty($response)) {
    http_response_code(500);
    echo json_encode(['error' => 'Empty response from upstream proxy']);
    exit;
}

// Try to decode JSON to validate
$decoded = json_decode($response, true);
if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
    // Invalid JSON – wrap raw response in error object
    http_response_code(502);
    echo json_encode([
        'error' => 'Upstream returned invalid JSON',
        'raw' => substr($response, 0, 500) // truncate to avoid huge output
    ]);
    exit;
}

// Valid JSON – pass through
http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
