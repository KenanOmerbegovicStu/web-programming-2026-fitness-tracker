<?php
require __DIR__ . '/../../../vendor/autoload.php';

// One PHP warning in the middle of the JSON would break Swagger UI
ini_set('display_errors', '0');
error_reporting(E_ALL);

// The base URL of the API, computed from this request, so "Try it out" works on XAMPP and on the server.
// SCRIPT_NAME is e.g. /fitness-tracker/backend/public/v1/docs/swagger.php -> /fitness-tracker/backend/
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$backend_path = preg_replace('#public/v1/docs/swagger\.php$#', '', $_SERVER['SCRIPT_NAME'] ?? '/');
define('API_BASE_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $backend_path);

// swagger-php reads the attributes with PHP reflection, which only works on classes that are loaded
$files = array_merge([__DIR__ . '/doc_setup.php'], glob(__DIR__ . '/paths/*.php'));
foreach ($files as $file) {
    require_once $file;
}

$openapi = (new \OpenApi\Generator())->generate($files);

header('Content-Type: application/json');
echo $openapi->toJson();
