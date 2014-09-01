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
<div id="title_left">
	<h1>Course of Treatment List</h1>
</div>
<div id="container_left">
	<?php
		$treatments = mysqli_query($mysqli, sprintf("SELECT * FROM treatments"));
	?>
	<form method="post">
	<?php
		while($treatment = mysqli_fetch_array($treatments)) {
			// echo $treatment['name'];
			$intervals = mysqli_query($mysqli, sprintf("
				SELECT * FROM treatment_interval
				WHERE treatments_idtreatment=%u ORDER BY priority",
				$treatment['idtreatment'])
			);

			$treatment_url = "treatment.php?id=".$treatment['idtreatment'];

			if(mysqli_fetch_array($intervals) != NULL) {
				?>
				<span class="course" title="Edit the course of treatment for <?php echo $treatment['name']; ?>."
					onClick="window.location.href='<?php echo $treatment_url; ?>'">
					<?php echo $treatment['name']; ?>
				</span>
			<?php
			} else {
				?>
				<span class="missing_course" title="Create a course of treatment for <?php echo $treatment['name']; ?>."
					onClick="window.location.href='<?php echo $treatment_url; ?>'">
					<?php echo $treatment['name']; ?>
				</span>
				<?php
			}
			echo "<br>";
		}

		mysqli_close($mysqli); 
	?>
	</form>
</div>
</body>
</html>