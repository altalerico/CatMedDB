<?php
include_once 'psl-config.php';

class interval {

	public $value;
	public $unit;
	public $count;

	public function __construct($value, $unit, $count) {
		$this->value = $value;
		$this->unit = $unit;
		$this->count = $count;
	}
}

class regimen {

	public $id;
	public $intervals;

	function __construct($mysqli, $id) {
		$this->id = $id;
		$this->intervals = array ();

		if ($stmt = $mysqli->prepare("SELECT value, unit, count
			FROM treatment_interval 
			WHERE treatments_idtreatment=?
			ORDER BY priority")) 
		{
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->bind_result($value, $unit, $count);
			while ($stmt->fetch()) {
				$interval = new interval($value, $unit, $count);
				array_push($this->intervals, $interval);
			}
			$stmt->close();
		}
	}
}

class cat {

	public $id;
	public $name;
	public $dob;
	public $sex;
	public $location;
	public $foster;
	public $notes;

	public function __construct($mysqli, $id) {
		$this->id = $id;

		$stmt =  $mysqli->stmt_init();
		if ($stmt = $mysqli->prepare("SELECT name, dob, sex, location, fosters_idfoster, notes FROM cats WHERE idcat=?")) {
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->bind_result($this->name, $this->dob, $this->sex, $this->location, $this->foster, $this->notes);
			$stmt->fetch();
			$stmt->close();
		}
	}
}

class intersect {

	public $id;
	public $name;
	public $date;

	public function __construct($id, $name, $date) {
		$this->id = $id;
		$this->name = $name;
		$this->date = $date;
	}
}

class cat_treatments {

	public $intersects;

	public function __construct($mysqli, $cat, $received, $order) {
		$this->intersects = array ();

		$stmt =  $mysqli->stmt_init();
		$query = "	SELECT idintersect, name, date
					FROM intersect_cat_treatment 
					INNER JOIN treatments
					ON treatments_idtreatment=idtreatment
					WHERE deleted=0 AND cats_idcat=? AND received=?
					ORDER BY " . $order;

		if ($stmt = $mysqli->prepare($query)) {
			$stmt->bind_param("ii", $cat, $received);
			$stmt->execute();
			$stmt->bind_result($idintersect, $name, $date);
			while ($stmt->fetch()) {
				$intersect = new intersect($idintersect, $name, $date);
				array_push($this->intersects, $intersect);
			}
			$stmt->close();
		}
	}
}

class location_treatments {

	public $rows;

	public function __construct($mysqli, $location) {
		$this->rows = array ();

		$week = date("Y-m-d", strtotime('next week'));

		$stmt =  $mysqli->stmt_init();

		if ($stmt = $mysqli->prepare("SELECT idintersect, t.`name` AS t_name, idcat, 
				cats.`name` AS c_name, `date`, fosters_idfoster AS f_id
			FROM intersect_cat_treatment AS ict
			INNER JOIN treatments AS t
			ON treatments_idtreatment=idtreatment
			INNER JOIN cats
			ON cats_idcat=idcat
			WHERE location=? AND `date`<? AND deleted=0 AND received=0
			ORDER BY `date` ASC"))
		{
			$stmt->bind_param("ss", $location, $week);
			$stmt->execute();
			$stmt->bind_result($idintersect, $t_name, $idcat, $c_name, $date, $f_id);
			while ($stmt->fetch()) {
				$temp = array ($idintersect, $t_name, $idcat, $c_name, $date, $f_id);
				array_push($this->rows, $temp);
			}
			$stmt->close();
		}
	}
}

class photo {

	public $id;
	public $file;
	public $cat;
	public $selected;

	public function __construct($id, $file, $cat, $selected) {
		$this->id = $id;
		$this->file = $file;
		$this->cat = $cat;
		$this->selected = $selected;
	}
}