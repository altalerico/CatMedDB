<?php
include_once 'includes/db_connect.php';
include_once 'includes/classes.php';
include_once 'includes/functions.php';

sec_session_start();
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Medical History Printout</title>
	<?php include_once 'includes/header.inc'; ?>
</head>

<body onload="window.print(), window.close()">
<?php
	parse_str($_SERVER['QUERY_STRING']);

	$cat = new cat($_SESSION['id'], $mysqli);

	if ($stmt = $mysqli->prepare("SELECT name FROM fosters WHERE idfoster=?")) {
		$stmt->bind_param("i", $cat->foster);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($foster_name);
		$stmt->fetch();
		$stmt->close();
	}

	echo "<br><label class='profile_label'>Name:</label>" . $cat->name;

	if ($foster_name) {
		echo "<br><label class='profile_label'>Foster:</label>" . $foster_name . "<br><br>";
	}

	$history = new cat_treatments($mysqli, $_SESSION['id'], 1, "name, date");
	
	$curr = "";
	foreach ($history->intersects as $intersect) {
		$date = date("m-d-y", strtotime($intersect->date));
		if ($intersect->name != $curr) {
			$cnt = 1;
			echo "<br><br>";
			echo "<div class='print_treatment'>" . $intersect->name . "</div>";
			echo "<div class='print_date'>$date</div>";
			$curr = $intersect->name;
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
	$mysqli->close();
?>
</body>
</html>