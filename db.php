<?php
$host = 'mike-server.tailed9121.ts.net';
$dbname = 'expense_tracker';
$username = 'mike';
$password = 'Mike@1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
