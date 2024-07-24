<?php
require_once __DIR__ . '/../config/config.php';

global $length_prediction, $date_increment_type;  

$data = $_POST['data']; // Assuming data is sent via POST from index.php

// Ensure date_increment_type is passed as part of the command
$python_script = 'python3 forecast.py ' . escapeshellarg($length_prediction) . ' ' . escapeshellarg($date_increment_type); // Adjust path to your Python script

// Execute Python script
$process = proc_open($python_script, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], $data);
    fclose($pipes[0]);

    $result = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    proc_close($process);

    // Output forecast result to index.php
    echo $result;
} else {
    echo "Failed to open process.";
}
