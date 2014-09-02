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
				$stmt->close();
			}
			printf("<div class='treatment_name %s'>%s</div>", $add_class, $row['name']);
			echo "</div>";
		}
		$result->close();
	}
	$mysqli->close(); 
	?>
</div>
</body>
</html>