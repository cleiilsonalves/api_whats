
<?php 

$data = [
    'phone' => '5511986586232', // Receivers phone 972275207
    'body' => 'https://chat-api.com/pt-br/', // Message
];
$json = json_encode($data); // Encode data to JSON
// URL for request POST /message
$url = 'https://eu28.chat-api.com/instance93157/message?token=5ra1llp4mj4ajsuw';
// Make a POST request
$options = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/json',
        'content' => $json
    ]
]);
// Send a request
$result = file_get_contents($url, false, $options);

echo $result;