<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

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
			<?php
				$locations_array = array ("foster", "petsmart", "limbo", "adopted");

				foreach($locations_array as $location) {
					$loc_title = ucfirst($location);
					$status = ($location == "adopted" ? "uncheck_received" : "check");
					
					echo "<div class='location' id='$loc_title'>";
						echo "<label for='$location'>$loc_title</label>";	
						echo "<div id='$location' class='toggle ".$status."' onclick='toggle(".$location.")'></div>";
					echo "</div>";
				}
			?>
		</div>
		
		<input type="button" class="button" id="new_cat_button" value="New Cat" onClick="window.location.href='cat.php?id='">
	</div>
	<div id="container">
	<?php
		$cats_result = mysqli_query($mysqli, "SELECT * FROM cats ORDER BY name;");
		while($cat_row = mysqli_fetch_array($cats_result)) {
			$cat_name = $cat_row['name'];
			$cat_id = $cat_row['idcat'];
			$cat_location = $cat_row['location'];

			$foster_name = "";
			$fosters_result = mysqli_query($mysqli,"SELECT * FROM fosters");
			while($foster_row = mysqli_fetch_array($fosters_result)) {
				if($cat_row['fosters_idfoster'] == $foster_row['idfoster']) {
					$foster_name = $foster_row['name'];
				}
			}

			$photo_result = mysqli_query($mysqli, "SELECT * FROM photos WHERE cats_idcat = $cat_id AND selected = 1;");
			$photo_row = mysqli_fetch_array($photo_result);
			$photo_file = str_replace(" ", "%20", $photo_row['file']);

			$bg_img = ($photo_file == "" ? "background-image:url(./img/default_small.png);" : "background-image:url(./upload/$photo_file);");
			echo "<div class = 'cat_box $cat_location'>";

			$intersect_result = mysqli_query($mysqli, "
				SELECT * FROM intersect_cat_treatment 
				WHERE received = 0 AND cats_idcat = $cat_id
				ORDER BY date ASC"
			);
			if(mysqli_num_rows($intersect_result) == 0) {
				echo "<div class='list_overlay utd_gradient'>$cat_name</div>";
			} else {
				$intersect = mysqli_fetch_array($intersect_result);
				$scheduled_date = date("y m d", strtotime($intersect['date']));
				$today = date("y m d", strtotime('today'));
			
				if($scheduled_date < $today) {
					echo "<div class='list_overlay missed_gradient'>$cat_name</div>";
				} else if($scheduled_date == $today) {
					echo "<div class='list_overlay todays_gradient'>$cat_name</div>";
				} else {
					echo "<div class='list_overlay'>$cat_name</div>";
				}
			}
			echo "<div 	class = 'cat'
						onClick = 'cat_url($cat_id)'
						style = $bg_img></div>";

			echo "</div>";
		}
		mysqli_close($mysqli);
	?>
	</div>
</div>
</body>
</html>