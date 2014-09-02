<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

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
if(isset($_POST['treatment'], $_POST['date'])) {

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

	if($_POST['treatment_type'] == 2) {
		if ($stmt = $mysqli->prepare("SELECT days_until_treatment, repeated 
			FROM treatment_interval 
			WHERE treatments_idtreatment=? 
			ORDER BY priority")) 
		{
			$stmt->bind_param('i', $_POST['treatment']);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($days, $repeated);

			$schedule_date = $_POST['date'];
			while ($stmt->fetch()) {
				$i = ($repeated == 11 ? 10 : 0);
				while ($i<$repeated) {
					$schedule_date = date('Y-m-d', strtotime($schedule_date . '+' . $days . ' days'));
					if ($stmt = $mysqli->prepare("INSERT INTO intersect_cat_treatment 
	 					(cats_idcat, treatments_idtreatment, date) 
	 					VALUES (?, ?, ?)")) 
					{
						$stmt->bind_param('iis', 
							$_SESSION['id'],
							$_POST['treatment'],
							$schedule_date);
						$stmt->execute();
						$stmt->close();
					}
					$i++;
				}
			}
			$stmt->close();
		}
	}
}

header("Location: ".$_SESSION['redirect'].$_SESSION['id'],true,303);
?>