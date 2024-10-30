<?php

//TestTest123

$hostName = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "CounterCalories";
$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);
if (!$conn) {
    die("Something went wrong;");
}

?>