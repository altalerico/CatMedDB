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
		$stmt = $mysqli->prepare("INSERT INTO cats (name, dob, sex, location, fosters_idfoster, notes) 
			VALUES (?, ?, ?, ?, ?, ?)");
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
	} else {
		$stmt = $mysqli->prepare("UPDATE cats SET name = ?, 
			dob = ?, 
			sex = ?,  
			location = ?,  
			fosters_idfoster = ?,
			notes = ? 
			WHERE idcat = ?");
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
			// $cat_result = mysqli_query($mysqli, sprintf("SELECT * FROM cats WHERE idcat=%u", $_SESSION['id']));
			// $cat = mysqli_fetch_array($cat_result);

			// $document_id_result = mysqli_query($mysqli, "SELECT MAX(iddocument) AS max FROM documents");
			// $document_id = mysqli_fetch_array($document_id_result);
			// $new_id = $document_id['max'] + 1;
			// $_FILES["file"]["name"] = $cat['name']."_scan".$new_id.".jpg";

			move_uploaded_file($_FILES["file"]["tmp_name"],
			"upload/" . $_FILES["file"]["name"]);
			
			// $submitted_file = mysqli_query($mysqli, sprintf("
			// 	INSERT INTO documents
			// 	(`file`,`cats_idcat`) 
			// 	VALUES ('%s', %u)", 
			// 	$_FILES["file"]["name"], $_SESSION['id'])
			// );
		}
	}

}


// New Treatment
if(isset($_POST['treatment'], $_POST['date'])) {

	$stmt = $mysqli->prepare("INSERT INTO intersect_cat_treatment (cats_idcat, treatments_idtreatment, date) 
		VALUES (?, ?, ?)");
	$stmt->bind_param('iis', 
	$_SESSION['id'], 
	$_POST['treatment'],
	$_POST['date']);
	$stmt->execute();
	$stmt->close();

	// if($_POST['treatment_type'] == 2) {
	// 	$course_result = mysqli_query($mysqli, sprintf("
	// 		SELECT * FROM treatment_interval 
	// 		WHERE treatments_idtreatment=%u ORDER BY priority;",
	// 		$new_treatment['idtreatment'])
	// 	);

		// $schedule_date = $_POST['date'];

		// while($treatment_interval = mysqli_fetch_array($course_result)) {
		// 	$days_interval = $treatment_interval['days_until_treatment'];
		// 	$repeat = $treatment_interval['repeated'];

		// 	if($repeat == 11) {
		// 		$schedule_date = date('Y-m-d', strtotime($schedule_date. ' + '.$days_interval.' days'));
		// 		$submitted_treatment = mysqli_query($mysqli, sprintf("
		// 			INSERT INTO intersect_cat_treatment 
		// 			(`cats_idcat`,`treatments_idtreatment`,`date`,`indefinitely`) 
		// 			VALUES (%u, %u, '%s', '%u')", 
		// 			$id, $new_treatment['idtreatment'], $schedule_date, $days_interval)
		// 		);					
		// 	} else {
		// 		for($i = 0; $i < $repeat; $i++) {
		// 			$schedule_date = date('Y-m-d', strtotime($schedule_date. ' + '.$days_interval.' days'));
		// 			$submitted_treatment = mysqli_query($mysqli, sprintf("
		// 				INSERT INTO intersect_cat_treatment 
		// 				(`cats_idcat`,`treatments_idtreatment`,`date`) 
		// 				VALUES (%u, %u, '%s')", 
		// 				$id, $new_treatment['idtreatment'], $schedule_date)
		// 			);					
		// 		}
		// 	}
		// }
	// }
}

header("Location: ".$_SESSION['redirect'].$_SESSION['id'],true,303);
?>