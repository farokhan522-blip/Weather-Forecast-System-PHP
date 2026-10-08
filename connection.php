<?php

function connect() {
    $SERVERNAME = "localhost";
    $USERNAME = "root";
    $PASSWORD = "";
    $DBNAME = "weather app";

    $conn = mysqli_connect($SERVERNAME, $USERNAME, $PASSWORD, $DBNAME);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $conn;
}
?>