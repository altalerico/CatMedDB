<?php
include_once 'includes/db_connect.php';

parse_str($_SERVER['QUERY_STRING']);

$delete = false;

switch ($action) {
	case 'received':
		$set = 1;
		break;
	case 'restore':
		$set = 0;
		break;
	case 'delete':
		$set = 1;
		$delete = true;
		break;
	default:
		break;
}

$stmt =  $mysqli->stmt_init();
$query = ($delete ? "UPDATE intersect_cat_treatment SET deleted=? WHERE idintersect=?" : 
	"UPDATE intersect_cat_treatment SET received=? WHERE idintersect=?");

if ($stmt = $mysqli->prepare($query)) {
	$stmt->bind_param("ii", $set, $id);
	$stmt->execute();
	$stmt->close();
}

$mysqli->close();
?>