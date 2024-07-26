<?php
$config_file = __DIR__ . '/config.json';

if (!file_exists($config_file)) {
    die('Configuration file not found.');
}

$config_data = json_decode(file_get_contents($config_file), true);

if ($config_data === null) {
    die('Error parsing JSON configuration file.');
}

// Assign variables from JSON data
$servername = $config_data['servername'];
$username = $config_data['username'];
$password = $config_data['password'];
$dbname = $config_data['dbname'];
$table_name = $config_data['table_name'];
$name_column = $config_data['name_column'];
$date_column = $config_data['date_column'];
$length_prediction = $config_data['length_prediction'];
$date_increment_type = $config_data['date_increment_type'];
$changepoint_prior_scale = $config_data['changepoint_prior_scale'];
$seasonality_prior_scale = $config_data['seasonality_prior_scale'];