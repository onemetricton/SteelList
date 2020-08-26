<?php

//Secure-er login function
//http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP
function sec_session_start() {
	if(!empty($_SESSION['LoggedIn']) && !empty($_SESSION['Username'])) {
		$session_name='sec_session_id';	//custom sess name --- CHANGE
	}
	$secure=false;	//set true if using https
	$httponly=true;	//stops js accessing session id

	ini_set('session.use_only_cookies',1);		//forces sessions to only use cookies
	$cookieParams = session_get_cookie_params();	//gets current cookie params
//	session_name($session_name);			//WIP: unable to log in
	session_start();
	session_regenerate_id(true);			//regen's session & deletes old one
}


//Returns possible combinations given password length and types of characters
//Relies on attacker not knowing how many characters of which sets are used ([A-z] vs [0-9] vs [...])
function pwStrength($candidate) {
	$uppercount = preg_match_all("#[A-Z]#", $candidate);
	$lowercount = preg_match_all("#[a-z]#", $candidate);
	$numcount = preg_match_all("#[0-9]#", $candidate);
	$symcount = preg_match_all("#[!@\#\$%^&*]#", $candidate);
	$charcount = 0;
	$charspace = 0;

	if ($uppercount) {
		$charspace += 26;
		$charcount += $uppercount;
	}
	if ($lowercount) {
		$charspace += 26;
		$charcount += $lowercount;
	}
	if ($numcount) {
		$charspace += 10;
		$charcount += $numcount;
	}
	if ($symcount) {
		$charspace += 8;
		$charcount += $symcount;
	}

	return pow($charspace, $charcount);
}


//Calculate which search form fields have been activated/filled out
function checkActive ($params, $bound) {
        $active_field = array();
        $index = 0;
        $actAny = false;

        foreach ($params as $field=>$entry) {
                if (preg_match("#[\\S]+#", $entry)) {
                        $active_field[$field] = 1;
                        $actAny = true;
                } else {
			$active_field[$field] = 0;
		}
                if ($index==$bound) {break;}
                $index++;
        }
        if ($actAny) {
                return $active_field;
        } else {
                return $actAny;
        }
}


$dbhost='localhost';
$dbname='steellist';
$dbusername='search';
$dbuserpass='HfUmTET4ttqdHP3N';

$dbuser_order='order';
$dbpass_order='REa2J6q9DtHTLPmQ';

$dbuser_acc='accounts';
$dbpass_acc='YQJwsh83HB6mGwnd';

$dbname_mail='mail';
$dbuser_mail='mail_search';
$dbpass_mail='47ZFpMEppcvEzuUm';

$minpw = 2821109907456;

ini_set('display_errors', 1);
date_default_timezone_set("US/Central");
sec_session_start();

//$db is intentionally left open
$db = new mysqli($dbhost, $dbusername, $dbuserpass, $dbname);
if($db->connect_errno > 0) {
	die('<p>Connection Failure: ' . $db->connect_error . '</p>'); 
}

$sessact = FALSE;
if (!empty($_SESSION['LoggedIn']) && !empty($_SESSION['Username'])) {
	$sql = "SELECT * FROM members WHERE username='" . $_SESSION['Username'] . "';";

	if (!($result = $db->query($sql))) {
                printf("[%d] %s\n", $db->errno, $db->error);
	} else {
		$useract = mysqli_fetch_array($result);
		$useract_name = $useract['username'];
		$sessact = TRUE;
		$result->free();
		unset($useract);
	}
}


//Appends user, time, query, and error to log file above web root in event of mysqli::query() failure
function logQueryFail ($mysqli_db, $qfail) {
        $now = date("Y:m:d:H:i:s", time());
	$message = "<$now> ";

	//Retrieve remote address if possible, and user name if logged in
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip_array = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
		$remoteip = trim($ip_array[count($ip_array)-1]);
	} else {
	        $remoteip = $_SERVER['REMOTE_ADDR'];
	}
	if (!empty($_SESSION['LoggedIn']) && !empty($_SESSION['Username'])) {
		$message .= "<" . $_SESSION['Username'] . "> ";
	}

        $message .= "<$remoteip> <$qfail>\n[$mysqli_db->errno] $mysqli_db->error\n\n";
        error_log($message, 3, "/var/www/log/dberr.log");
	return "<p><em>There was a problem processing your request. Please contact the administrator</em></p>";
}

?>
