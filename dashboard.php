<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/classes.php';

sec_session_start();
?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title>Dashboard</title>
		<?php include_once 'includes/header.inc'; ?>
	</head>

	<body class="_dp">
		<?php 
			include 'includes/menu.inc';

			$value = ($_SESSION['page'] == null ? "null" : $_SESSION['page']);
			echo "<input type='hidden' class='pass' value='$value'>";
			$_SESSION['page'] = "dp";
		?>
		<div id = "wrapper">
			<?php if (login_check($mysqli)) : ?>
				<div id="db_container_left">
					<h1 id="petsmart_label">At PetSmart</h1>
					<?php
					$style = (login_check($mysqli) == "super" ? "visibility: visible" : "visibility: hidden");

					$uncheck_tooltip = "Received: When treatment has been administered.";

					$today = date("Y-m-d", mktime());
					$week = date("Y-m-d", strtotime('next week'));

					$first_missed = true;
					$first_today = true;
					$first_week = true;

					$petsmart_treatments = new location_treatments("PetSmart", $mysqli);
					$foster_treatments = new location_treatments("Foster", $mysqli);

					foreach ($petsmart_treatments->rows as $row) {
						$intersect_id = $row[0];
						$treatment_name = $row[1];
						$cat_id = $row[2];
						$cat_name = $row[3];
						$print_date = date("M j", strtotime($row[4]));
						$scheduled_date = date("Y-m-d", strtotime($row[4]));
						
						$click_action = "window.location.href='cat.php?id=$cat_id'";

						if ($scheduled_date < $today and $first_missed) {
							$first_missed = false;
							echo "<div class='dashboard_treatments missed_border db_petsmart'>";
							echo "<span class='status missed_text'>Missed</span>";
						} elseif ($scheduled_date == $today and $first_today) {
							$first_today = false;
							if (!$first_missed) { echo "</div>"; }
							echo "<div class='dashboard_treatments today_border db_petsmart'>";
							echo "<span class='status today_text'>Today</span>";
						} elseif ($scheduled_date > $today and $first_week) {
							$first_week = false;
							if (!$first_missed or !$first_today) { echo "</div>"; }
							echo "<div class='dashboard_treatments week_border db_petsmart'>";
							echo "<span class='status week_text'>Week</span>";
						}

						echo "<div class='row'>";
						echo "<div class='db_date row_element'>$print_date</div>";
						echo "<div class='cat_name row_element' onClick=$click_action>$cat_name</div>";
						echo "<div class='treatment_name row_element'>$treatment_name</div>";;
						echo "<div class='box_submit uncheck drop'
									title='$uncheck_tooltip'
									id='$intersect_id'
									style='$style'></div>";
						echo "</div>";
					}

					if ($petsmart_treatments->rows.sizeof() != 0) {
						echo "</div>";
					}
					?>
				</div>
				<div id="db_container_right">
					<h1 id="foster_label">With Foster</h1>
					<?php
					$first_missed = true;
					$first_today = true;
					$first_week = true;

					foreach ($foster_treatments->rows as $row) {
						$intersect_id = $row[0];
						$treatment_name = $row[1];
						$cat_id = $row[2];
						$cat_name = $row[3];
						$print_date = date("M j", strtotime($row[4]));
						$scheduled_date = date("Y-m-d", strtotime($row[4]));

						$click_action = "window.location.href='cat.php?id=$cat_id'";

						if ($stmt = $mysqli->prepare("SELECT name FROM fosters WHERE idfoster=?")) {
							$stmt->bind_param("i", $row[5]);
							$stmt->execute();
							$stmt->bind_result($foster_name);
							$stmt->fetch();
							$stmt->close();
						}

						if ($scheduled_date < $today and $first_missed) {
							$first_missed = false;
							echo "<div class='dashboard_treatments missed_border db_foster'>";
							echo "<span class='status missed_text'>Missed</span>";
						} elseif ($scheduled_date == $today and $first_today) {
							$first_today = false;
							if (!$first_missed) { echo "</div>"; }
							echo "<div class='dashboard_treatments today_border db_foster'>";
							echo "<span class='status today_text'>Today</span>";
						} elseif ($scheduled_date > $today and $first_week) {
							$first_week = false;
							if (!$first_missed or !$first_today) { echo "</div>"; }
							echo "<div class='dashboard_treatments week_border db_foster'>";
							echo "<span class='status week_text'>Week</span>";
						}

						echo "<div class='row'>";
						echo "<div class='db_date row_element'>$print_date</div>";
						echo "<div class='cat_name row_element' onClick=$click_action>$cat_name</div>";
						echo "<div class='treatment_name row_element'>$treatment_name</div>";
						echo "<div class='foster_name row_element'>$foster_name</div>";
						echo "<div class='box_submit uncheck drop_r'
							title='$uncheck_tooltip'
							id='$intersect_id'
							style='$style'></div>";
						echo "</div>";
					}

					if ($foster_treatments->rows.sizeof() != 0) {
						echo "</div>";
					}
					$mysqli->close();
					?>
				</div>
			<?php else : ?>
				<span class="error">You are not authorized to access this page.</span>
			<?php endif; ?>
		</div>
	</body>
</html>