
<?php
use Dotenv\Dotenv;


$envPath = __DIR__ . '/../';

if (file_exists($envPath . '.env')) {
    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->load();
}
