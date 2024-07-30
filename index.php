<?php require 'config/index_config.php'; ?>
 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Forecast Display</title>
    
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
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>

    <!-- Pass PHP data to JavaScript -->
    <script>
        const nameColumn = '<?php echo $name_column; ?>';
        const dateColumn = '<?php echo $date_column; ?>';
        const dbFailConnect = '<?php echo $db_connection_failed?>'
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
                <div class="card-header p-0 m-0" style="background-color: #e3f2fd;">
                    <h5 class="mb-0 d-flex justify-content-between align-items-center">
                        <button class="btn btn-light w-100 text-start bg-transparent border-0 shadow-none" data-toggle="collapse" data-target="#configSettings" aria-expanded="true" aria-controls="configSettings">
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
                                    <label for="date_increment_type">Date Increment Type</label>
                                    <select class="form-control" id="date_increment_type" name="date_increment_type" required>
                                        <option value="seconds">Seconds</option>
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Hours</option>
                                        <option value="days">Days</option>
                                        <option value="month">Month</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="length_prediction">Prediction Length</label>
                                    <input type="number" class="form-control" id="length_prediction" name="length_prediction" value="<?php echo htmlspecialchars($length_prediction); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="changepoint_prior_scale">Changepoint Prior Scale</label>
                                    <input type="number" step="0.01" min="0.01" max="0.5" class="form-control" id="changepoint_prior_scale" name="changepoint_prior_scale" value="<?php echo htmlspecialchars($changepoint_prior_scale); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="seasonality_prior_scale">Seasonality Prior Scale</label>
                                    <input type="number" step="0.1" min="0.1" max="10" class="form-control" id="seasonality_prior_scale" name="seasonality_prior_scale" value="<?php echo htmlspecialchars($seasonality_prior_scale); ?>" required>
                                </div>
                                
                                <!-- Button to toggle the description section -->
                                <div class="form-group col-md-6 d-flex justify-content-end align-items-center">
                                    <!-- Question mark icon for collapsing the description section -->
                                    <div class="d-flex justify-content-center mb-3">
                                        <span data-toggle="collapse" data-target="#descriptionSection" aria-expanded="false" aria-controls="descriptionSection">
                                            <i class="bi bi-question-circle" style="font-size: 1.5rem; cursor: pointer;" title="Click to view descriptions"></i>
                                        </span>
                                    </div>
                                    
                                    <!-- Collapsible description section -->
                                    <div id="descriptionSection" class="collapse">
                                        <div class="card p-3">
                                            <h5>Parameter Descriptions</h5>
                                            <hr>
                                            <div class="mb-2">
                                                <strong>Table Name:</strong>
                                                <p>The name of the database table containing the data to be analyzed.</p>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Date Column:</strong>
                                                <p>The column in the selected table that contains date or time information for each record.</p>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Name Column:</strong>
                                                <p>The column in the selected table that contains the name or identifier for each record.</p>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Date Increment Type:</strong>
                                                <p>Defines the time unit for data (seconds, minutes, hours, days, months). You may pick Date Increment Type higher than the datetime (e.g., if datetime format is YYYY-MMM-DD HH, you can choose Hours, Days, or Months, but DO NOT choose Minutes or Seconds).</p>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Prediction Length:</strong>
                                                <p>Number of future periods to forecast based on Date Increment Type.</p>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Changepoint Prior Scale:</strong>
                                                <p><em>Do not change unless you understand its impact on detecting sudden shifts in your data.</em></p>
                                                <p>Controls the model's sensitivity to abrupt changes in the time series data. A higher value allows the model to be more responsive to sudden shifts or changes, potentially capturing more abrupt changes but also risking overfitting. A lower value results in a smoother model that may overlook sudden changes but is less likely to overfit. It ranges from 0.01 to 0.5, with a default value of 0.05. Adjust this parameter based on the frequency and significance of changes in your data.</p>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Seasonality Prior Scale:</strong>
                                                <p><em>Avoid changing this value unless you are familiar with its effect on the model's seasonal sensitivity.</em></p>
                                                <p>Adjusts the strength of seasonal patterns detected in the data. A higher value will emphasize the seasonal component, making the model more sensitive to recurring patterns. Conversely, a lower value will downplay seasonal effects, potentially improving the model's performance if seasonal patterns are less relevant. It ranges from 0.1 to 10.0, with a default value of 10.0. This parameter is particularly useful for datasets with strong, regular seasonal variations. Adjust based on how pronounced the seasonality is in your data.</p>
                                            </div>
                                        </div>
                                    </div>
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


    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                </div>
                <div class="modal-body">
                    Failed to connect to the database, please check the setting page!
                </div>
                <div class="modal-footer">
                    <a href="setting.php">
                        <button type="button" class="btn btn-secondary">Go to Setting</button>
                    </a>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($db_connection_failed): ?>
                var successModal = new bootstrap.Modal(document.getElementById('errorModal'));
                successModal.show();
            <?php endif; ?>
        });
    </script>

</body>
</html>

