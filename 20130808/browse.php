<?php
/*	
 *	Inventory Dump Page - browse.php
 *	Author - Zachary Smith
 *	<zsmith876@gmail.com>
 */


	require_once 'template.php';
	$currpage = basename($_SERVER['PHP_SELF'], '.php');

	$priority = "Grade";
	$sql = "SELECT id, OutD, InD, WT, Length, Grade FROM inventory ORDER BY $priority;";
	$message = "<br/>";
	echo "<br/><p class='welcome'><strong>Current inventory:</strong></p>";

	//Populate the results table
	if (!($result = $db->query($sql))) {
		$message .= logQueryFail($db, $sql);
	} else {
		echo	"<form method='post' name='subbrowse' action='checkout'><table>" . 
			"<thead><th>OD</th><th>ID</th><th>WT</th><th>Length</th><th>Grade</th></thead>";

		//Cycling result rows
		while ($row = $result->fetch_array()) {
			$piecenum = $row['id'];
			echo "<tr>";

			//Cycling result columns
			foreach ($row as $field=>$entry) {
				if (preg_match("#[A-z]+#", $field) && $field != 'id') {
					if (preg_match("#^[0-9]+$#", $entry)) {
						echo "<td>" . sprintf('%06d', $entry) . "</td>";
					} else {
						echo "<td>$entry</td>";
					}
				}
			}

			echo "<td><input type='checkbox' name='actPiece[]' value=$piecenum></td></tr>";
		}

		echo	"</table><div class='center'><input type='submit' name='subbrowse' value='Order'>" . 
			"</div></form>";
	}

	echo $message;
	require_once 'footer.php';
?>
