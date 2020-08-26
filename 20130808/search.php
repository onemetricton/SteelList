<?php
/*
 *      Main Search Page - search.php
 *      Author - Zach Smith
 *      <zsmith876@gmail.com>
 */

        require_once 'template.php';
        $currpage = basename($_SERVER['PHP_SELF'], '.php');
?>

<br/><p class="welcome"><strong>Fill in one or more fields:</strong></p>
<form method="post" action=" <?php echo $currpage; ?> " id="searchform">
<table>
     <tr>
        <td>OD Min</td>
        <td><input type='number' name='OD_Min' value='' autofocus></td>
	<script>$(function() {$('[autofocus]').focus()});</script>	<!-- IE -->
        <td>OD Max</td>
        <td><input type='number' name='OD_Max' value=''></td>
     </tr>
     <tr>
        <td>WT Min</td>
        <td><input type='number' name='WT_Min' value=''></td>
        <td>Length</td>
        <td><input type='number' name='Len' value=''></td>
     </tr>
</table>
<p>
        Grade
        <select name='Grd'>
                <option value=''>All</option>
                <option value='Alloy'>Alloy</option>
                <option value='Carbon'>Carbon</option>
                <option value='Stainless'>Stainless</option>
        </select>
        <input type='submit' name='submit'>
        <input type='reset'>
</p>
</form>

<?php
	$message = "<br/>";

	//After client submits form, server receives $_POST data
	if (isset($_POST['submit'])) {
		$odMin = $db->real_escape_string($_POST['OD_Min']);
		$odMax = $db->real_escape_string($_POST['OD_Max']);
		$wtMin = $db->real_escape_string($_POST['WT_Min']);
		$length = $db->real_escape_string($_POST['Len']);
		$grade = $db->real_escape_string($_POST['Grd']);
	        $search_params = array(	"OD-Min"=>$odMin, "OD-Max"=>$odMax, "WT-Min"=>$wtMin, 
					"Length"=>$length, "Grade"=>$grade);

		//If a single numeric field is activated, query $dbname and show results
		if (checkActive($search_params, 3)) {
			$_SESSION['sparams'] = $search_params;
			header("HTTP:/1.1 303 See Other");
			header("Location: http://$_SERVER[HTTP_HOST]/$currpage");
			die();
		} else {
			echo "<p class='welcome'><em>Please qualify your search. </em></p>";
	        }
	}

	//Redirected from server after initial post
	if (array_key_exists('sparams', $_SESSION)) {
		$search_params = $_SESSION['sparams'];
		$active_field = checkActive($search_params, 3);
		$search_cond = "Grade LIKE '%" . $search_params['Grade'] . "%'";

		//Print active search parameters
		echo "<br/><p>";
		foreach ($search_params as $field=>$entry) {
			if (!empty($active_field[$field])) {
				echo "<strong>$field:</strong>$entry ";
			}
		}
		echo "</p>";

		if ($active_field['OD-Min']) {
			$search_cond .= " AND OutD >= " . $search_params['OD-Min']; }
		if ($active_field['OD-Max']) {
			$search_cond .= " AND OutD <= " . $search_params['OD-Max']; }
		if ($active_field['WT-Min']) {
			$search_cond .= " AND WT >= " . $search_params['WT-Min']; }
		if ($active_field['Length']) {
			$search_cond .= " AND Length >= " . $search_params['Length']; }

		//Incorrect $username or $userpass (config) won't show an error in browser
		$priority = "Grade";
		$sql =	"SELECT id, OutD, InD, WT, Length, Grade FROM inventory " . 
			"WHERE $search_cond ORDER BY $priority LIMIT 0, 30;";

		//Populate the results table
		if(!($result = $db->query($sql))) {
			$message .= logQueryFail($db, $sql);
		} elseif ($result->num_rows > 0) {
			echo	"<form method='post' action='checkout' id='orderform'>" . 
				"<div id='pager'><table class='tsort'><thead><th>OD</th>" . 
				"<th>ID</th><th>WT</th><th>Length</th><th>Grade</th></thead>";
				
			//Cycling result rows
			while($row=$result->fetch_array()) {
				echo "<tr>";

				//Cylcing result columns
				foreach ($row as $field=>$entry) {
					if (preg_match("#[A-z]+#", $field) && $field != 'id') {
						if (preg_match("#^[0-9]+$#", $entry)) {
							echo 	"<td>" . sprintf('%06d', $entry) . 
								"</td>";
						} else {
							echo "<td>$entry</td>";
						}
					}
				}

				echo	"<td><button type='submit' name='submit' value='" . 
					$row['id'] . "'>Order</button></td></tr>";
			}

			echo "</form></div></table><p><em>Total Results: $result->num_rows</em></p>";
		} else {
			echo "<p><em>Total Results: 0</em></p>";
		}

		unset($_SESSION['sparams']);

		if ($result) {
			$result->free();
		}
	}
	//end redirect block

	echo $message;
	if ($db) {$db->close();}
	require_once 'footer.php';
?>
