<?php
// Load config.json
$configPath = 'config/config.json';
$config = json_decode(file_get_contents($configPath), true);

// Extract current settings
$servername = isset($config['servername']) ? htmlspecialchars($config['servername']) : '';
$username = isset($config['username']) ? htmlspecialchars($config['username']) : '';
$password = isset($config['password']) ? htmlspecialchars($config['password']) : '';
$dbname = isset($config['dbname']) ? htmlspecialchars($config['dbname']) : '';
$table_name = isset($config['table_name']) ? htmlspecialchars($config['table_name']) : '';
$name_column = isset($config['name_column']) ? htmlspecialchars($config['name_column']) : '';
$date_column = isset($config['date_column']) ? htmlspecialchars($config['date_column']) : '';
$length_prediction = isset($config['length_prediction']) ? htmlspecialchars($config['length_prediction']) : '';
$date_increment_type = isset($config['date_increment_type']) ? htmlspecialchars($config['date_increment_type']) : '';
$changepoint_prior_scale = isset($config['changepoint_prior_scale']) ? htmlspecialchars($config['changepoint_prior_scale']) : '';
$seasonality_prior_scale = isset($config['seasonality_prior_scale']) ? htmlspecialchars($config['seasonality_prior_scale']) : '';

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = $_POST['servername'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dbname = $_POST['dbname'];

    // Validate the database connection
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->close();

        // If connection is successful, save the new settings
        $config = [
            'servername' => $servername,
            'username' => $username,
            'password' => $password,
            'dbname' => $dbname,
            'table_name' => $table_name,
            'name_column' => $name_column,
            'date_column' => $date_column,
            'length_prediction' => $length_prediction,
            'date_increment_type' => $date_increment_type,
            'changepoint_prior_scale' => $changepoint_prior_scale,
            'seasonality_prior_scale' => $seasonality_prior_scale
        ];

        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
        $success_message = 'Settings saved successfully.';
    } catch (Exception $e) {
        // Show error message if connection fails
        $error_message = 'Failed to connect to the database. Please check your settings and try again.';
    }
}
?>