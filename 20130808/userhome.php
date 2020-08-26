<?php
/*
 *      User Account Home Page - userhome.php
 *      Author - Zach Smith
 *      <zsmith876@gmail.com>
 */


	require_once 'template.php';
	$currpage = basename($_SERVER['PHP_SELF'], '.php');

	echo "<br/><p class='welcome'></p>";
	$message = "<br/>";

	//Logout
	if (isset($_POST['submitlo'])) {
		unset($useract_name);
		$sessact = false;
		$_SESSION = array();
		session_destroy();
	}

	if ($sessact) {
		$db_card =      new mysqli($dbhost, $dbuser_acc, $dbpass_acc, $dbname)
				or die("<p>Connection Failure: " . mysqli_error() . "</p>");

		//Remove card from db at user's request
		if (isset($_POST['remcard'])) {
			$sql = "DELETE FROM credit_cards WHERE id='" . $_POST['remcard'] . "';";
			
			if (!$db_card->query($sql)) {
				$message .= logQueryFail($db_card, $sql);
			}
		}

		//Change password via post
		if (isset($_POST['submitpw'])) {
			if (!empty($_POST['oldpass']) && !empty($_POST['newpass'])) {
				$salt = ""; 
				$sql =	"SELECT last_update FROM members " . 
					"WHERE username = '$useract_name';";
				if (!($getsalt = $db->query($sql))) {
					$message .= logQueryFail($db, $sql);
				} else {
					$row = $getsalt->fetch_array();
					$salt = $row['last_update'];
				}
				if ($getsalt) {$getsalt->free();}

				$password = sha1($db->real_escape_string($_POST['oldpass']) . $salt);
				$sql =	"SELECT * FROM members " . 
					"WHERE username='$useract_name' AND password='$password';";

				//Query for users that have $_POST['oldpass'] and check new pw's
				if (!($checkpass = $db->query($sql))) {
					$message .= logQueryFail($db, $sql);
				} elseif ($_POST['newpass'] != $_POST['retypenew']) {
					$message .=	"<p><em>Entries for desired password do not match" . 
							"</em></p>";
				} elseif ($checkpass->num_rows != 1) {
					$message .= "<p><em>Authentication Failure</em></p>";
				} elseif ($_POST['oldpass'] == $_POST['newpass']) {
					$message .=	"<p><em>Old and new passwords should be different" . 
							"</em></p>";
				} elseif (pwStrength($_POST['newpass']) < $minpw ) {
					$message .=	"<p><em>Try a longer password " . 
							"or improve it by adding a number or symbol</em></p>";
				} else {
					$now = time();
					$row = $checkpass->fetch_array();
					$pass_new = sha1($db->real_escape_string($_POST['newpass']) . $now);
					$db_register =
						new mysqli($dbhost, $dbuser_order, $dbpass_order, $dbname)
						or die ("<p>Connection Failure: " . mysqli_error() . "</p>");
					$sql =	"UPDATE members SET password='$pass_new', " . 
						"last_update='$now' WHERE username='$useract_name';";

					if (!($pwchangequery = $db_register->query($sql))) {
						$message .= logQueryFail($db_register, $sql);
					} elseif ($pwchangequery) {
						$message .= "<p><em>Password Successfully Changed</em></p>";
					} else {
						$message .= "<p><em>Failed To Change Password</em></p>";
					}

					if ($db_register) {
						$db_register->close();
					}
				}
			} else {
				$message .=	"<p><em>Enter your current password " . 
						"then the desired password twice.</em></p>";
			}
		}

		//Change account info (Email, Phone)
		if (isset($_POST['submitinfo'])) {
			$infoActField = checkActive($_POST, count($_POST));

			if (!$infoActField['email'] && !$infoActField['phone']) {
				$message .= 	"<p><em>Please provide a current email address " . 
						"or phone number</em></p>";
			} else {
				$chmail = $db->real_escape_string(trim($_POST['email']));
				$chphnum = $db->real_escape_string(trim($_POST['phone']));
				$chdata = "";

				if ($infoActField['email'] && $infoActField['phone']) {
					$chdata .= "email='$chmail', phone='$chphnum'";
				} elseif ($infoActField['email']) {
					$chdata .= "email='$chmail'";
				} elseif ($infoActField['phone']) {
					$chdata .= "phone='$chphnum'";
				}

				$db_register =	new mysqli($dbhost, $dbuser_order, $dbpass_order, $dbname)
						or die("<p>Connection Failure: " . mysqli_error() . "</p>");
				$sql = "UPDATE members SET $chdata WHERE username='$useract_name';";

				if (!$db_register->query($sql)) {
					$message .= logQueryFail($db_register, $sql);
				} else {
					$message .= "<p><em>Account change successful</em></p>";
				}

				if ($db_register) {
					$db_register->close();
				}
			}
		}

		$sql =	"SELECT id, first_name, last_name, email, phone, company " . 
			"FROM members WHERE username='$useract_name';";

		//Display user info
		if (!($checkuser = $db->query($sql))) {
			$message .= logQueryFail($db, $sql);
		} else {
			$useract = $checkuser->fetch_array();

			echo 	"<p class='welcome'><table><thead><th colspan=2>" . 
				"<strong>$useract_name's Info</strong></thead>";
			foreach ($useract as $field=>$entry) {
				if (preg_match("#[A-z]+#", $field) && $field != "id" && $field != "password") {
					$refield = ucwords(preg_replace("#_#", " ", $field));
					echo "<tr><td><em>$refield:</em></td><td>$entry</td></tr>";
				 }
			}
			echo 	"</table></p>" . 
				"<form method='post' action='$currpage' id='logout'><div class='center'>" . 
				"<button type='submit' name='submitlo' value=''>Logout</button></div></form>";
		}

		echo "<p class='welcome'></p>";
		$sql =  "SELECT id, type, safe_num FROM credit_cards " . 
			"WHERE member_id='" . $useract['id'] . "';";

		//Display payment options
		if (!($checkcards = $db_card->query($sql))) {
			$message .= logQueryFail($db_acc, $sql);
		} elseif ($checkcards->num_rows > 0) {
			echo	"<form method='post' action='$currpage'><table><thead>" . 
				"<th colspan = 3><strong>Payment Options</strong></th>";
			while ($row = $checkcards->fetch_array()) {
				echo    "<tr><td>" . $row['type'] . "</td><td><em>" . $row['safe_num'] . 
					"</em></td><td><button type='submit' name='remcard' value='" . 
					$row['id'] . "'>Remove</button></td></tr>"; 
			}   
			echo	"</table></form>";
		}
		echo	"<div class='center'><p><form method='post' action='addcard'>" . 
			"<button type='submit' name='cartcard'>Add Card</button></form></p></div>";

		if ($checkuser) {$checkuser->free();}
		if ($checkcards) {$checkcards->free();}
		if ($db_card) {$db_card->close();}
?>

<p class="welcome"></p>
<form method="post" action=" <?php echo $currpage; ?> " id="pwchange">
<table class="usersub1">
<thead><th colspan=2>Change Password</th></thead>
<tr><td>Old Password</td><td><input type="password" name="oldpass" value=""></td>
<tr><td>New Password</td><td><input type="password" name="newpass" value=""></td>
<tr><td>Retype New Pass</td><td><input type="password" name="retypenew" value=""></td>
</table>
<div class="center">
<button type="submit" name="submitpw" value="">Submit</button>
</div>
</form>

<p class="welcome"></p>
<form method="post" action=" <?php echo $currpage; ?> " id="usermgmt">
<table class="usersub1">
<thead><th colspan=3>Change Account Info</th></thead>
<tr><td>Email</td><td colspan=2><input type="email" name="email"></td><td></td></tr>
<tr><td>Phone</td><td><input type="phone" name="phone"></td></tr>
</table>
<div class="center">
<button type="submit" name="submitinfo" value="">Submit</button>
</div>
</form>

<?php
		echo $message;
	} else {
		//Non-members not allowed!
		echo "<meta http-equiv='refresh' content='0;URL=register'>";
	}   

	if ($db) {$db->close();}
	require_once 'footer.php';
?>
