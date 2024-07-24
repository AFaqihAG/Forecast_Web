<?php
require_once __DIR__ . '/../config/config.php';
require 'db_connect.php';
require 'sql_query.php';

global $table_name, $name_column, $date_column, $length_prediction;

// Define the date increment type to MySQL DATE_FORMAT mapping
$dateIncrementFormats = [
    'seconds' => '%Y-%m-%d %H:%i:%s',
    'minutes' => '%Y-%m-%d %H:%i',
    'hours' => '%Y-%m-%d %H',
    'days' => '%Y-%m-%d',
    'month' => '%Y-%m'
];

// Use the default format if date_increment_type is not valid
$format_date = isset($dateIncrementFormats[$date_increment_type]) ? $dateIncrementFormats[$date_increment_type] : '%Y-%m-%d %H:%i';

// Execute the function to get columns
$columns_list = executeSQLAndGetColumns($conn, $table_name);

// Adjust the SQL query to calculate average values over minute intervals
$sql = 
"
SELECT * FROM
(
    SELECT  
        DATE_FORMAT($date_column, '$format_date') AS time_interval,
        AVG($name_column) AS average_value
    FROM $table_name
    GROUP BY time_interval
    ORDER BY time_interval DESC LIMIT 3650
) as temp
ORDER BY time_interval ASC;
";

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Format the minute interval as needed
        $data[] = [
            'time_stamp' => $row['time_interval'],    
            'name' => $row['average_value']   
        ];
    }
}

// Close the database connection
closeDatabase($conn);

// Output JSON encoded data
echo json_encode($data);