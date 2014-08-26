<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Classes</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="js/myscripts.js"></script>  
</head>

<body>
<?php

include 'D:/Users/Anthony/Documents/mysqlconnect.php';

class interval {

	public $id;
	public $priority;
	public $days;
	public $repeat;

	public function __construct($id, $priority, $days, $repeat) {
		$this->id = $id;
		$this->priority = $priority;
		$this->days = $days;
		$this->repeat = $repeat;

		$interval_result = mysqli_query($con, sprintf("
			SELECT * FROM treatment_interval 
			WHERE treatments_idtreatment=%u AND priority=%u;", 
			$id, $priority)
		);
		$sql_interval = mysqli_fetch_array($interval_result);

		if($sql_interval != NULL) {
			$interval_update = mysqli_query($con, sprintf("
				UPDATE treatment_interval
				SET days_until_treatment=%u, repeated=%u WHERE treatments_idtreatment=%u AND priority=%u;",
				$days, $repeat, $id, $priority)
			);
		} else {  // Create a new interval.
			$interval_insert = mysqli_query($con, sprintf("
				INSERT INTO treatment_interval 
				SET treatments_idtreatment=%u, priority=%u, days_until_treatment=%u, repeated=%u;",
				$id, $priority, $days, $repeat)
			);
		}
	}
}
mysqli_close($con); 

class regimen {

	public $test;
	public $intervals = array();

	function add_interval($interval) {
		$this->test = $interval->days;
		array_push($this->intervals, $interval);
	}
}
?>
</body>
</html>