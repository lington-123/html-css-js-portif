<?php
// Basic DB and mail configuration
// Update these values to match your local MySQL and email preferences

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'portfolio_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    // SMTP is optional. If left empty, PHP's mail() will be used.
    'smtp' => [
        'enabled' => false,
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'user@example.com',
        'password' => 'app-password',
        'from_email' => 'noreply@example.com',
        'from_name' => 'Portfolio Site',
        'to_email' => 'patrickshema150@gmail.com'
    ],
];


