<?php
include_once 'D:/Users/Anthony/Documents/Website/includes/db_connect.php';
include_once 'D:/Users/Anthony/Documents/Website/includes/functions.php';

session_start();
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Cat Profile</title>
	<?php include_once 'header.inc'; ?>
</head>

<body class="_cp">
<?php

	include_once 'menu.inc';

	parse_str($_SERVER['QUERY_STRING']);

	$_SESSION['id'] = $id;
	$_SESSION['redirect'] = "cat.php?id=";

	// Session variable tracking previous page to animate the line under header menu options.
	if ($_SESSION['page'] == NULL) {
		echo "<input type='hidden' class='pass' value='NULL'>";
	} else {
		echo "<input type='hidden' class='pass' value='".$_SESSION['page']."'>";
	}
	$_SESSION['page'] = "cp";

	// SQL queries of the database to get relevant cat info and set respective variables.
	$cat_result = mysqli_query($con,"SELECT * FROM cats WHERE idcat=$id");
	$cat = mysqli_fetch_array($cat_result);
	$name = $cat['name'];
	$foster_id = $cat['fosters_idfoster'];
	$notes = $cat['notes'];

	$photos_result = mysqli_query($con,"SELECT * FROM photos WHERE cats_idcat=$id");
	if (mysqli_num_rows($photos_result) == 0) {
		$selected_photo_file = "default.jpg";
	} else {
		$photo_sql_array = array ();
		while ($photo_row = mysqli_fetch_array($photos_result)) {
			if ($photo_row['selected']) {
				$selected_photo_file = str_replace(" ", "%20", $photo_row['file']);
				// $selected_photo_file = $photo_row['file'];
			}
			array_push($photo_sql_array, $photo_row);
		}
	}

	$treatment_array = array ();
	$treatments_result = mysqli_query($con,"SELECT * FROM treatments");				
	while ($treatment_row = mysqli_fetch_array($treatments_result)) {
		array_push($treatment_array, $treatment_row['name']);
	}

	$foster_array = array ("");
	$fosters_result = mysqli_query($con, "SELECT * FROM fosters");
	while ($foster_row = mysqli_fetch_array($fosters_result)) {
		if ($foster_row['idfoster'] == $foster_id) {
			$foster_name = $foster_row['name'];
		}
		array_push($foster_array, $foster_row['name']);
	}

	$document_array = array ();
	$documents_result = mysqli_query($con, "SELECT * FROM documents WHERE cats_idcat=$id");
	while ($document_row = mysqli_fetch_array($documents_result)) {
		if (file_exists("upload/".$document_row['file'])) {
			array_push($document_array, $document_row['file']);
		}
	}
	?>	

<!-- Cat's profile -->
	<?php 
	echo "<h1 id='profile_name'>$name's Profile</h1>";
	?>
<div id = "wrapper">

	<div id = "container_left">

		<!-- Information box -->
		<div class="profile_div">
			<form method="post" action="process.php">
			<div class="profile_div_header">
				<img class="profile_paw" src="img/paw.png">
				<h2 class="div_title" id="info_title">Information</h2>
				<input type="submit" class="profile_submit" value="Update">
			</div>
			<div class="profile_div_content">
				<label for="name" class="profile_label">Name</label>
				<input type="text" id="name_input" name="name" value="<?php echo $name; ?>"><br>

				<label for="sex" class="profile_label">Sex</label>
				<select name="sex" id="sex">
				<?php
					$sex_array = array ("", "Female","Spayed Female","Male","Neutered Male");
					foreach ($sex_array as $sex) {
						$selected = ($sex == $cat['sex'] ? "selected='selected'" : "");
						echo "<option $selected>$sex</option>";
					}
				?>
				</select><br>
				
				<label for="location" class="profile_label">Location</label>
				<select name="location" id="location" onchange="fosterToggle()">
				<?php
					$location_array = array ("", "Adopted", "Foster", "PetSmart");
					foreach ($location_array as $location) {
						$selected = ($location == $cat['location'] ? "selected='selected'" : "");
						echo "<option $selected>$location</option>";
					}
				?>
				</select>
				
				<?php
				$hidden = ($cat['location'] == "Foster" ? "" : "hidden");
				echo "<select name='foster' id='foster_toggle' class='$hidden'>";

				foreach ($foster_array as $f) {
					$selected = ($f == $foster_name ? "selected='selected'" : "");
					echo "<option $selected>$f</option>";
				}
				?>
				</select>
				
				<?php
				if (isset($cat['dob'])) {
					$birth = date_create($cat['dob']);
					$now = date_create("now");
					$year = date('Y')-1;
					$interval = date_diff($birth, $now);
				
					if ($interval->format('%y') >= 1) {
						$age_value = $interval->format('%y');
						$age_units = "Years";
					} elseif ($interval->format('%m') >= 1) {
						$age_value = $interval->format('%m');
						$age_units = "Months";
					} else {
						$days = $interval->format('%d');
						$age_value = (int) ($days / 7);
						$age_units = "Weeks";
					}
				}
				?>
				<label for="age_value" class="profile_label">Age/dob</label>
				<input type="text" id="age_input" name="age_value" value="<?php echo $age_value; ?>">
				<select name="age_units" id="age_select">
				<?php
					$age_units_array = array ("","Weeks","Months","Years");
					foreach($age_units_array as $unit) {
						$selected = ($unit == $age_units ? "selected='selected'" : "");
						echo "<option $selected>$unit</option>";
					}
					?>
				</select>
				<input type="text" id="dob" name="dob" placeholder="yyyy-mm-dd" value="<?php echo $cat['dob']; ?>"><br>
				
				<label for="notes" class="profile_label">Notes</label>
				<textarea name="notes" id="notes" rows="3" cols="32"><?php echo $notes ?></textarea>
			</div>
			</form>
		</div>

		<!-- New treatment box -->
		<div class="profile_div">
			<form method="post" action="process.php" class="profile_form">
			<div class="profile_div_header">
				<img class="profile_paw" src="img/paw.png">
				<h2 class="div_title" id="new_treatment_title">New Treatment</h2>
				<input type="submit" class="profile_submit" value="Schedule">
			</div>
			<div class="profile_div_content" id="new_treatment_content">
				<label for="treatment" class="profile_label">Treatment</label>
				<select name="treatment" id="treatment">
					<option></option>
					<?php
					foreach ($treatment_array as $treatment) {
						echo "<option>$treatment</option>";
					}
					?>
				</select><br>

				<label for="treatment_type" class="profile_label">Type</label>
				<select name="treatment_type" id="treatment_type">
					<option></option>
					<option value="1">Single Treatment</option>
					<option value="2">Course of Treatment</option>
				</select><br>

				<label for="datepicker" class="profile_label">Date</label>
				<input type="text" name="date" id="datepicker" />
			</div>
			</form>
		</div> 

		<!-- Scheduled treatments box. -->
		<div class="profile_div">
			<div class="profile_div_header">
				<img class="profile_paw" src="img/paw.png">
				<h2 class="div_title" id="scheduled_treatments_title">Scheduled Treatments</h2>
				<input type="button" 
					class="button" 
					id="print_cat_btn" 
					value="Print Medical" 
					onClick="window.open('print_medical.php?id=<?php echo $id ?>')">
			</div>
			<div class="profile_div_content">
				<?php
				$any_missed = false;
				$any_today = false;
				$any_upcoming = false;

				$today = date("Y-m-d", mktime());

				$uncheck_tooltip = "Received: When treatment has been administered.";
				$check_tooltip = "Restore:  Treatment has NOT been administered.";
				$x_tooltip = "Delete:  If unneeded or scheduled incorrectly.";
			
				$treatment_join_result = mysqli_query($con, "
					SELECT *
					FROM intersect_cat_treatment 
					JOIN treatments
					ON `intersect_cat_treatment`.`treatments_idtreatment` = `treatments`.`idtreatment`
					WHERE received = 0 AND cats_idcat = $id
					ORDER BY date ASC;"
				);
				while ($treatment_join_row = mysqli_fetch_array($treatment_join_result)) {
					$print_date = date("F j", strtotime($treatment_join_row['date']));
					$scheduled_date = date("Y-m-d", strtotime($treatment_join_row['date']));
					$treatment_name = $treatment_join_row['name'];
					$intersect_id = $treatment_join_row['idintersect'];

					if ($scheduled_date < $today) {
						$span = "<span class='missed_text'>$treatment_name</span>";
						$treatment_text = "Missed $span on $print_date.";
						$any_missed = true;
					} elseif ($scheduled_date == $today) {
						if (!$any_today) {
							$any_today = true;
							if ($any_missed) {
								echo "<div class='treatment_row'></div>";
							}
						}
						$span = "<span class='today_text'>$treatment_name</span>";
						$treatment_text = "$span scheduled for today.";
					} else {
						if (!$any_upcoming) {
							$any_upcoming = true;
							if ($any_missed or $any_today) {
								echo "<div class='treatment_row'></div>";
							}
						}
						$treatment_text = "$treatment_name scheduled for $print_date.";
					}
					echo "<div class='treatment_row'>";
						echo "<div class='profile_treatment_text row'>";
							echo $treatment_text;
						echo "</div>";
						echo "<div 	class='box_submit uncheck' 
									title='$uncheck_tooltip'
									onclick = 'check_box_recieved(this, $intersect_id)'></div>";
						echo "<div 	class='box_submit x' 
									title='$check_tooltip'
									onclick = 'check_box_recieved(this, $intersect_id)'></div>";
					echo "</div>";
				}

				echo "<div class='treatment_row'></div>";
				echo "<div class='treatment_row'></div>";

				$received_join_result = mysqli_query($con, "
					SELECT *
					FROM intersect_cat_treatment 
					JOIN treatments
					ON `intersect_cat_treatment`.`treatments_idtreatment` = `treatments`.`idtreatment`
					WHERE received = 1 AND cats_idcat = $id
					ORDER BY date DESC;"
				);
				while ($received_join_row = mysqli_fetch_array($received_join_result)) {
					$print_date = date("M j, Y", strtotime($received_join_row['date']));
					$treatment_name = $received_join_row['name'];
					$intersect_id = $received_join_row['idintersect'];

					echo "<div class='treatment_row received_text'>";
						echo "<div class='profile_treatment_text row'>";
							echo "Received $treatment_name on $print_date.";
						echo "</div>";
						echo "<div 	class='box_submit check_received' 
									title='$check_tooltip'
									onclick = 'check_box_recieved(this, $intersect_id)'></div>";
						echo "<div 	class='box_submit x_received' 
									title='$x_tooltip'
									onclick = 'check_box_recieved(this, $intersect_id)'></div>";
					echo "</div>";
				}
				?>
			</div>
		</div>
	</div>

	<div id="container_right">

		<!-- Profile picture -->
		<?php
			$bg_img = "background-image:url(./upload/$selected_photo_file);";

			echo "<div 	class = 'profile_div'
						id = 'profile_photo_div'
						style = $bg_img>";
		?>
			<div class="icon" onclick="overlay()"></div>
		</div>

		<!-- Scanned documents box -->
		<div class="profile_div" id="scanned_documents_div">
			<form method="post" action="process.php" enctype="multipart/form-data">
			<div class="profile_div_header">
				<img class="profile_paw" src="img/paw.png">
				<h2 class="div_title" id="scanned_documents_title">Scanned Documents</h2>
				<input type="submit" name="submit" value="Upload" class="profile_submit">
			</div>
			<div class="profile_div_content" id="scanned_documents_content">
				<div id="scanned_docs">
				<?php
					foreach ($document_array as $document) {
						echo "<div class = 'scan_box'>";
						echo "<img class='scanned_thumb' src='upload/$document' onclick='document_overlay_open(this)'>";
						echo "</div>";
					}
				?>
				</div>
				<input type="file" name="file" id="file" class="profile_scan_explorer">
			</div>	
			</form>
		</div>
	</div>
</div>
	<div id="overlay">
		<div id="overlay_content">
			<?php
			foreach ($photo_sql_array as $photo) {
				$photo_id = $photo['idphoto'];
				$file_name = $photo['file'];
				$class = ($photo['selected'] ? "overlay_photo selected" : "overlay_photo");
				echo "<img class='$class'
						src='/upload/$file_name'
						data-photo='$photo_id'
						data-cat='$id'
						data-file='$file_name'
						onclick='photo_select(this)'>";
			}
			?>
			<input type="button" id="overlay_close" class="button" value="Close" onClick="photo_update(), overlay()">
		</div>
	</div>
	<div id="document_overlay" onclick="document_overlay_close()">
		<img id="document_overlay_img">
	</div>
	<?php mysqli_close($con); ?>
</body>
</html>