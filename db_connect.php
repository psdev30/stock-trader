<?php
$host = "localhost";
$user = "psj4";
$password = "Student_4262639";
$dbName = "psj4";

$connection = mysqli_connect($host, $user, $password, $dbName);
if(mysqli_connect_errno())
    die("Database connection has failed: " . mysqli_connect_error() . " (" . mysqli_connect_errno() . ")");

?>