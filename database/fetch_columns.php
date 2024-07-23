<?php
require_once __DIR__ . '/../config/config.php';
require '../database/db_connect.php';
require '../database/sql_query.php';

$columns = [];

if (isset($_POST['table_name'])) {
    $table_name = $_POST['table_name'];

    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $columns = getTableColumns($conn, $table_name);
        closeDatabase($conn);
    } catch (Exception $e) {
        echo json_encode([]);
        exit;
    }
}

echo json_encode($columns);
?>
