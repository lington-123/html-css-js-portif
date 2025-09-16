<?php
header('Content-Type: application/json');

// Simple hardening
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// CSRF: disabled for local use; enable server-side tokens in production

// Input sanitization
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

// Load config
$config = require __DIR__ . '/config.php';

// Connect to DB (create table if not exists)
try {
    $db = $config['db'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['database'], $db['charset']);
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec('CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)');
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message,
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

// Email notification (optional SMTP)
$sent = false;
try {
    $smtp = $config['smtp'];
    $to = $smtp['to_email'] ?? 'patrickshema150@gmail.com';
    $subjectLine = 'Portfolio Contact: ' . $subject;
    $body = "Name: $name\nEmail: $email\nSubject: $subject\n\n$message";

    if (!empty($smtp['enabled'])) {
        // Minimal SMTP via fsockopen could be implemented, but for simplicity here we fall back to mail()
        // Configure a proper SMTP library like PHPMailer in production.
        $headers = 'From: ' . ($smtp['from_email'] ?? 'noreply@example.com') . "\r\n" . 'Reply-To: ' . $email;
        $sent = mail($to, $subjectLine, $body, $headers);
    } else {
        $headers = 'From: ' . $email;
        $sent = mail($to, $subjectLine, $body, $headers);
    }
} catch (Throwable $e) {
    // ignore mail errors, DB already saved
}

echo json_encode(['success' => true, 'message' => $sent ? 'Message sent and saved' : 'Message saved']);


