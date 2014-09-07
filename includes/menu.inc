<div class="menu_container">
	<div id="login">
		<?php if (login_check($mysqli)) : ?>
            <p>Welcome <?php echo htmlentities($_SESSION['username']); ?>!
			<a href="includes/logout.php"> Logout</a></p>
		<?php else : ?>
			<form action="includes/process_login.php" method="post" name="login_form">
				<label for="email" class="login">Email</label>
				<input type="text" name="email">
				<label for="password" class="login">Password</label>
				<input type="password" name="password">
				<input type="button"
					id="login"
					value="Login" 
					onclick="formhash(this.form, this.form.password);" />
				<a href="register.php" id="register">Register |</a>
			</form>
		<?php endif; ?>	
	</div>
	<nav class="menu_bar">
		<img id="menu_paw" src="img/paw.png">
		<a href="dashboard.php" id="dashboard_btn">Dashboard</a>
		<a href="cat_list.php" id="cat_btn">Cats</a>
		<a href="foster_list.php" id="foster_btn">Fosters</a>
		<a href="treatment_list.php" id="treatment_btn">Treatments</a>
		<div class="nav_line"></div>
	</div>
</div>