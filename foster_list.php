<?php
	include 'includes/db_connect.php';
	include 'includes/functions.php';

	sec_session_start();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Foster List</title>
		<?php include 'includes/header.inc'; ?>
	</head>
	<body class="_fp">
		<?php 
			include 'includes/menu.inc';

			$value = ($_SESSION['page'] == null ? "null" : $_SESSION['page']);
			echo "<input type='hidden' class='pass' value='$value'>";
			$_SESSION['page'] = "fp";
		?>
		<h1 id="page_title">Fosters</h1>
		<div id="container">
			<?php 
				if (login_check($mysqli) == "super") :
					$query = "SELECT idfoster, name FROM fosters";
					if ($result = $mysqli->query($query)) {
						$fosters = $result->fetch_all(MYSQLI_ASSOC);
					}

					foreach ($fosters as $foster) {
						echo "<div class='list_row utd_gradient'>";
						printf("<div class='list_name'>%s</div>", $foster['name']);

						if($stmt = $mysqli->prepare("SELECT name, idcat FROM cats WHERE fosters_idfoster=?")) {
							$stmt->bind_param('i', $foster['idfoster']);
							$stmt->execute();
							$stmt->bind_result($name, $idcat);
							$stmt->store_result();
							printf("<div class='description expand_div' id='ed_%s'>", 
								$foster['idfoster']);
							
							$cnt = 0;
							while ($stmt->fetch()) {
								$cnt++;
								echo "<div class='cat_name' onclick='cat_url($idcat)'>$name</div>";
								if ($cnt == 3 and $stmt->num_rows > 4) {
									$diff = $stmt->num_rows - $cnt;
									printf("<span class='more' id='m_%s'>+$diff more</span>", 
										$foster['idfoster']);
								} 
							}
							echo "</div>";
						}
						echo "</div>";
					}
					$mysqli->close();
				else : 
					echo "<span class='error'>You are not authorized to access this page.</span>";
				endif;
			?>
		</div>
	</body>
</html>