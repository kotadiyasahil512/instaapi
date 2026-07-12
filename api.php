<?php

// CORS
$allowed_origins = [
    'https://saveinsta.infinityfree.me',
    'https://saveinsta.online'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Preflight Request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'POST method required'
    ]);
    exit;
}

$url = trim($_POST['url'] ?? '');

if (empty($url)) {
    die(json_encode([
        'success' => false,
        'message' => 'Instagram URL is required'
    ]));
}

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.instagrab.me/instagram.php',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_POSTFIELDS => http_build_query([
        'url' => $url
    ]),
    CURLOPT_HTTPHEADER => [
        'accept: */*',
        'content-type: application/x-www-form-urlencoded',
        'origin: https://www.instagrab.me',
        'referer: https://www.instagrab.me/',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36'
    ]
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($error) {
    die(json_encode([
        'success' => false,
        'error' => $error
    ]));
}

$data = json_decode($response, true);

if (!$data) {
    die(json_encode([
        'success' => false,
        'http_code' => $httpCode,
        'message' => 'Invalid response',
        'raw_response' => $response
    ]));
}

echo json_encode([
    'success'      => true,
    'http_code'    => $httpCode,
    'type'         => $data['type'] ?? '',
    'author'       => $data['author'] ?? '',
    'thumbnail'    => $data['thumbnail'] ?? '',
    'caption'      => $data['caption'] ?? '',
    'download_url' => $data['url'] ?? '',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>