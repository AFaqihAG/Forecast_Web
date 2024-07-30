<?php
// Load config.json
$configPath = 'config/config.json';
$config = json_decode(file_get_contents($configPath), true);

// Extract current settings with default values
$table_name = isset($config['table_name']) ? htmlspecialchars($config['table_name']) : '';
$name_column = isset($config['name_column']) ? htmlspecialchars($config['name_column']) : '';
$date_column = isset($config['date_column']) ? htmlspecialchars($config['date_column']) : '';
$length_prediction = isset($config['length_prediction']) ? intval($config['length_prediction']) : 730;
$date_increment_type = isset($config['date_increment_type']) ? htmlspecialchars($config['date_increment_type']) : '';
$changepoint_prior_scale = isset($config['changepoint_prior_scale']) ? floatval($config['changepoint_prior_scale']) : 0.05;
$seasonality_prior_scale = isset($config['seasonality_prior_scale']) ? floatval($config['seasonality_prior_scale']) : 10.0;

$error_message = '';
$db_connection_failed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract posted settings
    $posted_table_name = $_POST['table_name'];
    $posted_name_column = $_POST['name_column'];
    $posted_date_column = $_POST['date_column'];
    $posted_length_prediction = $_POST['length_prediction'];
    $posted_date_increment_type = $_POST['date_increment_type'];
    $posted_changepoint_prior_scale = $_POST['changepoint_prior_scale'];
    $posted_seasonality_prior_scale = $_POST['seasonality_prior_scale'];

    // Extract database settings
    $servername = isset($config['servername']) ? htmlspecialchars($config['servername']) : '';
    $username = isset($config['username']) ? htmlspecialchars($config['username']) : '';
    $password = isset($config['password']) ? htmlspecialchars($config['password']) : '';
    $dbname = isset($config['dbname']) ? htmlspecialchars($config['dbname']) : '';

    try {
        // Validate the database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            // $db_connection_failed = true;
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->close();

        // Update settings if connection is successful
        $newConfig = [
            'table_name' => $posted_table_name,
            'name_column' => $posted_name_column,
            'date_column' => $posted_date_column,
            'length_prediction' => $posted_length_prediction,
            'date_increment_type' => $posted_date_increment_type,
            'changepoint_prior_scale' => $posted_changepoint_prior_scale,
            'seasonality_prior_scale' => $posted_seasonality_prior_scale
        ];

        // Read the existing config
        $existingConfig = json_decode(file_get_contents($configPath), true);

        // Merge existing config with new settings
        $updatedConfig = array_merge($existingConfig, $newConfig);

        // Write the updated config to the file
        file_put_contents($configPath, json_encode($updatedConfig, JSON_PRETTY_PRINT));

        // Update local variables to reflect the new settings
        $table_name = $posted_table_name;
        $name_column = $posted_name_column;
        $date_column = $posted_date_column;
        $length_prediction = $posted_length_prediction;
        $date_increment_type = $posted_date_increment_type;
        $changepoint_prior_scale = $posted_changepoint_prior_scale;
        $seasonality_prior_scale = $posted_seasonality_prior_scale;

    } catch (Exception $e) {
        $error_message = 'Failed to connect to the database. Please check your settings and try again.';
    }
}
?>