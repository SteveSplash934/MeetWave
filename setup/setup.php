<?php
// setup/setup.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appName = $_POST['app_name'];
    $dbHost = $_POST['db_host'];
    $dbUser = $_POST['db_user'];
    $dbPassword = $_POST['db_password'];
    $dbName = $_POST['db_name'];

    // Write to config.php
    $configContent = "<?php\n\nreturn [\n";
    $configContent .= "    'app_name' => '$appName',\n";
    $configContent .= "    'database' => [\n";
    $configContent += "        'host' => '$dbHost',\n";
    $configContent += "        'user' => '$dbUser',\n";
    $configContent += "        'password' => '$dbPassword',\n";
    $configContent += "        'dbname' => '$dbName'\n";
    $configContent += "    ],\n";
    $configContent += "];\n";

    file_put_contents('../src/config/config.php', $configContent);
    echo "Configuration saved successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup MeetWave</title>
</head>
<body>
    <h1>Setup MeetWave Configuration</h1>
    <form method="POST">
        <label>App Name:</label>
        <input type="text" name="app_name" required><br>

        <label>Database Host:</label>
        <input type="text" name="db_host" required><br>

        <label>Database User:</label>
        <input type="text" name="db_user" required><br>

        <label>Database Password:</label>
        <input type="password" name="db_password" required><br>

        <label>Database Name:</label>
        <input type="text" name="db_name" required><br>

        <input type="submit" value="Save Configuration">
    </form>
</body>
</html>
