<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
// include_once 'includes/classes.php';

sec_session_start();

// Profile Information
if(isset($_POST['name'])) {
	$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
	$dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);
	$age_value = filter_input(INPUT_POST, 'age_value', FILTER_SANITIZE_STRING);
	$age_units = $_POST['age_units'];
	$notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
	$sex = $_POST['sex'];
	$location = $_POST['location'];

	$dob = ($_POST['dob'] ? $dob : null);
	if ($_SESSION['id'] == null) {
		if ($stmt = $mysqli->prepare("INSERT INTO cats (name, dob, sex, location, fosters_idfoster, notes) 
			VALUES (?, ?, ?, ?, ?, ?)")) {
			$stmt->bind_param('ssssis', 
				$name, 
				$dob,
				$_POST['sex'],
				$_POST['location'],
				$_POST['foster'],
				$notes);
			$stmt->execute();
			$_SESSION['id'] = $stmt->insert_id;
			$stmt->close();
		}
	} else {
		if ($stmt = $mysqli->prepare("UPDATE cats SET name = ?, 
			dob = ?, 
			sex = ?,  
			location = ?,  
			fosters_idfoster = ?,
			notes = ? 
			WHERE idcat = ?")) {
			$stmt->bind_param('ssssisi',
				$name,
				$dob,
				$_POST['sex'],
				$_POST['location'], 
				$_POST['foster'],
				$notes,
				$_SESSION['id']);
			$stmt->execute(); 
			$stmt->close();		
		}
	}
}

// Scanned Document
if(isset($_FILES['file'])) {
	$allowedExts = array("gif", "jpeg", "jpg", "png");
	$temp = explode(".", $_FILES["file"]["name"]);
	$extension = end($temp);
	if ((($_FILES["file"]["type"] == "image/gif")
	|| ($_FILES["file"]["type"] == "image/jpeg")
	|| ($_FILES["file"]["type"] == "image/jpg")
	|| ($_FILES["file"]["type"] == "image/pjpeg")
	|| ($_FILES["file"]["type"] == "image/x-png")
	|| ($_FILES["file"]["type"] == "image/png"))
	&& in_array($extension, $allowedExts)) {
		if ($_FILES["file"]["error"] > 0) {
			echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
		} else {
			$query = "SELECT MAX(iddocument) AS id FROM documents";
			if ($result = $mysqli->query($query)) {
				$prev_doc = $result->fetch_assoc();
				$result->close();
			}
			$new_id = $prev_doc['id'] + 1;
			$_FILES["file"]["name"] = $_POST['name']."_scan".$new_id.".jpg";
			move_uploaded_file($_FILES["file"]["tmp_name"],
			"upload/" . $_FILES["file"]["name"]);
			
			if ($stmt = $mysqli->prepare("INSERT INTO documents (file, cats_idcat) VALUES (?, ?)")) {
				$stmt->bind_param('si', $_FILES["file"]["name"], $_SESSION['id']);
				$stmt->execute();
				$stmt->close();
			}
		}
	}
}

// New Treatment
if(isset($_POST['treatment'])) {
	if($_POST['treatment_type'] == 1) {
		if ($stmt = $mysqli->prepare("INSERT INTO intersect_cat_treatment 
			(cats_idcat, treatments_idtreatment, date) 
			VALUES (?, ?, ?)")) 
		{
			$stmt->bind_param('iis', 
				$_SESSION['id'], 
				$_POST['treatment'],
				$_POST['date']);
			$stmt->execute();
			$stmt->close();
		}	
	} elseif($_POST['treatment_type'] == 2) {
		if ($stmt1 = $mysqli->prepare("SELECT value, unit, count 
			FROM treatment_interval 
			WHERE treatments_idtreatment=? 
			ORDER BY priority")) 
		{
			$stmt1->bind_param('i', $_POST['treatment']);
			$stmt1->execute();
			$stmt1->store_result();
			$stmt1->bind_result($value, $unit, $count);
			$schedule_date = "";
			$indefinitely = 1;
			while ($stmt1->fetch()) {
				if ($count == 11) {
					$schedule_date = ($schedule_date == "" ? $_POST['date'] :
						date('Y-m-d', strtotime("$schedule_date + $value $unit")));
					if ($stmt2 = $mysqli->prepare("INSERT INTO intersect_cat_treatment 
		 					(cats_idcat, treatments_idtreatment, date, indefinitely) 
		 					VALUES (?, ?, ?, ?)"))
					{
						$stmt2->bind_param('iisi',
							$_SESSION['id'],
							$_POST['treatment'],
							$schedule_date,
							$indefinitely);
						$stmt2->execute();
						$stmt2->close();
					}
				} else {
					for ($i = 0; $i < $count; $i++) {
						$schedule_date = ($schedule_date == "" ? $_POST['date'] :
							date('Y-m-d', strtotime("$schedule_date + $value $unit")));
						if ($stmt3 = $mysqli->prepare("INSERT INTO intersect_cat_treatment 
		 					(cats_idcat, treatments_idtreatment, date) 
		 					VALUES (?, ?, ?)")) 
						{
							$stmt3->bind_param('iis', 
								$_SESSION['id'],
								$_POST['treatment'],
								$schedule_date);
							$stmt3->execute();
							$stmt3->close();
						}
					}
				}
			}
			$stmt1->close();
		}
	}
}

// if(isset($_POST['regimen'])) {  // treatment.php submit
// 	$regimen = filter_input(INPUT_POST, 'regimen', FILTER_SANITIZE_STRING);
// 	$treatment_result = mysqli_query($con, sprintf("SELECT * FROM treatments WHERE name='%s'", $_POST['treatment']));
// 	$sql_treatment = mysqli_fetch_array($treatment_result);
// 	$id = $sql_treatment['idtreatment'];

// 	for($i = 0; $i < $interval_cnt; $i++) {
// 		$priority = $i + 1;
// 		if($_POST['value'][$i] != NULL) {
// 			$days = calc_days($_POST['value'][$i], $_POST['unit'][$i]);
			
// 			$interval = new interval($id, $priority, $days, $_POST['repeat'][$i]);
// 			$regimen->add_interval($interval);
// 		}
// 	}
// }
header("Location: ".$_SESSION['redirect'].$_SESSION['id'],true,303);
?>