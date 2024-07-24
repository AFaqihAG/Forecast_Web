$(document).ready(function() {
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

        // Handle form submission
        $('#settingsForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            var formData = $(this).serialize(); // Serialize form data

            $.ajax({
                url: 'index.php',
                type: 'POST',
                data: formData,
                success: function() {
                    location.reload(); 
                },
                error: function() {
                    alert('Failed to save settings. Please try again.');
                }
            });
        });

        // Trigger change event to populate columns on page load
        $('#table_name').trigger('change');


    // Display the table initially
    const statsContainer = document.getElementById('stats-container');
    statsContainer.innerHTML = `
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0 d-flex justify-content-between align-items-center">
                <button class="btn btn-light w-100 text-start bg-transparent border-0 p-0 m-0" data-toggle="collapse" data-target="#statisticsContent" aria-expanded="true" aria-controls="statisticsContent">
                    <span>Statistics</span>
                </button>
            </h5>
        </div>
        <div id="statisticsContent" class="collapse show">
            <div class="card-body">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Data Type</th>
                            <th>Mean</th>
                            <th>Highest</th>
                            <th>Lowest</th>
                            <th>Slope Increase</th>
                            <th>Standard Deviation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Historical Data</td>
                            <td>.</td>
                            <td>.</td>
                            <td>.</td>
                            <td>.</td>
                            <td>.</td>
                        </tr>
                        <tr>
                            <td>Forecast Data</td>
                            <td>.</td>
                            <td>.</td>
                            <td>.</td>
                            <td>.</td>
                            <td>.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    `;
  
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
            label: 'Original Data',
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
              text: 'Forecast Data'
            },
            elements: {
              point: { radius: 0 }
            },
            scales: {
              y: {
                display: true,
                title: {
                  display: true,
                  text: nameColumn,
                  color: '#191',
                  font: {
                    family: 'Times',
                    size: 20,
                    style: 'normal',
                    lineHeight: 1.2
                  },
                  padding: {top: 0, left: 0, right: 0, bottom: 0}
                }
              },
              x: {
                ticks: {
                  maxTicksLimit: 7
                }
              }
            }
          },
        });
  
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
          // Calculate statistics for historical and forecast data
          const historicalValues = data.map(item => parseFloat(item.name));
          const forecastValues = forecastData.map(item => parseFloat(item.yhat));

          const historicalStats = {
            average: average(historicalValues),
            highest: Math.max(...historicalValues),
            lowest: Math.min(...historicalValues),
            averageSlopeIncrease: calculateAverageSlopeIncrease(historicalValues),
            standardDeviation: getStandardDeviation(historicalValues)
          };
          const forecastStats = {
            average: average(forecastValues),
            highest: Math.max(...forecastValues),
            lowest: Math.min(...forecastValues),
            averageSlopeIncrease: calculateAverageSlopeIncrease(forecastValues),
            standardDeviation: getStandardDeviation(forecastValues)
          };

            // Update the table with the forecast data
            statsContainer.innerHTML = `
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0 d-flex justify-content-between align-items-center">
                        <button class="btn btn-light w-100 text-start bg-transparent border-0 p-0 m-0" data-toggle="collapse" data-target="#statisticsContent" aria-expanded="true" aria-controls="statisticsContent">
                            <span>Statistics</span>
                        </button>
                    </h5>
                </div>
                <div id="statisticsContent" class="collapse show">
                    <div class="card-body">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Data Type</th>
                                    <th>Mean</th>
                                    <th>Highest</th>
                                    <th>Lowest</th>
                                    <th>Slope Increase</th>
                                    <th>Standard Deviation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Historical Data</td>
                                    <td>${historicalStats.average.toFixed(2)}</td>
                                    <td>${historicalStats.highest.toFixed(2)}</td>
                                    <td>${historicalStats.lowest.toFixed(2)}</td>
                                    <td>${historicalStats.averageSlopeIncrease.toFixed(2)}</td>
                                    <td>${historicalStats.standardDeviation.toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td>Forecast Data</td>
                                    <td>${forecastStats.average.toFixed(2)}</td>
                                    <td>${forecastStats.highest.toFixed(2)}</td>
                                    <td>${forecastStats.lowest.toFixed(2)}</td>
                                    <td>${forecastStats.averageSlopeIncrease.toFixed(2)}</td>
                                    <td>${forecastStats.standardDeviation.toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            `;
        }
      });
    }
  });
});

// Helper function to calculate average
function average(values) {
  const sum = values.reduce((accumulator, currentValue) => accumulator + currentValue, 0);
  return sum / values.length;
}

// Helper function to calculate average slope increase
function calculateAverageSlopeIncrease(values) {
  let sum = 0;
  for (let i = 1; i < values.length; i++) {
    sum += (values[i] - values[i - 1]) / (i - (i - 1));
  }
  return sum / (values.length - 1);
}

// Helper function to calculate standard deviation
function getStandardDeviation(values) {
  const n = values.length;
  const mean = values.reduce((a, b) => a + b) / n;
  return Math.sqrt(values.map(x => Math.pow(x - mean, 2)).reduce((a, b) => a + b) / n);
}  