<?php
require 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

$connectionString = getenv('AZURE_STORAGE_CONNECTION_STRING');
$blobClient = BlobRestProxy::createBlobService($connectionString);

$containerName = "formresponses";

$formData = $_POST;
$fileName = "response-" . time() . ".json";
$content = json_encode($formData);

try {
    $blobClient->createBlockBlob($containerName, $fileName, $content);
} catch (Exception $e) {
    error_log("Fel vid Blob Storage: " . $e->getMessage());
}
?>