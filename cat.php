<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/classes.php';

sec_session_start();
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Cat Profile</title>
	<?php include_once 'includes/header.inc'; ?>
</head>

<body class="_cp">
<?php
	include_once 'includes/menu.inc';

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
	$cat = new cat($mysqli, $id);

	// Foster names for select.
	$query = "SELECT idfoster, name FROM fosters";
	if ($result = $mysqli->query($query)) {
		$fosters = $result->fetch_all(MYSQLI_ASSOC);
		$result->close();
	}

	$photos = array ();
	if ($stmt = $mysqli->prepare("SELECT idphoto, file, cats_idcat, selected FROM photos WHERE cats_idcat=?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($idphoto, $file, $cats_idcat, $selected);
		if ($stmt->num_rows == 0) {
			$selected_photo_file = "default.jpg";
		} else {
			while ($stmt->fetch()) {
				if ($selected) {
					$selected_photo_file = str_replace(" ", "%20", $file);
				}
				$photo = new photo($idphoto, $file, $cats_idcat, $selected);
				array_push($photos, $photo);
			}
		}
		$stmt->close();
		echo $selected_photo_file;
	}

	// Treatment names for select.
	$query = "SELECT idtreatment, name FROM treatments";
	if ($result = $mysqli->query($query)) {
		$treatments = $result->fetch_all(MYSQLI_ASSOC);
		$result->close();
	}

	$documents = array ();
	if ($stmt = $mysqli->prepare("SELECT file FROM documents WHERE cats_idcat=?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($file);
		while ($row = $stmt->fetch()) {
			array_push($documents, $file);
		}
		$stmt->close();
	}

	// Treatments
	$upcoming = new cat_treatments($mysqli, $_SESSION['id'], 0, "date ASC");
	$received = new cat_treatments($mysqli, $_SESSION['id'], 1, "date DESC");

	$mysqli->close();
?>	

<!-- Cat's profile -->
<?php echo "<h1 id='page_title'>$cat->name's Profile</h1>"; ?>
<div id="profile_wrapper">

	<div id="container_left">

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
				<input type="text" id="name_input" name="name" value="<?php echo $cat->name; ?>"><br>

				<label for="sex" class="profile_label">Sex</label>
				<select name="sex" id="sex">
				<?php
					$sexes = array ("", "Female","Spayed Female","Male","Neutered Male");
					foreach ($sexes as $s) {
						$selected = ($s == $cat->sex ? "selected='selected'" : "");
						echo "<option $selected>$s</option>";
					}
				?>
				</select><br>
				
				<label for="location" class="profile_label">Location</label>
				<select name="location" id="location" onchange="fosterToggle()">
				<?php
					$locations = array ("", "Adopted", "Foster", "PetSmart");
					foreach ($locations as $location) {
						$selected = ($location == $cat->location ? "selected='selected'" : "");
						echo "<option $selected>$location</option>";
					}
				?>
				</select>
				
				<?php
				$hidden = ($cat->location == "Foster" ? "" : "hidden");
				echo "<select name='foster' id='foster_toggle' class='$hidden'>";

				foreach ($fosters as $foster) {
					$selected = ($foster['idfoster'] == $cat->foster ? "selected='selected'" : "");
					echo "<option 
						value='" . $foster['idfoster'] . "' $selected>" . 
						$foster['name'] . "</option>";
				}
				?>
				</select>
				
				<?php
				if (isset($cat->dob)) {
					$birth = date_create($cat->dob);
					$now = date_create("now");
					$interval = date_diff($birth, $now);
				
					if ($interval->format('%y') >= 1) {
						$age_value = $interval->format('%y');
						$age_unit = "Years";
					} elseif ($interval->format('%m') >= 1) {
						$age_value = $interval->format('%m');
						$age_unit = "Months";
					} else {
						$days = $interval->format('%d');
						$age_value = (int) ($days / 7);
						$age_unit = "Weeks";
					}
				}
				?>
				<label for="age_value" class="profile_label">Age/dob</label>
				<input type="text" id="age_input" name="age_value" value="<?php echo $age_value; ?>">
				<select name="age_units" id="age_select">
				<?php
					$units = array ("","Weeks","Months","Years");
					foreach($units as $unit) {
						$selected = ($unit == $age_unit ? "selected='selected'" : "");
						echo "<option $selected>$unit</option>";
					}
					?>
				</select>
				<input type="text" id="dob" name="dob" placeholder="yyyy-mm-dd" value="<?php echo $cat->dob; ?>"><br>
				
				<label for="notes" class="profile_label">Notes</label>
				<textarea name="notes" id="notes" rows="3" cols="32"><?php echo $cat->notes ?></textarea>
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
					foreach ($treatments as $treatment) {
						echo "<option 
							value='" . $treatment['idtreatment'] . "'>" . 
							$treatment['name'] . "</option>";
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

		<!-- Treatments box. -->
		<div class="profile_div">
			<div class="profile_div_header">
				<img class="profile_paw" src="img/paw.png">
				<h2 class="div_title" id="scheduled_treatments_title">Treatments</h2>
				<input type="button" 
					class="button" 
					id="print_cat_btn" 
					value="Print" 
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
			
				foreach ($upcoming->intersects as $ui) {
					$print_date = date("M j", strtotime($ui->date));
					$scheduled_date = date("Y-m-d", strtotime($ui->date));

					if ($scheduled_date < $today) {
						$span = "<span class='missed_text'>$ui->name</span>";
						$treatment_text = "Missed $span on $print_date.";
						$any_missed = true;
					} elseif ($scheduled_date == $today) {
						if (!$any_today) {
							$any_today = true;
							if ($any_missed) {
								echo "<div class='row'></div>";
							}
						}
						$span = "<span class='today_text'>$ui->name</span>";
						$treatment_text = "$span scheduled for today.";
					} else {
						if (!$any_upcoming) {
							$any_upcoming = true;
							if ($any_missed or $any_today) {
								echo "<div class='row'></div>";
							}
						}
						$treatment_text = "$ui->name scheduled for $print_date.";
					}
					echo "<div class='row'>";
						echo "<div class='profile_treatment_text row_element'>";
							echo $treatment_text;
						echo "</div>";
						echo "<div 	class='box_submit uncheck' 
									title='$uncheck_tooltip'
									onclick = 'check_box_recieved(this, $ui->id)'></div>";
						echo "<div 	class='box_submit x' 
									title='$check_tooltip'
									onclick = 'check_box_recieved(this, $ui->id)'></div>";
					echo "</div>";
				}

				echo "<div class='row'></div>";
				echo "<div class='row'></div>";

				foreach ($received->intersects as $ri) {
					$print_date = date("M j, Y", strtotime($ri->date));

					echo "<div class='row received_text'>";
						echo "<div class='profile_treatment_text row_element'>";
							echo "$ri->name on $print_date.";
						echo "</div>";
						echo "<div 	class='box_submit check_received' 
									title='$check_tooltip'
									onclick = 'check_box_recieved(this, $ri->id)'></div>";
						echo "<div 	class='box_submit x_received' 
									title='$x_tooltip'
									onclick = 'check_box_recieved(this, $ri->id)'></div>";
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

		<!-- Documents box -->
		<div class="profile_div" id="scanned_documents_div">
			<form method="post" action="process.php" enctype="multipart/form-data">
			<div class="profile_div_header">
				<img class="profile_paw" src="img/paw.png">
				<h2 class="div_title" id="scanned_documents_title">Documents</h2>
				<input type="submit" name="submit" value="Upload" class="profile_submit">
			</div>
			<div class="profile_div_content" id="scanned_documents_content">
				<div id="scanned_docs">
				<?php
					foreach ($documents as $d) {
						echo "<div class = 'scan_box'>";
						echo "<img class='scanned_thumb' src='upload/$d' onclick='document_overlay_open(this)'>";
						echo "</div>";
					}
				?>
				</div>
				<input type="hidden" name="name" id="hiddenField" value="<?php echo $cat->name ?>">
				<input type="file" name="file" id="file" class="profile_scan_explorer">
			</div>	
			</form>
		</div>
	</div>
</div>
	<div id="overlay">
		<div id="overlay_content">
			<?php
			foreach ($photos as $p) {
				$class = ($p->selected ? "overlay_photo selected" : "overlay_photo");
				echo "<img class='$class'
						src='/upload/$p->file'
						data-photo='$p->id'
						data-cat='$id'
						data-file='$p->file'
						onclick='photo_select(this)'>";
			}
			?>
			<input type="button" id="overlay_close" class="button" value="Close" onClick="photo_update(), overlay()">
		</div>
	</div>
	<div id="document_overlay" onclick="document_overlay_close()">
		<img id="document_overlay_img">
	</div>
</body>
</html>