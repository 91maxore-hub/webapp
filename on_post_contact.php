<?php

if (!defined('CURLOPT_SSLVERSION')) {
    define('CURLOPT_SSLVERSION', 32);
}
if (!defined('CURL_SSLVERSION_TLSv1_2')) {
    define('CURL_SSLVERSION_TLSv1_2', 6);
}

// Handle POST request for contact form submission
require_once 'database_setup.php';
require_once 'vendor/autoload.php'; // Azure SDK

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($name) && !empty($email) && !empty($message)) {
        try {
            // === 1. Spara i databasen (din befintliga kod) ===
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);
            $success = true;

            // === 2. Skapa JSON av formulärsvar ===
            $formData = [
                'name' => $name,
                'email' => $email,
                'message' => $message,
                'timestamp' => date('c')
            ];
            $jsonData = json_encode($formData, JSON_PRETTY_PRINT);
            $filename = 'formulärsvar_' . time() . '_' . uniqid() . '.json';

            // === 3. Azure Blob Storage setup ===
            $connectionString = "DefaultEndpointsProtocol=https;AccountName=blobresponses;AccountKey=bW4S5o6DgnNef09ZZsCDE3Q/gDMx04Z+J/J0xVBGdUIObcbOgvPt0utrvx6V8ejaMUxTn1UbWCO8+AStJc4ubA==;EndpointSuffix=core.windows.net";
            $containerName = 'responses';

            $blobClient = BlobRestProxy::createBlobService($connectionString);
            $options = new CreateBlockBlobOptions();
            $options->setContentType("application/json");

            // === 4. Spara JSON som blob ===
            $blobClient->createBlockBlob($containerName, $filename, $jsonData, $options);

        } catch (PDOException $e) {
            $error = "Error saving message: " . $e->getMessage();
        } catch (ServiceException $e) {
            $error = "Error saving to blob storage: " . $e->getMessage();
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Sent - Azure MySQL Contact App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📨 Message Status</h1>
        </header>

        <nav>
            <a href="index.html" class="btn">Home</a>
            <a href="contact_form.html" class="btn">Contact Form</a>
            <a href="on_get_messages.php" class="btn">View Messages</a>
        </nav>

        <main>
            <?php if (isset($success)): ?>
                <div class="success-message">
                    <h2>✅ Message Sent Successfully!</h2>
                    <p>Thank you for your message. It has been saved to both the Azure MySQL database and Azure Blob Storage.</p>
                </div>
            <?php elseif (isset($error)): ?>
                <div class="error-message">
                    <h2>❌ Error</h2>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="actions">
                <a href="contact_form.html" class="btn">Send Another Message</a>
                <a href="on_get_messages.php" class="btn">View All Messages</a>
            </div>
        </main>
    </div>
</body>
</html>
