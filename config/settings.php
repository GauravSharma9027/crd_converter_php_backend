<?php
return [
    'api_urls' => [
        'url1' => getenv('API_URL_1'),
        'url2' => getenv('API_URL_2'),
        'url3' => getenv('API_URL_3'),
    ],
    'log_file' => __DIR__ . '/../logs/app.log',
    'uploads_dir' => __DIR__ . '/../public/uploads',
];