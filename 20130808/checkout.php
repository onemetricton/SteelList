<?php
/*
 *      Order Processing Page - checkout.php
 *      Author - Zach Smith
 *      <zsmith876@gmail.com>
 */


        require_once 'template.php';
        $currpage = basename($_SERVER['PHP_SELF'], '.php');

	//Go no further in checkout if user not logged in
	if ($sessact) {
		$sql = "SELECT id FROM members WHERE username='$useract_name';";
		$pieces = array();
		$message = "<br/>";
		$anyActive = false;

		if (!($checkuser = $db->query($sql))) {
			$message .= logQueryFail($db, $sql);
		} elseif ($checkuser->num_rows != 1) {
			$message .= "<br/><p class='welcome'><em>Failed to authenticate</em></p>";
		} else {
			$row = $checkuser->fetch_array();
			$userId = $row['id'];
		}
		if ($checkuser) {$checkuser->free();}

		//Load post data into session, prep for redirect
		if (isset($_POST['submit'])) {
			$_SESSION['steelcart'][] = $db->real_escape_string($_POST['submit']);
			$anyActive = true;
		} elseif (isset($_POST['subbrowse'])) {
			foreach ($_POST['actPiece'] as $entry) {
				$_SESSION['steelcart'][] = $db->real_escape_string($entry);
			}
			$anyActive = true;
		} elseif (!empty($_POST['rempo'])) {
			$_SESSION['rempo'] = array_search($_POST['rempo'], $_SESSION['steelcart']);
			$anyActive = true;
		} elseif (isset($_POST['submitorder'])) {
			if (!empty($_POST['selcard'])) {
				$_SESSION['selcard'] = $_POST['selcard'];
			} else {
				$_SESSION['selcard'] = null;
			}
			$anyActive = true;
		}

		//Redirect
		if ($anyActive) {
			header("HTTP:/1.1 303 See Other");
			header("Location: http://$_SERVER[HTTP_HOST]/$currpage");
			die();
		}

		//Remove PO from cart at user's request
		if (isset($_SESSION['rempo'])) {
			$remIndex = $_SESSION['rempo'];
			unset($_SESSION['steelcart'][$remIndex]);
			unset($_SESSION['rempo']);
		}

		//Remove duplicates from the cart and re-index the entries
		if (isset($_SESSION['steelcart'])) {
			$pieces = array_values(array_unique($_SESSION['steelcart']));
		}

		if (count($pieces) < 1) {
			$message .=	"<br/><p class='welcome'><em><a href='search'>" . 
					"Your cart is empty</a></em></p>";
	
		//If 'Complete Order' button is clicked & pymt info provided, file PO
		} elseif (isset($_SESSION['selcard'])) {
			$columns = "piece_id, credit_id, customer_id, order_time";
			$now = date("Y:m:d:H:i:s", time());
			$db_order =	new mysqli($dbhost, $dbuser_order, $dbpass_order, $dbname) 
					or die("<p>Connection Failure:" . mysqli_error() . "</p>");

			for ($i = 0; $i < count($pieces); $i++) {
				$columnData =	"'" . $pieces[$i] . "', '" . $_SESSION['selcard'] . 
						"', '$userId', '$now'";
				$sql = "INSERT INTO purchase_order ($columns) VALUES ($columnData);";

				if (!$db_order->query($sql)) {
					$message .= logQueryFail($db_order, $sql);
				} else {
					$message .= "<p><em>Order Complete</em></p>";
				}
			}

			unset($_SESSION['steelcart']);
			unset($_SESSION['selcard']);
			if ($db_order) {$db_order->close();}
		} elseif (array_key_exists('selcard', $_SESSION)) {
			$message .=	"<br/><p class='welcome'><em>" . 
					"<a href='$currpage'>Please choose a payment option</a></em></p>";
			unset($_SESSION['selcard']);

		//Display shopping cart info and options
		} else {
			echo	"<br/><p class='welcome'><strong>Shopping cart:</strong></p>" . 
				"<form method='post' action='$currpage' id='rempo'>" . 
				"<table><thead><th>OD</th><th>ID</th><th>WT</th>" . 
				"<th>Length</th><th>Grade</th></thead>";
			$columnData = "false";
			$poData = "false";

			for ($i = 0; $i < count($pieces); $i++) {
				//For printing shopping cart
				$columnData .= " OR id='" . $pieces[$i] . "'";
				//For cheching duplicate PO's
				$poData .= " OR piece_id='" . $pieces[$i] . "'";
			}

			$sql =  "SELECT id, OutD, InD, WT, Length, Grade FROM inventory " .
				"WHERE $columnData;";

			//Populate the results table
			if(!($result = $db->query($sql))) {
				$message .= logQueryFail($db, $sql);
			} else {
				//Cycling result rows
				while($row=$result->fetch_array()) {
					echo "<tr>";

					//Cylcing result columns
					foreach ($row as $field=>$entry) {
						if (preg_match("#[A-z]+#", $field) && $field != 'id') {
							if (preg_match("#^[0-9]+$#", $entry)) {
								echo    "<td>" . sprintf('%06d', $entry) .
									"</td>";
							} else {
								echo "<td>$entry</td>";
							}
						}
					}

					echo	"<td><button type='submit' name='rempo' value='" . 
						$row['id'] . "'>Remove</button></td></tr>";
				}
			}

			if ($result) {$result->free();}

			echo "</form></table><p><em>Total Results: " . count($pieces) . "</em></p>";
			$sql = "SELECT piece_id FROM purchase_order WHERE $poData;";

			//Check whether requested piece is already ordered, inform user
			if (!($checkDupPO = $db->query($sql))) {
				$message .= logQueryFail($db, $sql);
			} elseif ($checkDupPO->num_rows > 0) {
				$dupData = "false";

				//Build WHERE clause for all pieces in conflicting PO's
				while ($dupPO = $checkDupPO->fetch_array()) {
					$dupData .= " OR id='" . $dupPO['piece_id'] . "'";
				}
				$sql =  "SELECT id, OutD, InD, WT, Length, Grade FROM inventory " . 
					"WHERE $dupData;";

				//Given duplicate PO, find inventory data and display
				if (!($checkDup = $db->query($sql))) {
					$message .= logQueryFail($db, $sql);
				} else {
					//Cycling duplicate rows
					while($row = $checkDup->fetch_array()) {
						echo    "<br/><p class='welcome'></p><table><thead>" . 
							"<th>OD</th><th>ID</th><th>WT</th><th>Length" . 
							"</th><th>Grade</th></thead>";

						//Cycling duplicate columns
						foreach ($row as $field=>$entry) {
							if (preg_match("#[A-z]+#", $field) && $field != 'id') {
								if (preg_match("#^[0-9]+$#", $entry)) {
									echo    "<td>" . 
										sprintf('%06d', $entry) . 
										"</td>";
								} else {
									echo "<td>$entry</td>";
								}
							}
						}

						echo	"</tr></table>" . 
							"<p><em>These pieces are not available</em></p>";
					}
				}

               	                if ($checkDup) {$checkDup->free();}

			//Payment information
			} else {
				$db_card =	new mysqli($dbhost, $dbuser_acc, $dbpass_acc, $dbname)
						or die("<p>Connection Failure: " . mysqli_error() . "</p>");
				$sql =	"SELECT id, type, safe_num FROM credit_cards " . 
					"WHERE member_id='$userId';";

				if (!($checkcards = $db_card->query($sql))) {
					$message .= logQueryFail($db_acc, $sql);
				} else {
					echo	"<p class='welcome'></p>" . 
						"<form method='post' action='$currpage' id='placeorder'>" . 
						"<table><thead><th colspan = 3><strong>Payment Options" . 
						"</strong></th>";
					while ($row = $checkcards->fetch_array()) {
						echo	"<tr><td>" . $row['type'] . "</td><td><em>" . 
							$row['safe_num'] . "</em></td><td>" . 
							"<input type='radio' name='selcard' value = '" . 
							$row['id'] . "'></td></tr>";
					}
					echo	"</table><div class='center'>" . 
						"<button type='submit' name='submitorder'>Complete Order" . 
						"</button></form><p>" . 
						"<form method='post' action='addcard'>" . 
						"<button type='submit' name='cartcard'>Add Card</button>" . 
						"</p></div>";
				}

				if ($checkcards) {$checkcards->free();}
			}

			if ($checkDupPO) {$checkDupPO->free();}
			if ($db_card) {$db_card->close();}
		}
		echo $message;

	} else {
		echo "<br/><p class='welcome'><a href='register'>Please login to place an order</a></p>";
	}

	if ($db) {$db->close();}
	require_once 'footer.php';
?>
