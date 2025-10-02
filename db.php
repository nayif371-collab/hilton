<?php
// db.php
// Database connection details (from you)
$DB_HOST = 'localhost';
$DB_NAME = 'dbduiiuo3xdtsf';
$DB_USER = 'uhbgtuxfhy4wg';
$DB_PASS = 'oramgejfijqa';
 
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
 
