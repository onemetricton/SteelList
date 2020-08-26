<?php
/*
 *	Credit Card Entry & Validation - addcard.php
 *      Author - Zach Smith
 *      <zsmith876@gmail.com>
 */
	

	require_once 'template.php';
	$currpage = basename($_SERVER['PHP_SELF'], '.php');
	echo "<br/><p class='welcome'><strong>Payment method:</strong></p>";

	//PHP's 32b integers won't exceed 'PHP_INT_MAX'
	//af-design.com/blog/2009/10/28/php-64-bit-integer-modulus-almost/
	function mod($val, $mod) {
		return $val - floor($val/$mod) * $mod;
	}

	//Validate card number, but not account existence
	function cardMod($cardNumber) {
		$candidate = array_reverse(str_split($cardNumber));
		$altCand = array();
		$sumCand = 0;

		for ($i = 0; $i < count($candidate); $i++) {
			if ($i % 2 == 1) {
				$altCand[($i-1)/2] = array_sum(str_split(2 * $candidate[$i]));
				$sumCand += $altCand[($i-1)/2];
			} else {
				$sumCand += $candidate[$i];
			}
		}

		return mod($sumCand, 10);
	}

	//Obscure a credit card number
	function cardSafe($cardNumber) {
		$candidate = str_split($cardNumber);

		for($i = 0; $i < count($candidate) - 4; $i++) {
			$candidate[$i] = '*';
		}

		return implode("", array_slice($candidate, count($candidate) - 12));
	}

	$cardActField = checkActive($_POST, count($_POST));
	$message = "<br/>";

	//non-members not allowed
	if(!$sessact || empty($useract_name)) {
		echo "<meta http-equiv='refresh' content='0;URL=register'>";
	} elseif (isset($_POST['submit'])) {
		$cardnumber = $db->real_escape_string(trim($_POST['cardNum']));
		$cardholder = $db->real_escape_string(trim($_POST['nameOnCard']));
		$cardissuer = $db->real_escape_string(trim($_POST['cardType']));
		$cardmonth = $db->real_escape_string(trim($_POST['expMon']));
		$cardyear = $db->real_escape_string(trim($_POST['expYear']));
		$cardcode = $db->real_escape_string(trim($_POST['secCode']));

		if (!$cardActField['cardType']) {
			$message .= "<p><em>Please specify a credit card issuer</em></p>";
		} elseif (!$cardActField['nameOnCard']) {
			$message .= "<p><em>Please provide a card holder</em></p>";
		} elseif	(!$cardActField['cardNum'] || 
				strlen($cardnumber) < 12 || 
				cardMod($cardnumber)) {
					$message .= "<p><em>Please provide a valid card number</em></p>";
		} elseif (!$cardActField['secCode']) {
			$message .= "<p><em>Please provide a security code</em></p>";
		} else {
			$db_cardreg =   new mysqli($dbhost, $dbuser_acc, $dbpass_acc, $dbname)
					or die("<p>Connection Error: " . mysqli_error() . "</p>");
			$safeNum = cardSafe($cardnumber);
			$cardField =	"name_on_card, type, card_num, safe_num, exp_mon, exp_year, " . 
					"sec_code, member_id";
			$cardData = 	"'$cardholder', '$cardissuer', '$cardnumber', '$safeNum', " . 
					"'$cardmonth', '$cardyear', '$cardcode'";
			$sqlAuth = "SELECT id FROM members WHERE username='$useract_name';";
			$sqlCard = "SELECT id FROM credit_cards WHERE card_num='$cardnumber';";

			if (!($cardquery = $db_cardreg->query($sqlCard))) {
				$message .= logQueryFail($sqlCard);
			} elseif ($cardquery->num_rows > 0) {
				$message .= "<p><em>This card is being used</em></p>";
			} elseif(!($userquery = $db->query($sqlAuth))) {
				$message .= logQueryFail($db, $sqlAuth);
			} elseif($userquery->num_rows != 1) {
				$message .=	"<p><em>Failed to authenticate: " . 
						"This incident has been reported</em></p>";
			} else {
				$row = $userquery->fetch_array();
				$cardData .= ", '" . $row['id'] . "'";
				$sql = "INSERT INTO credit_cards ($cardField) VALUES ($cardData);";

				if(!$db_cardreg->query($sql)) {
					$message .= logQueryFail($db_cardreg, $sql);
				} else {
					$message .= "<p><em>Card: $safeNum  added for $useract_name</em></p>";
				}
			}
		
			if ($cardquery) {$cardquery->free();}
			elseif ($userquery) {$userquery->free();}
			if ($db_cardreg) {$db_cardreg->close();}
		}
	}
?>

<form method="post" action=" <?php echo $currpage; ?> " id="credit_card">
<table>
<tr><td>Name On Card</td><td><input type="text" name="nameOnCard" value="" autofocus></td>
        <script>$(function() {$('[autofocus]').focus()});</script>      <!-- IE compat -->
        <td>Card Number</td><td><input type="text" name="cardNum" value=""></td></tr>
        <tr><td>Issuer</td><td><select name="cardType">
                <option value=''></option>
                <option value='American Express'>American Express</option>
                <option value='Discover'>Discover</option>
                <option value='MasterCard'>MasterCard</option>
                <option value='Visa'>Visa</option>
        </select></td><td>Security Code</td><td><input type="text" name="secCode" value=""></td></tr>
	<tr><td>Expiration Month</td><td><select name="expMon">
		<option value='1'>January</option><option value='2'>February</option>
		<option value='3'>March</option><option value='4'>April</option>
		<option value='5'>May</option><option value='6'>June</option>
		<option value='7'>July</option><option value='8'>August</option>
		<option value='9'>September</option><option value='10'>October</option>
		<option value='11'>November</option><option value='12'>December</option></select></td>
	<td>Expiration Year</td><td><select name="expYear">

<?php
	$currYear = date('Y');
	settype($currYear, 'integer');

	for ($i = $currYear; $i <= $currYear + 10; $i++) {
		echo "<option value='$i'>$i</option>";
	}

	echo	"</select></td></tr></table><div class='center'>" . 
		"<button type='submit' name='submit'>Submit</button></div></form><br/>";
	echo $message;

        if ($db) {$db->close();}
	require_once 'footer.php';
?>
