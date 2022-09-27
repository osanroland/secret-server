<?php
require __DIR__ . "/inc/bootstrap.php";
 
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

require PROJECT_ROOT_PATH . "/Controller/Api/SecretController.php";
 
$controller = new SecretController();
$controller->processRequest($uri);
?>
