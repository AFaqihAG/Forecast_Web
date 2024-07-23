<?php
require_once __DIR__ . '/../config/config.php';
require 'db_connect.php';

global $table_name, $name_column, $date_column, $length_prediction;

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

// Execute the function to get columns
$columns_list = executeSQLAndGetColumns($conn, $table_name);

// Adjust the SQL query to calculate average values over minute intervals
$sql = "SELECT 
            DATE_FORMAT($date_column, '%Y-%m-%d %H:%i') AS minute_interval,
            AVG($name_column) AS average_value
        FROM $table_name
        GROUP BY minute_interval";

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Format the minute interval as needed
        $data[] = [
            'time_stamp' => $row['minute_interval'],    
            'name' => $row['average_value']   
        ];
    }
}

// Close the database connection
$conn->close();

// Output JSON encoded data
echo json_encode($data);
?>