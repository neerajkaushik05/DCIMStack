<br>
<label>Device HDDs</label>
<?php
include 'config/db.php';
$sql = "SELECT * FROM `devices` WHERE `device_parent`='$device_id'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	// output data of each row
	while ($row = $result->fetch_assoc()) {
		echo "<br>";
		echo $row["device_label"];
	}
} else {
	echo "0 results";
}
?>
</form>