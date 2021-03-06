<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>DCIMStack</title>
	<?php
	include_once 'libraries/css2.php';
	include_once 'libraries/general.php';
	?>
</head>

<body>

	<?php include_once 'libraries/header2.php'; ?>

	<div class="container-fluid">
		<h1 class="page-header">Disks
			<div class='pull-right'>
				<button type="button" class='btn btn-primary' data-toggle="modal" data-target="#add_hdd"><img src='assets/img/add.png'> Add</button>
				<a class='btn btn-primary' href="hdd_stats.php"><img src='assets/img/chart_bar.png'> Stats</a>


					<div class="btn-group">
						<button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src='assets/img/find.png'>
     		Disk Filter
   </button>
  		<div class="dropdown-menu">
    	<a class="dropdown-item" href="disks.php?filter=inuse&var=HDD"> <img src='assets/img/drive_go.png'>  List HDD in-use</a>
    	<a class="dropdown-item" href="disks.php?filter=inuse&var=SSD"> <img src='assets/img/drive_go.png'>  List SSD in-use</a>
    	<a class="dropdown-item" href="disks.php?filter=inuse&var=SAS"> <img src='assets/img/drive_go.png'>  List SAS in-use</a>
    	<a class="dropdown-item" href="disks.php?filter=all&var=notuse"> <img src='assets/img/drive_go.png'>  List free disks</a>
			<a class="dropdown-item" href="disks.php?filter=all&var=failed"> <img src='assets/img/drive_error.png'>  List Failed drives</a>
		</div>
	</div>
				<a class='btn btn-primary' href="disks.php"><img src='assets/img/chart_bar.png'> Clear filter</a>
			</div>
		</h1>
		<hr>





		<?php include 'libraries/alerts.php'; ?>
		<?php
		include_once 'config/db.php';
		if(isset($_GET['filter']) && isset($_GET['var'])) {
			if($_GET['filter']=="inuse" && $_GET['var']=="HDD") {
				$sql = "SELECT * FROM `devices` WHERE `device_type`='HDD' AND `device_inuse`=1";
			}
			if($_GET['filter']=="inuse" && $_GET['var']=="SSD") {
				$sql = "SELECT * FROM `devices` WHERE `device_type`='SSD' AND `device_inuse`=1";
			}
			if($_GET['filter']=="inuse" && $_GET['var']=="SAS") {
				$sql = "SELECT * FROM `devices` WHERE `device_type`='SAS' AND `device_inuse`=1";
			}
			if($_GET['filter']=="all" && $_GET['var']=="failed") {
				$sql = "SELECT * FROM `devices` WHERE `device_type` in ('SSD','HDD','SAS') AND `device_failed`='YES'";
			}
			if ($_GET['filter']=="all" && $_GET['var']=="notuse") {
				$sql = "SELECT * FROM `devices` WHERE `device_type` in ('SSD','HDD','SAS') AND `device_failed`!='YES' AND `device_inuse`='0'";
			}
		} else {
			$sql = "SELECT * FROM `devices` WHERE `device_type` in ('SSD','HDD','SAS') AND `device_failed`!='YES'";
		}
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
      		// output data of each row
			echo "<table class='table' id='search_table'>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>Location</th>";
			echo "<th>Vendor</th>";
			echo "<th>Type</th>";
			echo "<th>Physical Label</th>";
			echo "<th>Capacity</th>";
			echo "<th>Serial #</th>";
			echo "<th><center>Manage</center></th>";
			echo "</tr>";
			echo "</thead>";
			while ($row = $result->fetch_assoc()) {
				$device_id = $row["device_id"];
				$first_echo = '';
				if($row["device_parent"]!=0 || $row["device_inuse"]==1) {
					$first_echo = "<tr class='info'>";
				}

				if($row["device_failed"]=='YES') {
					$first_echo = "<tr class='bg-danger'>";
				}

				if ($first_echo == 'NULL' && $row["device_inuse"]!=1) {
					$first_echo = "<tr>";
				}

				echo $first_echo;
				if(get_device_label_from_id($row["device_parent"]) == "None") {
					$device_location = get_rack_name($row['rackid']);
				} else {
					$device_location = get_device_label_from_id($row["device_parent"]);
				}
				echo "<td>$device_location</td>";
				echo "<td>".$row["device_brand"]."</td>";
				echo "<td>".$row["device_type"]."</td>";
				echo "<td>".$row["device_label"]."</td>";
				echo "<td>".$row["device_capacity"]."</td>";
				echo "<td>".$row["device_serial"]."</td>";
				echo"<td><center>";
				echo"<div class='btn-group'>";
				echo"<a href='manage_disk.php?device_id=$device_id' class='btn btn-primary' role='button'>Manage</a>";
				echo "<form action='manage_disk_use_status.php' id='form-status' method='post'>";
				echo "<div class='form-group'>";
				echo "<input type='text' name='device_id' value='$device_id' hidden>";
				echo "</div>";
				echo "</form>";
				echo"<button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
				echo"<span class='caret'></span>";
				echo"<span class='sr-only'>Toggle Dropdown</span>";
				echo"</button>";
				echo "<div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>";
				echo "<button type='submit' name='disk_status' value='status_disk' class='dropdown-item' form='form-status'>Mark Device Not In use</button>";
			//	echo "</form>";
				echo "</div>";
				echo "</div>";
				echo"</center>";
				echo "</td>";
				echo "</tr>";

			}
			echo "</table>";
		} else {
			echo "0 results";
		}
		$conn->close();
		?>

		<div class="pull-right">
			<small><i>Blue indicates the drive in use, Red indicates a failed drive</i></small>
		</div>
	</div>
	<!-- Add HDD Modal -->
	<div class="modal fade" id="add_hdd" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel"><img src="assets/img/drive_add.png"> Add Disk</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="add_device_db.php" id="add_hdds" method="post">
						<div class="row">
							<div class="col-md-6">
								<input type="hidden" name="page_referrer" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
								<label>Device Type</label><br>
								<select data-live-search="true" class="selectpicker" data-width="100%" name="device_type">
									<option value="SSD">SSD</option>
									<option value="HDD">HDD</option>
									<option value="SAS">SAS</option>
								</select>
								<br>
								<label>Device Vendor</label><br>
								<select data-live-search="true" class="selectpicker" data-width="100%" name="device_brand">
									<option value="Hitachi">Hitachi</option>
									<option value="HGST">HGST</option>
									<option value="Seagate">Seagate</option>
									<option value="WD">WD</option>
									<option value="Samsung">Samsung</option>
									<option value="Toshiba">Toshiba</option>
									<option value="Intel">Intel</option>
									<option value="HP">HP</option>
									<option value="Dell">Dell</option>
									<option value="Sandisk">Sandisk</option>
									<option value="Mediamax">Mediamax</option>
									<option value="Corsair">Corsair</option>
									<option value="Whitelabel">Whitelabel</option>
									<option value="ADATA">ADATA</option>
									<option value="Micron">Micron</option>
									<option value="Crucial">Crucial</option>
								</select>
								<br>
								<label>Device Installed To</label><br>
								<?php
								include 'config/db.php';
								$sql = "SELECT * FROM `devices` WHERE device_type='server'";
								$result = $conn->query($sql);
								if ($result->num_rows > 0) { // output data of each row
									echo "<select data-live-search='true' class='selectpicker' data-width='100%' name='device_location'>";
									while ($row = $result->fetch_assoc()) {
										$serverid = $row["device_id"];
										$server_label = $row["device_label"];
										echo "<option value='$serverid'>$server_label</option>";
									}
									echo "</select>";
								} else {
									echo "0 results";
								}
								?>
								<label>Device Date Of Purchase</label>
								<input type="date" class="form-control" name="device_dop">
							</div>

							<div class="col-md-6">
								<label>Warranty valid til</label>
								<input type="date" class="form-control" name="device_warranty">
								<label>Device Label</label>
								<input type="text" class="form-control" name="device_label" required>
								<label>Device Serial</label>
								<input type="text" class="form-control" name="device_serial">
								<label>Device Capacity</label>
								<input type="number" class="form-control" name="device_capacity" required>
								<select class="form-control" name="device_capacity_size">
									<option value="TB">TB</option>
									<option value="GB">GB</option>
									<option value="MB">MB</option>
								</select>
							</div>
						</div>
					</form>


				</div>
				<div class="modal-footer">
					<input type="submit" form="add_hdds" class="btn btn-primary">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<?php include 'libraries/js2.php'; ?>
</body>
</html>
