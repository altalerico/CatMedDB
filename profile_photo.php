<?php
include_once 'D:/Users/Anthony/Documents/Website/includes/db_connect.php';

parse_str($_SERVER['QUERY_STRING']);

// $id = $_GET['id'];
$update_result1 = mysqli_query($con, "UPDATE photos SET selected=0 WHERE cats_idcat=$c");
$update_result2 = mysqli_query($con, "UPDATE photos SET selected=1 WHERE cats_idcat=$c AND idphoto=$p");
?>