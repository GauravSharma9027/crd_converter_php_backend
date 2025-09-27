<?php
use App\Controllers\ConvertController;
use Slim\App;

return function (App $app) {
    // Convert API route
    $app->post('/api/convert', [ConvertController::class, 'convertFile']);
};
