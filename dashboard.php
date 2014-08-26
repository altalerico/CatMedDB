<?php
include_once 'D:/Users/Anthony/Documents/Website/includes/db_connect.php';

session_start();
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Dashboard</title>
	<?php include_once 'header.inc'; ?>
</head>

<body class="_dp">
<?php 
	include 'menu.inc';


	if($_SESSION['page'] == NULL) {
		echo "<input type='hidden' class='pass' value='NULL'>";
	} else {
		echo "<input type='hidden' class='pass' value='".$_SESSION['page']."'>";
	}
	$_SESSION['page'] = "dp";

	if(isset($_POST['check'])) {
		$received_update = mysqli_query($con, sprintf("
			UPDATE `intersect_cat_treatment` 
			SET `received`=1 
			WHERE `idintersect`=%u;", $_POST['check'])
		);
	}
?>
<div id = "wrapper">

	<div id="db_container_left">
		<h1 id="petsmart_label">At PetSmart</h1>
		<?php
		$uncheck_tooltip = "Received: When treatment has been administered.";

		$today = date("Y-m-d", mktime());
		$week = date("Y-m-d", strtotime('next week'));

		$first_missed = true;
		$first_today = true;
		$first_week = true;

		$join_result = mysqli_query($con, "
			SELECT * FROM intersect_cat_treatment 
			INNER JOIN cats
			ON cats_idcat = idcat 
			WHERE received = 0 AND location = 'PetSmart' AND date < '$week'
			ORDER BY date ASC;"
		);
		while($join_row = mysqli_fetch_array($join_result)) {
			$intersect_id = $join_row['idintersect'];
			$treatment_id = $join_row['treatments_idtreatment'];
			$treatment_result = mysqli_query($con, "SELECT * FROM treatments WHERE idtreatment = $treatment_id");				
			$treatment_row = mysqli_fetch_array($treatment_result);
			$cat_name = $join_row['name'];
			$cat_id = $join_row['idcat'];
			$click_action = "window.location.href='cat.php?id=$cat_id'";
			$print_date = date("M j", strtotime($join_row['date']));
			$scheduled_date = date("Y-m-d", strtotime($join_row['date']));
			$treatment_name = $treatment_row['name'];

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

			echo "<div class='treatment_row'>";
			echo "<div class='db_date row'>$print_date</div>";
			echo "<div class='cat_name row' onClick=$click_action>$cat_name</div>";
			echo "<div class='db_treatment row'>$treatment_name</div>";;
			echo "<div 	class='box_submit uncheck'
						title = '$uncheck_tooltip'
						onclick = 'check_box_recieved(this, $intersect_id)'></div>";
			echo "</div>";
		}
		echo "</div>";
		?>
	</div>
	<div id="db_container_right">
		<h1 id="foster_label">With Foster</h1>
		<?php
		$first_missed = true;
		$first_today = true;
		$first_week = true;

		$foster_join_result = mysqli_query($con, "
			SELECT * FROM intersect_cat_treatment 
			INNER JOIN cats
			ON cats_idcat = idcat 
			WHERE received = 0 AND location = 'Foster' AND date < '$week'
			ORDER BY date ASC;"
		);
		while($foster_join_row = mysqli_fetch_array($foster_join_result)) {
			$intersect_id = $foster_join_row['idintersect'];
			$treatment_id = $foster_join_row['treatments_idtreatment'];
			$treatment_result = mysqli_query($con, "SELECT * FROM treatments WHERE idtreatment = $treatment_id");				
			$treatment_row = mysqli_fetch_array($treatment_result);

			$cat_name = $foster_join_row['name'];
			$cat_id = $foster_join_row['idcat'];
			$foster_id = $foster_join_row['fosters_idfoster'];

			$foster_result = mysqli_query($con, "SELECT * FROM fosters WHERE idfoster = $foster_id");				
			$foster_row = mysqli_fetch_array($foster_result);
			$foster_name = $foster_row['name'];

			$click_action = "window.location.href='cat.php?id=$cat_id'";
			$print_date = date("M j", strtotime($foster_join_row['date']));
			$scheduled_date = date("Y-m-d", strtotime($foster_join_row['date']));
			$treatment_name = $treatment_row['name'];

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

			echo "<div class='treatment_row'>";
			echo "<div class='db_date row'>$print_date</div>";
			echo "<div class='cat_name row' onClick=$click_action>$cat_name</div>";
			echo "<div class='db_treatment row'>$treatment_name</div>";
			echo "<div class='db_foster_name row'>$foster_name</div>";
			echo "<div 	class='box_submit uncheck' 
						title = '$uncheck_tooltip'
						onclick = 'check_box_recieved(this, $intersect_id)'></div>";
			echo "</div>";
		}
		echo "</div>";
		mysqli_close($con);
		?>
		</div>
	</div>
</div>
</body>
</html>