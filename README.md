# README.md

# Converter PHP Backend

This project is a simple PHP backend application built using the Slim Framework. It provides an API endpoint that makes external API calls and returns the results in JSON format. The application is designed to be easily deployable on shared hosting environments.

## Project Structure

```
converter-php-backend
├── public
│   ├── index.php          # Entry point of the application
│   ├── .htaccess          # URL rewriting configuration
│   └── uploads            # Directory for storing uploaded files
├── src
│   ├── Controllers
│   │   └── ConvertController.php  # Controller for handling API logic
│   ├── Routes
│   │   └── routes.php           # Route definitions
│   ├── Utils
│   │   ├── HttpClient.php        # Utility for making HTTP requests
│   │   └── Helpers.php           # Helper functions
│   ├── Middleware
│   │   └── ErrorHandler.php       # Middleware for error handling
│   └── bootstrap.php             # Application initialization
├── config
│   └── settings.php              # Configuration settings
├── logs
│   └── app.log                   # Log file for application errors
├── composer.json                  # Composer dependencies
├── .env                           # Environment variables
├── .gitignore                     # Git ignore file
└── README.md                      # Project documentation
```

## Required PHP Packages

To run this application, you need to install the following PHP packages:

- Slim Framework
- vlucas/phpdotenv (for loading environment variables)

You can install these packages using Composer. Run the following command in the project root:

```
composer install
```

## Environment Variables Setup

Create a `.env` file in the project root and add the following environment variables:

```
API_URL_1=https://api.example.com/endpoint1
API_URL_2=https://api.example.com/endpoint2
API_URL_3=https://api.example.com/endpoint3
```

## Usage

1. Upload the project files to your shared hosting server.
2. Ensure that the server has PHP and Composer installed.
3. Access the application via your web browser at `http://yourdomain.com/public/index.php`.
4. You can call the API endpoint by navigating to `http://yourdomain.com/public/index.php/api/convert`.

## Example API Call

The API endpoint `/api/convert` will make three external API calls to the URLs defined in the `.env` file and return the results in JSON format.

## Logging

Application logs are stored in the `logs/app.log` file. Make sure this file is writable by the web server.

## License

This project is open-source and available under the MIT License.