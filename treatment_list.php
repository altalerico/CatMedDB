<?php
	include_once 'includes/db_connect.php';
	include_once 'includes/functions.php';

	sec_session_start();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Treatment List</title>
		<?php include 'includes/header.inc'; ?>
	</head>
	<body class="_tp">
		<?php 
			include 'includes/menu.inc';

			$value = ($_SESSION['page'] == null ? "null" : $_SESSION['page']);
			echo "<input type='hidden' class='pass' value='$value'>";
			$_SESSION['page'] = "tp";
		?>
		<h1 id="page_title">Treatments</h1>
		<div id="container">
			<?php 
				if (login_check($mysqli)) :
					$query = "SELECT idtreatment, name FROM treatments";
					if ($result = $mysqli->query($query)) {
						while ($row = $result->fetch_assoc()) {
							echo "<div class='list_row'>";
							if ($stmt1 = $mysqli->prepare("SELECT idinterval 
								FROM treatment_interval 
								WHERE treatments_idtreatment=?")) {

								$stmt1->bind_param('i', $row['idtreatment']);
								$stmt1->execute();
								$stmt1->store_result();
								$add_class = ($stmt1->num_rows == 0 ? "missed_text" : "");

								if ($stmt2 = $mysqli->prepare("SELECT value, unit, count 
									FROM treatment_interval 
									WHERE treatments_idtreatment=? 
									ORDER BY priority")) {

									$stmt2->bind_param('i', $row['idtreatment']);
									$stmt2->execute();
									$stmt2->store_result();
									$stmt2->bind_result($value, $unit, $count);
									$text = "";

									while ($stmt2->fetch()) {
										$interval_text = ($value == 1 ? "Once a $unit " : 
											"Every $value $unit" . "s ");
										$product = ($text == "" ? $value * ($count - 1) : 
											$value * $count);
										if ($unit == "day") {$product++;}
										$duration = ($count == 11 ? "indefinitely. 
											(<span style='color: #60abf8; font-size: 17px;'>&#8734</span>)" : 
											"for $product $unit" . "s. 
											(<span style='color: #60abf8; font-size: 17px;'>$count</span>)");
										$text = ($text == "" ? "$interval_text $duration" : 
											"$text<br>Then " . lcfirst ($interval_text) . $duration);
									}
									$stmt2->close();
								}
								$stmt1->close();
							}
							if (login_check($mysqli) == 'super') {
								printf("<div class='list_name %s' 
									onclick='treatment_url(%s)'>%s</div>", 
									$add_class, $row['idtreatment'], $row['name']);
							} else {
								printf("<div class='list_name %s'>%s</div>", 
									$add_class, $row['name']);
							}

							echo "<div class='description'>$text</div>";
							echo "</div>";
						}
						$result->close();
					}
					$mysqli->close(); 
				else :
					echo "<span class='error'>You are not authorized to access this page.</span>";
				endif; 
			?>
		</div>
	</body>
</html>