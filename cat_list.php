<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/classes.php';

sec_session_start();
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Cat_List</title>
	<?php include_once 'includes/header.inc'; ?>
</head>

<body class="_cp">
<?php 
include_once 'includes/menu.inc';

// Session variables
if($_SESSION['page'] == NULL) {
	echo "<input type='hidden' class='pass' value='NULL'>";
} else {
	echo "<input type='hidden' class='pass' value='".$_SESSION['page']."'>";
}
$_SESSION['page']="cp";

?>
<div id = "wrapper">
	<div id = "nav_left">
		<div class = "location_box">
			<img class="paw" src="img/paw.png">
			<h2 class="div_title">Locations</h2>
			<?php
				$locations_array = array ("foster", "petsmart", "limbo", "adopted");

				foreach($locations_array as $location) {
					$loc_title = ucfirst($location);
					$status = ($location == "adopted" ? "uncheck_received" : "check");
					
					echo "<div class='location' id='$loc_title'>";
						echo "<label for='$location'>$loc_title</label>";	
						echo "<div id='$location' class='toggle $status' onclick='toggle($location)'></div>";
					echo "</div>";
				}
			?>
		</div>
		<input type="button" class="button" id="new_cat_btn" value="New Cat" onClick="window.location.href='cat.php?id='">
	</div>
	<div id="cat_container">
	<?php
		$query = "SELECT idcat FROM cats ORDER BY name;";
		if ($result = $mysqli->query($query)) {
			while ($row = $result->fetch_assoc()) {
				$cat = new cat($mysqli, $row['idcat']);

				if ($stmt = $mysqli->prepare("SELECT file 
					FROM photos 
					WHERE cats_idcat=? AND selected=1")) 
				{
					$stmt->bind_param('i', $cat->id);
					$stmt->execute();
					$stmt->bind_result($photo_file);
					$stmt->fetch();
					$photo_file = str_replace(" ", "%20", $photo_file);
					$stmt->close();
				}

				$bg_img = ($photo_file == "" ? "background-image:url(./img/default_small.png);" : "background-image:url(./upload/$photo_file);");
				echo "<div class = 'cat_box $cat->location' onClick = 'cat_url($cat->id)'>";

				if ($stmt = $mysqli->prepare("SELECT date FROM intersect_cat_treatment 
					WHERE cats_idcat=? AND received=0 AND deleted=0
					ORDER BY date ASC
					LIMIT 1")) 
				{
					$stmt->bind_param('i', $cat->id);
					$stmt->execute();
					$stmt->bind_result($date);
					$stmt->fetch();
					$stmt->close();
				}
				$date = date("y m d", strtotime($date));
				$today = date("y m d", strtotime('today'));
				
				if($date < $today) {
					echo "<div class='list_overlay missed_gradient'>$cat->name</div>";
				} else if($date == $today) {
					echo "<div class='list_overlay todays_gradient'>$cat->name</div>";
				} else {
					echo "<div class='list_overlay'>$cat->name</div>";
				}

				echo "<div 	class = 'cat'
					style = $bg_img></div>";
				echo "</div>";
			}
			$result->close();
		}
		$mysqli->close();
	?>
	</div>
</div>
</body>
</html>