<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/classes.php';

sec_session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Course of Treatment</title>
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
parse_str($_SERVER['QUERY_STRING']);
?>

<div class="container">
<?php
	$regimen = new regimen($mysqli, $id);

	if($id) {  // Selected treatment from list.
		if ($stmt = $mysqli->prepare("SELECT name FROM treatments WHERE idtreatment=?")) {
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->bind_result($treatment_name);
			$stmt->fetch();
			$stmt->close();
		}
		$mysqli->close();
	}
?>
<h1 id="page_title">Course of Treatment</h1>
<div id="container">
	<div class="card">
		<form method="post" action="process.php">
		<div class="header">
			<img class="paw" src="img/paw.png">
			<h2 class="div_title">Intervals</h2>
			<input type="submit" class="profile_submit" value="Submit">
		</div>
		<div class="content">
			<label for="regimen" class="card">Treatment</label>
			<input type="text" name="regimen" value="<?php echo $treatment_name ?>"><br><br>
			<?php
			$interval_cnt = 4;
			for($i = 0; $i < $interval_cnt; $i++) {
				if ($interval = $regimen->intervals[$i]) {
					$value = $interval->value;
					$unit = $interval->unit;
					$count = $interval->count;
				} else {
					$value = 0;
					$unit = "";
					$count = 0;
				}

				// Number of days/weeks/etc between treatments.
				echo "<select name='value[]' class='value' id='value $i' onchange='update_unit(this)'>";
					for ($v = 0; $v < 10; $v++) {
						$selected = ($v == $value ? "selected='$selected'" : "");

						switch ($v) {
							case 0:
								$text = "";
								break;
							case 1:
								$text = ($count == 1 ? "After 1" : "Once a");
								break;
							default:
								$text = ($count == 1 ? "After $v" : "Every $v");
								break;
						}
						echo "<option value='$v' $selected>$text</option>";
					}
				echo "</select>";

				// Units of time for the interval.
				echo "<select name='unit[]' id='unit $i' class='unit' onchange='update_count(this)'>";
					$units = array("", "day", "week", "month", "year");
					foreach($units as $u) {
						$selected = ($u == $unit ? "selected='$selected'" : "");
						$text = ($value > 1 ? $u . "s" : $u);
						echo "<option value='$u' $selected>$text</option>";
					}
				echo "</select>";

				// The number of times an interval should be count.
				$style = ($unit == "" ? "visibility: hidden" : "visibility: visible");
				echo "<select name='count[]' 
					id='count $i' 
					class='count'
					onchange='update_value(this)'
					style='$style'>";
					for ($c = 0; $c < 12; $c++) {
						$selected = ($count == $c ? "selected='selected'" : "");
						switch ($c) {
							case 0:
								$text = "";
								break;
							case 1:
								$text = "single treatment.";
								break;
							case 11:
								$text = "indefinitely.";
								break;
							default:
								$product = $value * $c;
								$text = ($value == 0 ? "" : "for $product $unit" . "s.");
						}
						echo "<option value='$c' $selected>$text</option>";
					}
				echo "</select>";
			}
			?>
		</div>
		</form>
	</div>
</div>
</body>
</html>