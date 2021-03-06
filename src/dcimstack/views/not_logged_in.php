<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>DCIMStack</title>
	<?php include 'libraries/css2.php'; ?>
</head>

<body>
	<div style='padding-bottom:40px;'></div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-4"></div>
			<div class="col-md-4">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title"><i class="fa fa-database"></i> DCIMStack</h5>
						<hr>
						<?php
						if (isset($login)) { // show potential errors / feedback (from login object)
							if ($login->errors) {
								foreach ($login->errors as $error) {
									echo "<div class='alert alert-warning alert-dismissible' role='alert'>";
									echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
									echo "<center>$error</center>";
									echo "</div>";
								}
							}
							if ($login->messages) {
								foreach ($login->messages as $message) {
									echo "<div class='alert alert-success alert-dismissible' role='alert'>";
									echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
									echo "<center>$message</center>";
									echo "</div>";
								}
							}
						}
						session_start();
						$_SESSION['POST_login_url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
						?>
						<form method="post" action="index.php" id="loginform">
							<input id="login_input_username" class="form-control" type="text" name="user_name" placeholder="Username" required>
							<div style='padding-bottom:5px;'></div>
							<input id="login_input_password" class="form-control" type="password" name="user_password" autocomplete="off" placeholder="Password" required>
						</form>
						<hr>
						<center><input type="submit" class="btn btn-primary" form="loginform" name="login" value="Log in"></center>
					</div>
				</div>
			</div>
			<div class="col-md-4"></div>
		</div>
	</div><!-- /.container -->


	<!-- Bootstrap core JavaScript -->
	<!-- Placed at the end of the document so the pages load faster -->
	<?php include 'libraries/js2.php'; ?>
</body>
</html>