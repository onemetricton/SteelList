<?php
/*
 *      User Login and Registration Page - register.php
 *      Author - Zach Smith
 *      <zsmith876@gmail.com>
 */

        require_once 'template.php';
        $currpage = basename($_SERVER['PHP_SELF'], '.php');
?>

<br/><p class="welcome"><strong>Login existing user:</strong></p>

<form method="post" action=" <?php echo $currpage; ?> " id="login">
<table>
	<tr><td>User Name</td><td><input type="text" name="name" value="" autofocus></td></tr>
	<script>$(function() {$('[autofocus]').focus()});</script>	<!-- IE compat -->
	<tr><td>Password</td><td><input type="password" name="pass" value=""></td></tr>
</table>
<div class="center"><button type="submit" name="submit">Submit</button></div>
</form><br />

<?php
	//Retain a registration form in case of error correction
	function keepFormData($fieldName, $endUserReg) {
		if (!empty($_POST[$fieldName]) && !$endUserReg) {
			echo 'value="' . $_POST[$fieldName] . '"';
		}
		else {
			echo 'value=""';
		}
	}

	//Limit the number of valid login attempts
	function checkBrute($user, $db_login) {
		$now = time();
		$failsAllowed = 5;
		$waitMinutes = 5;
		$attemptWindow = $now - ($waitMinutes * 60);

		if ($stmt = $db_login->prepare(	"SELECT time FROM login_attempts " . 
						"WHERE user=? AND time>?")) {
			$stmt->bind_param('ss', $user, $attemptWindow);
			$stmt->execute();
			$stmt->store_result();

			if ($stmt->num_rows > $failsAllowed) {
				return true;
			} else {
				return false;
			}

			$stmt->free_result();
		}
	}

	$userActField = checkActive($_POST, count($_POST));
	$message = "<br/>";

	//$sessact defined in config.php
	if ($sessact) {
		//$message .= "<p><em><a href='userhome'>Logged In</a></em></p>";
		echo "<meta http-equiv='refresh' content='0;URL=userhome'>";
	}

	//If user isn't logged in but credentials are provided, check against mysql
	elseif (!empty($userActField['name']) && !empty($userActField['pass'])) {
		$salt = "";
		$sql = "SELECT last_update FROM members WHERE username = '" . $_POST['name'] . "';";
		if (!($getsalt = $db->query($sql))) {
			$message .= logQueryFail($db, $sql);
		} else {
			$row = $getsalt->fetch_array();
			$salt = $row['last_update'];
		}
		if ($getsalt) {$getsalt->free();}

		$username = $db->real_escape_string($_POST['name']);
		$password = sha1($db->real_escape_string($_POST['pass']) . $salt);
		$sql =	"SELECT * FROM members " . 
			"WHERE username = '$username' AND password = '$password';";

		if (!($checklogin = $db->query($sql))) {
			$message .= logQueryFail($db, $sql);
		} else {
			if ($checklogin->num_rows==1) {
				$row = $checklogin->fetch_array();
				if (!checkBrute($row['username'], $db)) {
					$_SESSION['Username'] = $username;
					$_SESSION['EmailAddress'] = $row['email'];
					$_SESSION['LoggedIn'] = 1;
					echo "<meta http-equiv='refresh' content='0;URL=$currpage'>";
				} else {
					$message .= "<p><em>Login Attempts Exceeded: Wait 5 Minutes</em></p>";
				}
			} else {
				$now = time();
				$sql = "INSERT INTO login_attempts (user, time) VALUES ('$username', '$now');";
				if(!$db->query($sql)) {
					$message .= logQueryFail($db, $sql);
				}
				$message .= "<p><em>Login Failure</em></p>";
			}
		}
		if ($checklogin) {
			$checklogin->free();
		}

	}
	elseif (!empty($userActField['name']) || !empty($userActField['pass'])) {
		$message .= "<p><em>Login Failure</em></p>";
	}

	else {			//block doesn't close until after registration form
	$success = false;	//becomes true after successful registration

	//If credentials aren't provided but registration form is filled, insert into members
	if ($userActField['username'] && $userActField['userpass']) {
		$now = time();
                $username = $db->real_escape_string($_POST['username']);
                $password = sha1($db->real_escape_string($_POST['userpass']) . $now);
                $email = $db->real_escape_string($_POST['email']);
		$fName = $db->real_escape_string($_POST['first']);
		$lName = $db->real_escape_string($_POST['last']);
		$company = $db->real_escape_string($_POST['company']);
		$phone = $db->real_escape_string($_POST['phone']);

		//Check registration form for errors
		if ($_POST['userpass'] != $_POST['retypepw']) {
			$message .= "<p><em>Password Mismatch</em></p>";
		} else {

			$db_register =	new mysqli($dbhost, $dbuser_order, $dbpass_order, $dbname) 
					or die("Connection Error: " . mysqli_error());
			$sqlDupName = "SELECT * FROM members WHERE username = '$username';";
			$sqlDupEmail = "SELECT * FROM members WHERE email = '$email';";
			$userField = "username, password, email, first_name, last_name, company, last_update";
			$userData =	"'$username', '$password', '$email', " . 
					"'$fName', '$lName', '$company', '$now'";

			if (!($checkUname = $db->query($sqlDupName))) {
				$message .= logQueryFail($db, $sqlDupName);
			} elseif ($checkUname->num_rows > 0) {
				$message .= "<p><em>Name Unavailable<em></p>";
			} elseif (pwStrength($db->real_escape_string($_POST['userpass'])) < $minpw) {
				$message .= 	"<p><em>Try a longer password or improve it " . 
						"by adding a number or symbol</em></p>";
			} elseif (!$userActField['company']) {
				$message .= "<p><em>Please specify a company</em></p>";
			} elseif (!$userActField['last']) {
				$message .= "<p><em>Please specify a last name</em></p>";
			} elseif (!$userActField['first']) {
				$message .= "<p><em>Please specify a first name</em></p>";
			} elseif (!$userActField['email']) {
				$message .= "<p><em>Please specify an email address</em></p>";
			} elseif (!($checkEmail = $db->query($sqlDupEmail))) {
				$message .=  logQueryFail($db, $sqlDupEmail);
			} elseif ($checkEmail->num_rows > 0) {
				$message .=	"<p><em>The specified email address " . 
						"is already registered</em></p>";
			} else {
				if ($userActField['phone']) {
					$userField .= ", phone";
					$userData .= ", '" . $_POST['phone'] . "'";
				}

				$sql = 	"INSERT INTO members ($userField) VALUES ($userData);";
				if(!($registerquery = $db_register->query($sql))) {
					$message .= logQueryFail($db_register, $sql);
				} elseif ($registerquery) {
					$message .= "<p><em>Account Successfully Created</em></p>";
					$success = true;
				} else {
					$message .= "<p><em>Account Creation Failure</em></p>";
				}
			}

			if ($db_register) {$db_register->close();}
			if ($checkUname) {$checkUname->free();}
		}
	} elseif ($userActField['username']) {
		$message .= "<p><em>Please choose a password</em></p>";
	}
?>

<br/><p><strong>Register new user:</strong></p>
<form method="post" action=" <?php echo $currpage; ?> " id="adduserform">
<table>
	<tr>
	<td>User Name:</td><td><input type="text" id="uname" name="username"
	<?php	keepFormData('username', $success); ?>></td>
        <td>Company:</td><td><input type="text" name="company"
	<?php	keepFormData('company', $success); ?>></td>
	</tr><tr>
       	<td>Password:</td><td><input type="password" name="userpass" value=""></td>
	<td>Retype PW</td><td><input type="password" name="retypepw" value=""></td>
	</tr><tr>
	<td>First Name:</td><td><input type="text" name="first"
	<?php	keepFormData('first', $success); ?>></td>
	<td>Last Name:</td><td><input type="text" name="last"
	<?php	keepFormData('last', $success); ?>></td>
	</tr><tr>
	<td>Email:</td><td colspan=2><input type="email" name="email"
	<?php	keepFormData('email', $success); ?>></td>
	</tr><tr>
        <td>Phone:</td><td><input type="tel" name="phone"
	<?php	keepFormData('phone', $success); ?>></td>
	<td><em>(optional)</em></td>
	</tr>
</table>
<div class="center"><button type="submit" name="submit">Submit</button></div>
</form>

<?php
	} //!$sessact (no active session), !empty($_POST['name']) , !empty($_POST['pass'])  ---  are all false
	echo $message;
	if($db) {$db->close();}

	require_once 'footer.php';
?>
