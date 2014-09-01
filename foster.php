<?php
include 'includes/db_connect.php';

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

if($_SESSION['page'] == NULL) {
	echo "<input type='hidden' class='pass' value='NULL'>";
} else {
	echo "<input type='hidden' class='pass' value='".$_SESSION['page']."'>";
}
$_SESSION['page'] = "fp";
?>
<div id="title_left">
	<h1>Foster List</h1>
</div>
<div id="container">
	<ul id="foster_list">
	<?php
	$fosters = mysqli_query($con,"SELECT * FROM fosters");
	while($foster = mysqli_fetch_array($fosters)) {
		printf("<li>%s</li>",$foster['name']);
		$cats = mysqli_query($con,"SELECT * FROM cats");
		while($cat = mysqli_fetch_array($cats)) {
			if($foster['idfoster'] == $cat['fosters_idfoster']) {
			
			}
		}
	}
	mysqli_close($con); 
	?>
	</ul>
</div>
</body>
</html>