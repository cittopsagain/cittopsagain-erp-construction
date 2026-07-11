<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo APP_NAME . ' - ' . $this->escape($title); ?></title>
</head>
<body>
<h1>404 - Page Not Found</h1>
<p>The page you are looking for does not exist.</p>
<a href="<?php echo(defined('BASE_URL') ? BASE_URL : '/'); ?>">Go back home</a>
</body>
</html>
