<?php
require_once __DIR__ . '/config/config.php';
require 'database/db_connect.php';
require 'database/sql_query.php';

// Initialize variables
$servername = isset($servername) ? $servername : '';
$username = isset($username) ? $username : '';
$password = isset($password) ? $password : '';
$dbname = isset($dbname) ? $dbname : '';
$table_name = isset($table_name) ? $table_name : '';
$name_column = isset($name_column) ? $name_column : '';
$date_column = isset($date_column) ? $date_column : '';
$length_prediction = isset($length_prediction) ? $length_prediction : '';

// Function to update global variables based on form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and update variables if form is submitted
    $servername = $_POST['servername'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dbname = $_POST['dbname'];
    $table_name = $_POST['table_name'];
    $name_column = $_POST['name_column'];
    $date_column = $_POST['date_column'];
    $length_prediction = $_POST['length_prediction'];
    
    // Prepare array with updated settings
    $config_data = array(
        "servername" => $servername,
        "username" => $username,
        "password" => $password,
        "dbname" => $dbname,
        "table_name" => $table_name,
        "name_column" => $name_column,
        "date_column" => $date_column,
        "length_prediction" => (int) $length_prediction
    );
    
    // Write updated settings to config.json
    $config_file = __DIR__ . '/config/config.json';

    if (file_exists($config_file)) {
        // Encode array as JSON and save to file
        $json_config = json_encode($config_data, JSON_PRETTY_PRINT);
        if (file_put_contents($config_file, $json_config) !== false) {
            $confirmation_message = "Settings saved successfully.";
        } else {
            $confirmation_message = "Failed to save settings. Please try again.";
        }
    } else {
        die('Configuration file not found.');
    }
}

// Get all columns from the selected table
$columns = getTableColumns($conn, $table_name);

// Close the database connection
closeDatabase($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Data Display with Forecasting</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Settings</h1>
        <?php if (isset($confirmation_message)) : ?>
            <div class="alert alert-<?php echo isset($json_config) ? 'success' : 'danger'; ?>" role="alert">
                <?php echo $confirmation_message; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="servername">Server Name</label>
                <input type="text" class="form-control" id="servername" name="servername" value="<?php echo htmlspecialchars($servername); ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
            </div>
            <div class="form-group">
                <label for="dbname">Database Name</label>
                <input type="text" class="form-control" id="dbname" name="dbname" value="<?php echo htmlspecialchars($dbname); ?>" required>
            </div>
            <div class="form-group">
                <label for="table_name">Table Name</label>
                <input type="text" class="form-control" id="table_name" name="table_name" value="<?php echo htmlspecialchars($table_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="name_column">Name Column</label>
                <select class="form-control" id="name_column" name="name_column" required>
                    <?php foreach ($columns as $column) : ?>
                        <option value="<?php echo $column; ?>" <?php echo ($column == $name_column) ? 'selected' : ''; ?>><?php echo $column; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="date_column">Date Column</label>
                <select class="form-control" id="date_column" name="date_column" required>
                    <?php foreach ($columns as $column) : ?>
                        <option value="<?php echo $column; ?>" <?php echo ($column == $date_column) ? 'selected' : ''; ?>><?php echo $column; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="length_prediction">Length of Prediction (minutes)</label>
                <input type="number" class="form-control" id="length_prediction" name="length_prediction" value="<?php echo htmlspecialchars($length_prediction); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>

        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">Back to Data Display</a>
        </div>
    </div>
</body>
</html>