<?php
require_once 'includes/database.php';

$db = new Database();
$conn = $db->getConnection();

$sql = "DELETE FROM accounts WHERE deleted_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)"; 
if ($conn) {
    $conn->exec($sql);
} 