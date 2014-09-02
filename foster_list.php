<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

sec_session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Foster List</title>
	<?php include 'includes/header.inc'; ?>
</head>

<body class="_fp">

<?php 
include 'includes/menu.inc';

if($_SESSION['page'] == NULL) {
	echo "<input type='hidden' class='pass' value='NULL'>";
} else {
	echo "<input type='hidden' class='pass' value='".$_SESSION['page']."'>";
}
$_SESSION['page'] = "fp";
?>
<h1 id="page_title">Fosters</h1>
<div id="container">
	<?php
	$query = "SELECT idfoster, name FROM fosters";
	if ($result = $mysqli->query($query)) {
		$fosters = $result->fetch_all(MYSQLI_ASSOC);
	}

	foreach ($fosters as $foster) {
		echo "<div class='row'>";
		printf("<div class='foster_name row_element'>%s</div>", $foster['name']);

		if($stmt = $mysqli->prepare("SELECT name FROM cats WHERE fosters_idfoster=?")) {
			$stmt->bind_param('i', $foster['idfoster']);
			$stmt->execute();
			$stmt->bind_result($name);
			$cnt = 0;
			while ($stmt->fetch()) {
				$cnt++;
				if ($cnt > 5) {
					echo "</div><div class='row'><div class='foster_name row_element'></div>";
					$cnt = 0;
				}
				echo "<div class='cat_name row_element'>$name</div>";
			}
		}

		echo "</div>";
	}
	$mysqli->close(); 
	?>
</div>
</body>
</html>