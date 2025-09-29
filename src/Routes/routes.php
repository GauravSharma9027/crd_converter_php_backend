<?php
use App\Controllers\ConvertController;
use Slim\App;

return function (App $app) {
    // Convert API route
    $app->post('/convert', [ConvertController::class, 'convertFile']);
};
