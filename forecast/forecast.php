<?php
require_once __DIR__ . '/../config/config.php';

global $length_prediction, $date_increment_type;  

$data = $_POST['data']; 

$python_script = 'python3 forecast.py'; 

$process = proc_open($python_script, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], $data);
    fclose($pipes[0]);

    $result = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    proc_close($process);
    echo $result;
} else {
    echo "Failed to open process.";
}
