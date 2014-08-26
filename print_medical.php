<?php
include_once 'D:/Users/Anthony/Documents/Website/includes/db_connect.php';

session_start();
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Medical History Printout</title>
	<?php include_once 'header.inc'; ?>
</head>

<body class="_cp" onload="window.print(), window.close()">
<?php
	parse_str($_SERVER['QUERY_STRING']);

	$cat_result = mysqli_query($con, "SELECT * FROM cats WHERE idcat = $id;");
	$cat_row = mysqli_fetch_array($cat_result);
	$cat_name = $cat_row['name'];
	$foster_id = $cat_row['fosters_idfoster'];

	$foster_result = mysqli_query($con, "SELECT name FROM fosters WHERE idfoster = $foster_id;");
	$foster_row = mysqli_fetch_array($foster_result);
	$foster_name = $foster_row['name'];

	echo "<br><label class='profile_label'>Name:</label>$cat_name";

	if ($foster_name) {
		echo "<br><label class='profile_label'>Foster:</label>$foster_name<br><br>";
	}

	$join_result = mysqli_query($con, "
		SELECT `intersect_cat_treatment`.`date`,  `treatments`.`name`
		FROM intersect_cat_treatment 
		JOIN treatments
		ON `intersect_cat_treatment`.`treatments_idtreatment` = `treatments`.`idtreatment`
		WHERE received = 1 AND cats_idcat = $id
		ORDER BY name, date;"
	);
	$curr = "";
	while ($join_row = mysqli_fetch_array($join_result)) {
		$treatment = $join_row['name'];
		$date = $scheduled_date = date("m-d-y", strtotime($join_row['date']));

		if ($treatment != $curr) {
			$cnt = 1;
			echo "<br><br>";
			echo "<div class='print_treatment'>$treatment</div>";
			echo "<div class='print_date'>$date</div>";
			$curr = $treatment;
		} else {
			$cnt += 1;
			echo "<div class='print_date'>$date</div>";
			if ($cnt == 6) { 
				echo "<br>";
				echo "<div class='print_treatment'></div>"; 
				$cnt = 0;
			}
		}
	}
	mysqli_close($con);
?>
</body>
</html>