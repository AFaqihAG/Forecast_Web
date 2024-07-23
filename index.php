<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Display with Forecasting</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Font Icon CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <?php include 'config/config.php'?>
</head>
<body>
    <!-- Topbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Data Forecasting</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="btn btn-primary" onclick="window.location.href = 'setting.php';">
                            <i class="bi bi-gear"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <div class="container mt-4">
        <canvas id="forecastData" style="width:80%;max-width:1800px"></canvas>

        <div id="spinner" class="spinner-grow text-primary" role="status" style="display:none">
            <span class="sr-only">Loading...</span>
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
                        label: '<?php echo $name_column;?>',
                        data: data.map(item => item.name),
                        borderColor: 'rgb(64, 96, 255)',
                        tension: 0.9,
                        fill: false,
                    }]
                };

                // Render historical data chart
                const ctx = document.getElementById('forecastData').getContext('2d');
                const myChart = new Chart(ctx, {
                    type: 'line',
                    data: historicalData,
                    options: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Forecast Data - <?php echo $name_column;?>'
                        },
                        scales: {
                            xAxes: [{
                                ticks: {
                                    maxTicksLimit: 7
                                }
                            }]
                        },
                        elements: {
                            point:{
                                radius: 0
                            }
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

                        // Update Chart.js data with forecasted values
                        historicalData.labels.push(...forecastData.map(item => item.ds));
                        historicalData.datasets[0].data.push(...forecastData.map(item => item.yhat));

                        // Update chart with new data
                        myChart.update();

                        // Hide spinner after forecast is done
                        $('#spinner').hide();
                    }
                });
            }
        });
    });
</script>

</body>
</html>