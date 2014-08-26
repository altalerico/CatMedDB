<?php
include_once 'D:/Users/Anthony/Documents/Website/includes/db_connect.php';

parse_str($_SERVER['QUERY_STRING']);

switch ($action) {
	case 'received':
		$update_result = mysqli_query($con, "UPDATE intersect_cat_treatment SET received=1 WHERE idintersect=$id");
		break;
	case 'restore':
		$update_result = mysqli_query($con, "UPDATE intersect_cat_treatment SET received=0 WHERE idintersect=$id");
		break;
	case 'delete':
		$delete_result = mysqli_query($con, "DELETE FROM intersect_cat_treatment WHERE idintersect=$id");
		break;
	default:
		break;
}
mysqli_close($con);
?>