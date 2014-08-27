<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Print Application</title>
</head>

<body>
	<?php
	if (isset($_POST['text_to_parse'])) {
		$text = $_POST['text_to_parse'];
		$text = str_replace(array(',','(', ')', '"', '{', '}'), '', $text);
		$array = explode("\n", $text);
		$is_list = false;
		$first_item = true;
		$empty_drops = array ("name", "phone", "address2", "familyNotAwareReason");
		$ignored = array ("whyWantPet", "emailConfirm", "on", "animalType");

		foreach ($array as $value) {
			$test = explode(":", $value);
			$test_0 = trim($test[0]);

			if (trim($test[0]) == "]") {
				echo "<br>";
			} elseif (in_array($test_0, $ignored)) {

			} elseif (trim($test[1]) == null) {
				if ($test_0 == "vets") {
					echo $test_0 . "TESTING<br>";
				} elseif (in_array($test_0, $empty_drops)) {
					
				} else {
					echo " " . $test_0 . " ";
				}
			} elseif (trim($test[1]) == "[") {
				echo "<div style = 'width: 160px; display: inline-block'>" . $test[0] . "</div> ";
				if ($test_0 == "vets") {
					echo "<br>";
				}
			} else {
				echo "<div style = 'width: 160px; display: inline-block; vertical-align: top;'>" . $test[0] . "</div><div style = 'width: 510px; display: inline-block'> " . $test[1] . "</div><br>";
			}
		}
	}
	?>
</body>
</html>