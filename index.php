<?php
// Load config.json
$configPath = 'config/config.json';
$config = json_decode(file_get_contents($configPath), true);

// Extract current settings
$table_name = isset($config['table_name']) ? htmlspecialchars($config['table_name']) : '';
$name_column = isset($config['name_column']) ? htmlspecialchars($config['name_column']) : '';
$date_column = isset($config['date_column']) ? htmlspecialchars($config['date_column']) : '';
$length_prediction = isset($config['length_prediction']) ? intval($config['length_prediction']) : 730;

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract posted settings
    $posted_table_name = $_POST['table_name'];
    $posted_name_column = $_POST['name_column'];
    $posted_date_column = $_POST['date_column'];
    $posted_length_prediction = $_POST['length_prediction'];

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
            'length_prediction' => $posted_length_prediction
        ];

        // Read the existing config
        $existingConfig = json_decode(file_get_contents($configPath), true);

        // Merge existing config with new settings
        $updatedConfig = array_merge($existingConfig, $newConfig);

        // Write the updated config to the file
        file_put_contents($configPath, json_encode($updatedConfig, JSON_PRETTY_PRINT));

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
    <title>Data Display with Forecasting</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <!-- Topbar -->
    <?php include 'topbar.php'; ?>

    <!-- Main content -->
    <div class="container mt-4">
        <!-- Chart -->
        <canvas id="forecastData" style="width:80%;max-width:1800px"></canvas>

        <!-- Spinner -->
        <div id="spinner" class="spinner-grow text-primary" role="status" style="display:none">
            <span class="sr-only">Loading...</span>
        </div>

        <!-- Configuration Settings Form -->
        <div class="mt-4">
            <h5>Configuration Settings</h5>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form id="settingsForm" method="post">
                <div class="form-group">
                    <label for="table_name">Table Name</label>
                    <select class="form-control" id="table_name" name="table_name" required>
                        <?php
                        // Fetch and display table names from the database
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
                <div class="form-group">
                    <label for="name_column">Name Column</label>
                    <select class="form-control" id="name_column" name="name_column" required>
                        <!-- Options will be populated via AJAX -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_column">Date Column</label>
                    <select class="form-control" id="date_column" name="date_column" required>
                        <!-- Options will be populated via AJAX -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="length_prediction">Prediction Length</label>
                    <input type="number" class="form-control" id="length_prediction" name="length_prediction" value="<?php echo htmlspecialchars($length_prediction); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Run Forecast Model</button>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Trigger AJAX request on page load
        $.ajax({
            url: 'database/fetch_data.php',
            type: 'GET',
            success: function(response) {
                const data = JSON.parse(response);

                // Chart.js configuration for historical data
                const historicalData = {
                    labels: data.map(item => item.time_stamp),
                    datasets: [{
                        label: 'Historical Data - <?php echo $name_column; ?>',
                        data: data.map(item => item.name),
                        borderColor: 'rgb(64, 96, 255)',
                        tension: 0.9,
                        fill: false,
                    }]
                };

                // Initialize chart with historical data
                const ctx = document.getElementById('forecastData').getContext('2d');
                const myChart = new Chart(ctx, {
                    type: 'line',
                    data: historicalData,
                    options: {
                        legend: { display: true },
                        title: {
                            display: true,
                            text: 'Forecast Data - <?php echo $name_column; ?>'
                        },
                        elements: {
                            point: { radius: 0 }
                        }
                    },
                });

                // Show spinner before starting forecast
                $('#spinner').show();

                // Send data to Python for forecasting
                $.ajax({
                    url: 'forecast/forecast.php',
                    type: 'POST',
                    data: { data: response },
                    success: function(forecastResponse) {
                        const forecastData = JSON.parse(forecastResponse);

                        // Add forecast data to the chart
                        myChart.data.datasets.push({
                            label: 'Forecast Data',
                            data: forecastData.map(item => ({ x: item.ds, y: item.yhat })),
                            borderColor: 'rgb(255, 99, 132)',
                            tension: 0.1,
                            fill: false,
                        });

                        // Update chart with forecast data
                        myChart.update();

                        // Hide spinner after forecast is done
                        $('#spinner').hide();
                    }
                });
            }
        });

        // Populate columns when table is selected
        $('#table_name').change(function() {
            var tableName = $(this).val();
            $.ajax({
                url: 'database/fetch_columns.php',
                method: 'POST',
                data: { table_name: tableName },
                success: function(data) {
                    var columns = JSON.parse(data);
                    $('#name_column').empty();
                    $('#date_column').empty();
                    $.each(columns, function(index, column) {
                        $('#name_column').append('<option value="' + column + '">' + column + '</option>');
                        $('#date_column').append('<option value="' + column + '">' + column + '</option>');
                    });
                }
            });
        });

        // Trigger change event to populate columns on page load
        $('#table_name').trigger('change');
    });
    </script>
</body>
</html>
