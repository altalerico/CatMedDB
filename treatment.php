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

			$value = ($_SESSION['page'] == null ? "null" : $_SESSION['page']);
			echo "<input type='hidden' class='pass' value='$value'>";
			$_SESSION['page'] = "tp";

			parse_str($_SERVER['QUERY_STRING']);

			$regimen = new regimen($id, $mysqli);

			// Treatment selected from list on previous page.
			if ($id) {  
				if ($stmt = $mysqli->prepare("SELECT name 
					FROM treatments 
					WHERE idtreatment=?")) {
					
					$stmt->bind_param("i", $id);
					$stmt->execute();
					$stmt->bind_result($treatment_name);
					$stmt->fetch();
					$stmt->close();
				}
				$login = login_check($mysqli);
				$mysqli->close();
			}
		?>
		<h1 id="page_title"><?php echo "$treatment_name Regimen" ?></h1>
		<div id="container">
			<?php if ($login == 'super') : ?>
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
								for ($i = 0; $i < $interval_cnt; $i++) {
									if ($interval = $regimen->intervals[$i]) {
										$value = $interval->value;
										$unit = $interval->unit;
										$count = $interval->count;
									} else {
										$value = 0;
										$unit = "";
										$count = 1;
									}

									$r_text = ($i == 0 ? "First" : "Then");
									$r_style = ($value == 0 ? "visibility: hidden" : 
										"visibility: visible");
									if ($i == 0 and $regimen->intervals[1] == null) {
										$r_style = "visibility: hidden";
									}
									echo "<div class='interval_label' 
										id='r_text $i'
										style='$r_style'>$r_text</div>";

									// Number of days/weeks/etc between treatments.
									echo "<select name='value[]'
										class='value' id='value $i' 
										onchange='update_unit(this)'>";
									for ($v = 0; $v < 10; $v++) {
										$selected = ($v == $value ? "selected='$selected'" : "");

										switch ($v) { 
											case 0:
												$text = "";
												break;
											case 1:
												$text = "once a";
												break;
											default:
												$text = "every $v";
												break;
										}

										if ($r_style == "visibility: hidden" and $i == 0) {
											$text = ucfirst($text);
										}
										echo "<option value='$v' $selected>$text</option>";
									}
									echo "</select>";

									// Units of time for the interval.
									echo "<select name='unit[]' 
										id='unit $i' 
										class='unit' 
										onchange='update_count(this)'>";
									$units = array("", "day", "week", "month", "year");
									foreach ($units as $u) {
										$selected = ($u == $unit ? "selected='$selected'" : "");
										$text = (($value > 1 and $u != "") ? $u . "s" : $u);
										echo "<option value='$u' $selected>$text</option>";
									}
									echo "</select>";

									// The number of times an interval should be count.
									$style = ($unit == "" ? "visibility: hidden" : 
										"visibility: visible");
									echo "<select name='count[]' 
										id='count $i' 
										class='count'
										onchange='update_value(this)'
										style='$style'>";
									for ($c = 1; $c < 12; $c++) {
										$selected = ($count == $c ? "selected='selected'" : "");
										switch ($c) {
											case 1:
												$text = "";
												break;
											case 11:
												$text = "indefinitely.";
												break;
											default:
												if ($i > 0) {
													$product = $value * $c;
												} else {
													$product = $value * ($c - 1);
												}

												if ($unit == "day") {
													$product++;
												}
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
			<?php else : ?>
				<span class="error">You are not authorized to access this page.</span>
			<?php endif; ?>
		</div>
	</body>
</html>