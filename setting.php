<?php
// Load config.json
$configPath = 'config/config.json';
$config = json_decode(file_get_contents($configPath), true);

// Extract current settings
$servername = isset($config['servername']) ? htmlspecialchars($config['servername']) : '';
$username = isset($config['username']) ? htmlspecialchars($config['username']) : '';
$password = isset($config['password']) ? htmlspecialchars($config['password']) : '';
$dbname = isset($config['dbname']) ? htmlspecialchars($config['dbname']) : '';
$table_name = isset($config['table_name']) ? htmlspecialchars($config['table_name']) : '';
$name_column = isset($config['name_column']) ? htmlspecialchars($config['name_column']) : '';
$date_column = isset($config['date_column']) ? htmlspecialchars($config['date_column']) : '';
$length_prediction = isset($config['length_prediction']) ? htmlspecialchars($config['length_prediction']) : '';

$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = $_POST['servername'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dbname = $_POST['dbname'];

    // Validate the database connection
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->close();

        // If connection is successful, save the new settings
        $config = [
            'servername' => $servername,
            'username' => $username,
            'password' => $password,
            'dbname' => $dbname,
            'table_name' => $table_name,
            'name_column' => $name_column,
            'date_column' => $date_column,
            'length_prediction' => $length_prediction
        ];

        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
        $success_message = 'Settings saved successfully.';
    } catch (Exception $e) {
        // Show error message if connection fails
        $error_message = 'Failed to connect to the database. Please check your settings and try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'topbar.php'; ?>

    <div class="container mt-4">
        <h1>Update Database Configuration</h1>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php elseif (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="servername">Server Name</label>
                <input type="text" class="form-control" id="servername" name="servername" value="<?php echo $servername; ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" value="<?php echo $password; ?>" required>
            </div>
            <div class="form-group">
                <label for="dbname">Database Name</label>
                <input type="text" class="form-control" id="dbname" name="dbname" value="<?php echo $dbname; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</body>
</html>
