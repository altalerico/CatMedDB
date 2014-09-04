<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/classes.php';

sec_session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Treatment List</title>
	<?php include 'includes/header.inc'; ?>
</head>

<body class="_tp">

<?php 
include 'includes/menu.inc';

if($_SESSION['page'] == NULL) {
	echo "<input type='hidden' class='pass' value='NULL'>";
} else {
	echo "<input type='hidden' class='pass' value='".$_SESSION['page']."'>";
}
$_SESSION['page'] = "tp";
?>
<h1 id="page_title">Treatments</h1>
<div id="container">
	<?php
	$query = "SELECT idtreatment, name FROM treatments";
	if ($result = $mysqli->query($query)) {
		while ($row = $result->fetch_assoc()) {
			echo "<div class='row'>";
			if ($stmt = $mysqli->prepare("SELECT idinterval 
				FROM treatment_interval 
				WHERE treatments_idtreatment=?"))
			{
				$stmt->bind_param('i', $row['idtreatment']);
				$stmt->execute();
				$stmt->store_result();
				$add_class = ($stmt->num_rows == 0 ? "missed_text" : "");
				if ($stmt2 = $mysqli->prepare("SELECT value, unit, count 
					FROM treatment_interval 
					WHERE treatments_idtreatment=? 
					ORDER BY priority")) 
				{
					$stmt2->bind_param('i', $row['idtreatment']);
					$stmt2->execute();
					$stmt2->store_result();
					$stmt2->bind_result($value, $unit, $count);
					$text = "";
					while ($stmt2->fetch()) {
						if ($value == 1) {
							$interval_text = "Once a $unit ";
						} else {
							$unit = $unit . "s";
							$interval_text = "Every $value $unit ";
						}

						if ($count == 1) {
							$unit_text = ($value == 1 ? "a $unit" : "$value $unit");
							$text = "Second treatment $unit_text after initial.";
						} else {
							if($value == 1) {
								$unit = $unit . "s";
							}
							$value_text = $value * $count;
							$duration = ($count == 11 ? "indefinitely." : "for $value_text $unit.");
							$text = ($text == "" ? "$interval_text $duration" : "$text Then " . lcfirst ($interval_text) . $duration);
						}
					}
					$stmt2->close();
				}
				$stmt->close();
			}
			printf("<div class='treatment_name row_element %s' onclick='treatment_url(%s)'>%s</div>", 
				$add_class, $row['idtreatment'], $row['name']);
			echo "<div class='description row_element'>$text</div>";
			echo "</div>";
		}
		$result->close();
	}
	$mysqli->close(); 
	?>
</div>
</body>
</html>