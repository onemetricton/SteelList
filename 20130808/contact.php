<?php
/*
 *      Contacts Page - contact.php
 *      Author - Zach Smith
 *      <zsmith876@gmail.com>
 */


        require_once 'template.php';
	$currpage = basename($_SERVER['PHP_SELF'], '.php');
?>

<br/><p class="welcome"></p>
<p><strong>Contact us:</strong></p>

<?php
	$db_mail = new mysqli($dbhost, $dbuser_mail, $dbpass_mail, $dbname_mail) or die ("<p>Connection Failure:" . mysqli_error() . "</p>");	
	$sql="SELECT * FROM contact LIMIT 0, 30;";
	$message = "<br/>";

	if (!($result=$db_mail->query($sql))) {
		$message .= logQueryFail($db, $sql);
	} elseif ($result->num_rows>0) {
		echo "<p><table>";
		while ($row=$result->fetch_array()) {
			$sql="SELECT email FROM virtual_users WHERE id = '" . $row['vmail_id'] . "' LIMIT 0, 30;";
			if (!($result_mail=$db_mail->query($sql))) {
				$message .= logQueryFail($db_mail, $sql);
			} elseif ($result_mail->num_rows==1) {
				$row_mail = $result_mail->fetch_array();
				echo "<tr><td>'" . $row['first_name'] . " " . $row['last_name'] . "'</td><td>" . $row_mail['email'] . "</td></tr>";
			}
		}
		echo "</table></p>";
	}

	if ($result) {
		$result->free();
	}
	if ($db_mail) {
		$db_mail->close();
	}
	if ($db) {
		$db->close();
	}

	if($sessact) {require_once '../.config2.php';}
	require_once 'footer.php';
?>
