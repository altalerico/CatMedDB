<?php
include_once 'includes/db_connect.php';

parse_str($_SERVER['QUERY_STRING']);

if ($stmt = $mysqli->prepare("UPDATE photos SET selected=0 WHERE cats_idcat=?")) {
	$stmt->bind_param("i", $c);
	$stmt->execute();
	$stmt->close();
}

if ($stmt = $mysqli->prepare("UPDATE photos SET selected=1 WHERE cats_idcat=? AND idphoto=?")) {
	$stmt->bind_param("ii", $c, $p);
	$stmt->execute();
	$stmt->close();
}
?>