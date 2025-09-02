<?php
// Handle GET request to display all contact messages
require_once 'database_setup.php';

try {
    $stmt = $pdo->prepare("SELECT id, name, email, message, created_at FROM contacts ORDER BY created_at DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error retrieving messages: " . $e->getMessage();
    $messages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Messages - Azure MySQL Contact App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 All Messages</h1>
        </header>

        <nav>
            <a href="index.html" class="btn">Home</a>
            <a href="contact_form.html" class="btn">Contact Form</a>
            <a href="on_get_messages.php" class="btn active">View Messages</a>
        </nav>

        <main>
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <h2>❌ Error</h2>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php elseif (empty($messages)): ?>
                <div class="info-message">
                    <h2>📭 No Messages Yet</h2>
                    <p>No messages have been submitted yet.</p>
                    <a href="contact_form.html" class="btn">Send First Message</a>
                </div>
            <?php else: ?>
                <div class="messages-count">
                    <p>Total messages: <strong><?php echo count($messages); ?></strong></p>
                </div>

                <div class="messages-list">
                    <?php foreach ($messages as $message): ?>
                        <div class="message-item">
                            <div class="message-header">
                                <h3><?php echo htmlspecialchars($message['name']); ?></h3>
                                <span class="message-date"><?php echo htmlspecialchars($message['created_at']); ?></span>
                            </div>
                            <p class="message-email">📧 <?php echo htmlspecialchars($message['email']); ?></p>
                            <div class="message-content">
                                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
