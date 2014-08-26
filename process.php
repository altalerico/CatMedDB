<?php
include_once 'D:/Users/Anthony/Documents/Website/includes/db_connect.php';

session_start();

$id = $_SESSION['id'];

// Profile Information
if(isset($_POST['name'])) {
	$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
	$dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);
	$age_value = filter_input(INPUT_POST, 'age_value', FILTER_SANITIZE_STRING);
	$age_units = $_POST['age_units'];
	$notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
	$foster_name = $_POST['foster'];
	$sex = $_POST['sex'];
	$location = $_POST['location'];
	
	$fosters = mysqli_query($con,"SELECT * FROM fosters");
	while($foster = mysqli_fetch_array($fosters)) {
		if($foster_name == $foster['name']) {
			$foster_id = $foster['idfoster'];
		}
	}

	if($dob == NULL) {
		if($age_value != "" and $age_units != "") {
			$dob = date('Y-m-d',strtotime(date("Y-m-d", mktime()) . " - ".$_POST['age_value'].$_POST['age_units']));
			$sql_dob = "dob='".date('Y-m-d',strtotime(date("Y-m-d", mktime()) . " - ".$_POST['age_value'].$_POST['age_units']))."'";
		} else {
			$sql_dob = "dob=NULL";
		}
	} else {
		$sql_dob = "dob='".$dob."'";
	}

	if($sex == NULL or $sex == "Sex") {
		$sql_sex = "sex=NULL";
	} else {
		$sql_sex = "sex='".$sex."'";
	}
	
	if($location == NULL) {
		$sql_loc = "location='Limbo'";
		$location = "Limbo";
	} else {
		$sql_loc = "location='".$location."'";
	}
	
	if($foster_id == NULL) {
		$sql_foster = "NULL";
	} else {
		$sql_foster = $foster_id;
	}
	
	if($notes == NULL) {
		$sql_notes = "notes=NULL";
	} else {
		$sql_notes = "notes='" . $notes . "'";
	}

	if($id == NULL) {
		$sql_start = "INSERT INTO";
		$sql_end = NULL;
		$new_cat_result = mysqli_query($con,"SELECT MAX(`idcat`) AS id FROM cats;");
		$new_cat = mysqli_fetch_array($new_cat_result);
		$_SESSION['id'] = $new_cat['id'];
	} else {
		$sql_start = "UPDATE";
		$sql_end = " WHERE idcat=" . $id;
	}

	$submitted_cat = mysqli_query($con, sprintf(" 
		%s cats SET name='%s', %s, %s, %s, fosters_idfoster=%s, %s %s;",
		$sql_start, $name, $sql_dob, $sql_sex, $sql_loc, $sql_foster, $sql_notes, $sql_end)
	);
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
			$cat_result = mysqli_query($con, sprintf("SELECT * FROM cats WHERE idcat=%u", $_SESSION['id']));
			$cat = mysqli_fetch_array($cat_result);

			$document_id_result = mysqli_query($con, "SELECT MAX(iddocument) AS max FROM documents");
			$document_id = mysqli_fetch_array($document_id_result);
			$new_id = $document_id['max'] + 1;
			$_FILES["file"]["name"] = $cat['name']."_scan".$new_id.".jpg";

			move_uploaded_file($_FILES["file"]["tmp_name"],
			"upload/" . $_FILES["file"]["name"]);
			
			$submitted_file = mysqli_query($con, sprintf("
				INSERT INTO documents
				(`file`,`cats_idcat`) 
				VALUES ('%s', %u)", 
				$_FILES["file"]["name"], $_SESSION['id'])
			);
		}
	}

}


// New Treatment
if(isset($_POST['treatment'], $_POST['date'])) {

	$treatment_result = mysqli_query($con,sprintf("
		SELECT * FROM treatments 
		WHERE `name`='%s'", $_POST['treatment'])
	);
	$new_treatment = mysqli_fetch_array($treatment_result);

	$submitted_treatment = mysqli_query($con, sprintf("
		INSERT INTO intersect_cat_treatment 
		(`cats_idcat`,`treatments_idtreatment`,`date`) 
		VALUES (%u, %u, '%s')", 
		$id, $new_treatment['idtreatment'], $_POST['date'])
	);

	if($_POST['treatment_type'] == 2) {
		$course_result = mysqli_query($con, sprintf("
			SELECT * FROM treatment_interval 
			WHERE treatments_idtreatment=%u ORDER BY priority;",
			$new_treatment['idtreatment'])
		);

		$schedule_date = $_POST['date'];

		while($treatment_interval = mysqli_fetch_array($course_result)) {
			$days_interval = $treatment_interval['days_until_treatment'];
			$repeat = $treatment_interval['repeated'];

			if($repeat == 11) {
				$schedule_date = date('Y-m-d', strtotime($schedule_date. ' + '.$days_interval.' days'));
				$submitted_treatment = mysqli_query($con, sprintf("
					INSERT INTO intersect_cat_treatment 
					(`cats_idcat`,`treatments_idtreatment`,`date`,`indefinitely`) 
					VALUES (%u, %u, '%s', '%u')", 
					$id, $new_treatment['idtreatment'], $schedule_date, $days_interval)
				);					
			} else {
				for($i = 0; $i < $repeat; $i++) {
					$schedule_date = date('Y-m-d', strtotime($schedule_date. ' + '.$days_interval.' days'));
					$submitted_treatment = mysqli_query($con, sprintf("
						INSERT INTO intersect_cat_treatment 
						(`cats_idcat`,`treatments_idtreatment`,`date`) 
						VALUES (%u, %u, '%s')", 
						$id, $new_treatment['idtreatment'], $schedule_date)
					);					
				}
			}
		}
	}
}
header("Location: ".$_SESSION['redirect'].$_SESSION['id'],true,303);
?>