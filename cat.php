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
			$value = ($_SESSION['page'] == null ? "null" : $_SESSION['page']);
			echo "<input type='hidden' class='pass' value='$value'>";
			$_SESSION['page'] = "cp";

			// SQL queries of the database to get relevant cat info and set respective variables.

			// Class objects 
			$cat = new cat($id, $mysqli);
			$upcoming = new cat_treatments($_SESSION['id'], 0, "date ASC", $mysqli);
			$received = new cat_treatments($_SESSION['id'], 1, "date DESC", $mysqli);

			// Foster names for select.
			$query = "SELECT idfoster, name FROM fosters";
			if ($result = $mysqli->query($query)) {
				$fosters = $result->fetch_all(MYSQLI_ASSOC);
				$result->close();
			}

			$photos = array ();
			if ($stmt = $mysqli->prepare("SELECT idphoto, file, cats_idcat, selected 
				FROM photos 
				WHERE cats_idcat=?")) {

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
				$stmt->bind_result($file);

				while ($row = $stmt->fetch()) {
					array_push($documents, $file);
				}
				$stmt->close();
			}

			echo login_check($mysqli);
			switch (login_check($mysqli)) {
				case 'super':
					$disable = "";
					$style = "visibility: visible";
					$btn_style = "display: inline-block";
					$div_stype = "display: block";
					break;
				case 'foster':
					$disable = "disabled";
					$style = "visibility: visible";
					$btn_style = "display: none";
					$div_style = "display: none";
					break;
				default:
					$disable = "disabled";
					$style = "visibility: hidden";
					$btn_style = "display: none";
					$div_style = "display: none";
					break;
			}

			$mysqli->close();

			echo "<h1 id='profile_title'>$cat->name's Profile</h1>"; 
		?>
		<div id="profile_wrapper">
			<div id="container_left">
				<!-- Information box -->
				<div class="card">
					<form method="post" action="includes/process.php">
						<div class="header">
							<img class="paw" src="img/paw.png">
							<h2 class="div_title" id="info_title">Information</h2>
							<input type="submit" 
								class="profile_submit" 
								value="Update"
								style="<?php echo $btn_style ?>">
						</div>
						<div class="content">
							<label for="name" class="card">Name</label>
							<input type="text" 
								id="cat_name" 
								name="name"
								value="<?php echo $cat->name?>"<?php echo $disable ?>><br>

							<label for="sex" class="card">Sex</label>
							<select name="sex" id="sex" <?php echo $disable ?>>
							<?php
								$sexes = array ("", "Female","Spayed Female","Male","Neutered Male");
								foreach ($sexes as $s) {
									$selected = ($s == $cat->sex ? "selected='selected'" : "");
									echo "<option $selected>$s</option>";
								}
							?>
							</select><br>
							
							<label for="location" class="card">Location</label>
							<?php
								echo "<select name='location' 
									id='location' 
									onchange='fosterToggle()' $disable>";
								$locations = array ("", "Adopted", "Foster", "PetSmart");
								foreach ($locations as $location) {
									$selected = ($location == $cat->location ? "selected='selected'" : "");
									echo "<option $selected>$location</option>";
								}
								echo "</select>";
							
								$hidden = ($cat->location == "Foster" ? "" : "hidden");
								echo "<select name='foster' id='foster_toggle' class='$hidden' $disable>";
								foreach ($fosters as $foster) {
									$selected = ($foster['idfoster'] == $cat->foster ? "selected='selected'" : "");
									echo "<option 
										value='" . $foster['idfoster'] . "' $selected>" . 
										$foster['name'] . "</option>";
								}
								echo "</select>";
							
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
							<label for="age_value" class="card">Age/dob</label>
							<input type="text" 
								id="age" 
								name="age_value" 
								value="<?php echo $age_value; ?>" <?php echo $disable ?>>
							<select name="age_units" id="age" <?php echo $disable ?>>
							<?php
								$units = array ("","Weeks","Months","Years");
								foreach($units as $unit) {
									$selected = ($unit == $age_unit ? "selected='selected'" : "");
									echo "<option $selected>$unit</option>";
								}
							?>
							</select>
							<input type="text" 
								id="dob" 
								name="dob" 
								placeholder="yyyy-mm-dd" 
								value="<?php echo $cat->dob; ?>" <?php echo $disable ?>>
							<label for="notes" class="card top">Notes</label>
							<textarea 
								name="notes" 
								id="notes" 
								rows="3" 
								cols="32" <?php echo $disable ?>><?php echo $cat->notes; ?>
							</textarea>
						</div>
					</form>
				</div>

				<!-- New treatment box -->
				<div class="card" style="<?php echo $div_style ?>">
					<form method="post" action="includes/process.php" class="profile_form">
						<div class="header">
							<img class="paw" src="img/paw.png">
							<h2 class="div_title" id="new_treatment_title">New Treatment</h2>
							<input type="submit" 
								class="profile_submit" 
								value="Schedule">
						</div>
						<div class="content" id="new_treatment_content">
							<label for="treatment" class="card">Treatment</label>
							<select name="treatment" id="treatment">
								<option></option>
								<?php
								foreach ($treatments as $treatment) {
									printf("<option value='%u'>%s</option>", 
										$treatment['idtreatment'], $treatment['name']);
								}
								?>
							</select><br>

							<label for="treatment_type" class="card">Type</label>
							<select name="treatment_type" id="treatment_type">
								<option></option>
								<option value="1">Single Treatment</option>
								<option value="2">Course of Treatment</option>
							</select><br>

							<label for="datepicker" class="card">Date</label>
							<input type="text" name="date" id="datepicker" />
						</div>
					</form>
				</div> 

				<!-- Treatments box. -->
				<div class="card" style="<?php echo $style ?>">
					<div class="header">
						<img class="paw" src="img/paw.png">
						<h2 class="div_title" id="scheduled_treatments_title">Treatments</h2>
						<input type="button" 
							class="button" 
							id="print_cat_btn" 
							value="Print"
							style="<?php echo $btn_style ?>"
							title="Print <?php echo $cat->name ?>'s medical history."
							onClick="window.open('print_medical.php?id=<?php echo $id ?>')">
					</div>
					<div class="content">
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
												style='$btn_style'
												onclick = 'check_box(this, $ui->id)'></div>";
									echo "<div 	class='box_submit x drop' 
												title='$x_tooltip'
												style='$btn_style'
												id='$ui->id'></div>";
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
												style='$btn_style'
												onclick='check_box(this, $ri->id)'></div>";
									echo "<div 	class='box_submit x_received drop' 
												title='$x_tooltip'
												style='$btn_style'
												id='$ri->id'></div>";
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

					echo "<div 	class = 'card'
								id = 'profile_photo'
								style = $bg_img>";
					echo "<div class='icon' onclick='overlay()' style='$style'></div>";
					echo "</div>";
				?>
				<!-- Documents box -->
				<div class="card" id="scanned_documents_div" style="<?php echo $style ?>">
					<form method="post" action="includes/process.php" enctype="multipart/form-data">
						<div class="header">
							<img class="paw" src="img/paw.png">
							<h2 class="div_title" id="scanned_documents_title">Documents</h2>
							<input type="submit" 
								name="submit" 
								value="Upload"
								style="<?php echo $btn_style ?>"
								class="profile_submit">
						</div>
						<div class="content" id="scanned_documents_content">
							<div id="scanned_docs">
								<?php
									foreach ($documents as $d) {
										echo "<div class = 'scan_box'>";
										echo "<img class='scanned_thumb' '
											src='upload/$d' 
											onclick='document_overlay_open(this)'>";
										echo "</div>";
									}
								?>
							</div>
							<input type="hidden" 
								name="name" 
								id="hiddenField" 
								value="<?php echo $cat->name ?>">
							<input type="file" 
								name="file" 
								id="file"
								style="<?php echo $btn_style ?>"
								class="profile_scan_explorer">
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