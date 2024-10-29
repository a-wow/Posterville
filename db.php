<?php
$DB_HOST = 'localhost';
$DB_USER = 'username';
$DB_PASS = 'password';
$DB_NAME = 'base';

$db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$db->set_charset("utf8");
?>
