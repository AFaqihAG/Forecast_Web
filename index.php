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

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract posted settings
    $posted_table_name = $_POST['table_name'];
    $posted_name_column = $_POST['name_column'];
    $posted_date_column = $_POST['date_column'];
    $posted_length_prediction = $_POST['length_prediction'];
    $posted_date_increment_type = $_POST['date_increment_type'];

    // Extract database settings
    $servername = isset($config['servername']) ? htmlspecialchars($config['servername']) : '';
    $username = isset($config['username']) ? htmlspecialchars($config['username']) : '';
    $password = isset($config['password']) ? htmlspecialchars($config['password']) : '';
    $dbname = isset($config['dbname']) ? htmlspecialchars($config['dbname']) : '';

    try {
        // Validate the database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->close();

        // Update settings if connection is successful
        $newConfig = [
            'table_name' => $posted_table_name,
            'name_column' => $posted_name_column,
            'date_column' => $posted_date_column,
            'length_prediction' => $posted_length_prediction,
            'date_increment_type' => $posted_date_increment_type
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

    } catch (Exception $e) {
        $error_message = 'Failed to connect to the database. Please check your settings and try again.';
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Forecasting Display</title>
    
    <!-- Bootstrap  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    
    <!-- Chart JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3"></script>

    <!-- Pass PHP data to JavaScript -->
    <script>
        const nameColumn = '<?php echo $name_column; ?>';
    </script>

    <!-- Custom JS -->
    <script src="javascript/main.js" defer></script>

</head>
<body>
    <!-- Topbar -->
    <?php include 'topbar.php'; ?>

    <!-- Main content -->
    <div class="container mt-4">

        <!-- Chart -->
        <div class="card">
            <div class="card-body">
            <h5 class="mb-0 text-center">Forecast Data Line Chart</h5>
            </div>
            <div class="col-12 card-body">
                <canvas id="forecastData" style="width:100%;max-width:1800px;"></canvas>
            </div>
        </div>

        <!-- Statistics -->
        <div id="stats-container" class="mt-4">
            <!-- Statistics will be injected here by JavaScript -->
        </div>

        <!-- Configuration Settings Form -->
        <div class="mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 d-flex justify-content-between align-items-left">
                        <button class="btn btn-light w-100 text-start bg-transparent border-0 " data-toggle="collapse" data-target="#configSettings" aria-expanded="true" aria-controls="configSettings">
                            <span>Configuration Settings</span>
                        </button>
                    </h5>
                </div>
                <div id="configSettings" class="collapse show">
                    <div class="card-body">
                    <form id="settingsForm" method="post">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="table_name">Table Name</label>
                                <select class="form-control" id="table_name" name="table_name" required>
                                    <option value="" disabled>Select table</option>
                                    <?php
                                    require 'database/db_connect.php';
                                    require 'database/sql_query.php';

                                    try {
                                        $conn = new mysqli($servername, $username, $password, $dbname);
                                        if ($conn->connect_error) {
                                            throw new Exception("Connection failed: " . $conn->connect_error);
                                        }
                                        $tables = getTableNames($conn);
                                        foreach ($tables as $table) {
                                            echo "<option value=\"$table\"" . ($table === $table_name ? ' selected' : '') . ">" . htmlspecialchars($table) . "</option>";
                                        }
                                        closeDatabase($conn);
                                    } catch (Exception $e) {
                                        echo '<option value="" disabled>Error fetching tables</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="date_column">Date Column</label>
                                <select class="form-control" id="date_column" name="date_column" required>
                                    <option value="" disabled>Select date column</option>
                                    <!-- Options will be populated via AJAX -->
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="name_column">Name Column</label>
                                <select class="form-control" id="name_column" name="name_column" required>
                                    <option value="" disabled>Select name column</option>
                                    <!-- Options will be populated via AJAX -->
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="length_prediction">Prediction Length</label>
                                <input type="number" class="form-control" id="length_prediction" name="length_prediction" value="<?php echo htmlspecialchars($length_prediction); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="date_increment_type">Date Increment Type</label>
                                <select class="form-control" id="date_increment_type" name="date_increment_type" required>
                                    <option value="seconds">Seconds</option>
                                    <option value="minutes">Minutes</option>
                                    <option value="hours">Hours</option>
                                    <option value="days">Days</option>
                                    <option value="month">Month</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col text-center">
                                <button type="submit" class="btn btn-primary">Run Forecast Model</button>
                            </div>
                        </div>
                    </form>

                    </div>
                </div>
            </div>
        </div>

        
        <!-- Rectangle Block at the Bottom -->
        <footer class="bg-light border-top py-3 mt-4">
            <div class="container text-center">
                <p class="mb-0">Bla Bla Bla Bla Bla Bla Bla</p>
            </div>
        </footer>

    </div>

</body>
</html>

