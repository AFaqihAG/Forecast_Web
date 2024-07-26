<nav class="navbar navbar-expand-lg navbar-light sticky-top" style="background-color: #e3f2fd;">
    <div class="container-fluid">
        <!-- Main Menu -->
        <a class="navbar-brand" href="index.php" id="dataForecastingLink">Data Forecast</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <!-- Setting -->
                    <button class="btn btn-link p-0" id="settingsButton">
                        <i class="bi bi-gear" style="color: #333; font-size: 1.5rem;"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const currentPage = window.location.pathname.split("/").pop();

        // Check if "Data Forecasting" link is the same as the current page
        document.getElementById('dataForecastingLink').addEventListener('click', function(event) {
            if (currentPage === 'index.php') {
                event.preventDefault();
            }
        });

        // Check if settings button is pointing to the current page
        document.getElementById('settingsButton').addEventListener('click', function(event) {
            if (currentPage === 'setting.php') {
                event.preventDefault();
            } else {
                window.location.href = 'setting.php';
            }
        });
    });
</script>
