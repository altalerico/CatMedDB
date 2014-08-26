<!DOCTYPE html>
<html lang="en">
<head>
	<title>Course of Treatment</title>
	<?php include 'header.inc'; ?>
</head>

<body class="_tp">

<?php 
include 'menu.inc';
include 'functions.php';
include 'classes.php';
include 'D:/Users/Anthony/Documents/mysqlconnect.php';

session_start();
if($_SESSION['page'] == NULL) {
	echo "<input type='hidden' class='pass' value='NULL'>";
} else {
	echo "<input type='hidden' class='pass' value='".$_SESSION['page']."'>";
}
$_SESSION['page'] = "tp";
parse_str($_SERVER['QUERY_STRING']);
$time_units_array = array("Days","Weeks","Months", "Years");
$interval_cnt = 4;
?>

<div class="container">
<?php
	$regimen = new regimen();

	if(isset($_POST['treatment'])) {  // Submit button pressed.
		$treatment_name = trim($_POST['treatment']);
		$treatment_result = mysqli_query($con, sprintf("SELECT * FROM treatments WHERE name='%s'", $_POST['treatment']));
		$sql_treatment = mysqli_fetch_array($treatment_result);
		$id = $sql_treatment['idtreatment'];

		for($i = 0; $i < $interval_cnt; $i++) {
			$priority = $i + 1;
			if($_POST['value'][$i] != NULL) {
				$days = calc_days($_POST['value'][$i], $_POST['unit'][$i]);
				
				$interval = new interval($id, $priority, $days, $_POST['repeat'][$i]);
				$regimen->add_interval($interval);
			}
		}
	} else if($id != NULL) {  // Selected treatment from list.
		$treatment_result = mysqli_query($con,"SELECT * FROM treatments WHERE idtreatment=" . $id);
		$sql_treatment = mysqli_fetch_array($treatment_result);
		$treatment_name = $sql_treatment['name'];

		$sql_intervals = mysqli_query($con, sprintf("
			SELECT * FROM treatment_interval
			WHERE treatments_idtreatment=%u ORDER BY priority",
			$id)
		);

		while($sql_interval = mysqli_fetch_array($sql_intervals)) {
			$interval = new interval($id, $sql_interval['priority'], $sql_interval['days_until_treatment'], $sql_interval['repeated']);
			$regimen->add_interval($interval);
		}
	}
?>
<h1>Course of Treatment</h1><br>
<form method="post">

	<label for="treatment">Treatment</label>
	<select name="treatment" id="treatment">
		<?php
		if($treatment_name == NULL) {
			echo "<option selected='selected'></option>";
		}
		$treatments_result = mysqli_query($con,"SELECT * FROM treatments");
		while($treatment = mysqli_fetch_array($treatments_result)) {
			if($treatment['name'] == $treatment_name) {
				printf("<option selected='selected'> %s </option>", $treatment['name']);
			} else {
				printf("<option> %s </option>", $treatment['name']);
			}
		}
		?>
	</select>
	<input type="submit" class="submit" value="Submit"><br>

	<h2 class="course_label interval">Intervals</h2>
	<div class="course">

		<span >Initial Treatment</span>
		<br><br>

	<?php
	for($i = 0; $i < $interval_cnt; $i++) {
		$interval = $regimen->intervals[$i];

		switch(true)
		{
		case $interval->days >= 365:
			$value = $interval->days / 365;
			$unit = "Years";
			break;
		case $interval->days >= 30:
			$value = $interval->days / 30;
			$unit = "Months";
			break;
		case $interval->days >= 7:
			$value = $interval->days / 7;
			$unit = "Weeks";
			break;
		case $interval->days >= 1:
			$value = $interval->days;
			$unit = "Days";
			break;
		default:
			$value = NULL;
			$unit = "";
			break;
		}
	?>

	<!--Text input and select to set and display intervals between treatments.-->
	<input type="text" class="value" name="value[]" value="<?php echo $value; ?>">
	<select name="unit[]" class="units">
		<option></option>
		<?php
		foreach($time_units_array as $u) {
			if($u == $unit) {
				echo "<option selected='selected'>".$u."</option>";
			} else {
				echo "<option>".$u."</option>";
			}
		}
		?>
	</select>

	<!--Select to set and display the number of times an interval should be repeated.-->
	Repeat
	<select name="repeat[]" class="repeat">
		<?php
		for($r = 1; $r < 12; $r++) {
			switch($r)
			{
			case 1:
				echo "<option value='1'></option>";
				break;
			case 11:
				if($interval->repeat == 11) {
					echo "<option value='11' selected='selected'>Indefinitely</option>";
				} else {
					echo "<option value='11'>Indefinitely</option>";
				}				
				break;
			default:
				if($interval->repeat == $r) {
					echo "<option value='".$r."' selected='selected'>".$r." Times</option>";
				} else {
					echo "<option value='".$r."'>".$r." Times</option>";
				}				
			}
		}
		?>
	</select><br><br>
<?php
}
?>
</div>
<?php mysqli_close($con); ?>
</form>
</div>
</body>
</html>