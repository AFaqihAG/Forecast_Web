<?php
require_once __DIR__ . '/../config/config.php';

function getDatabase() {
    global $servername, $username, $password, $dbname;

    // Create a new connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function closeDatabase($conn) {
    $conn->close();
}

// Function to get all columns from a table
function getTableColumns($conn, $table_name) {
    $columns = [];
    $sql = "SHOW COLUMNS FROM $table_name";
    $result = null; // Initialize the result variable

    try {
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
        }
    } catch (mysqli_sql_exception $e) {
        // If an error occurs, return an empty array
        $columns[] = 'unknown';
    }

    return $columns;
}

function executeSQLAndGetColumns($conn, $table_name) {
    // Prepare the SQL query to get all columns in the table
    $sql_columns = "SHOW COLUMNS FROM $table_name";
    $result_columns = $conn->query($sql_columns);

    if ($result_columns->num_rows > 0) {
        $columns = [];
        while ($row = $result_columns->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        return $columns;
    } else {
        return false;
    }
}
?>
