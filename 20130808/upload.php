<?php
/*
 *      File Upload Page - upload.php
 *      Author - Zach Smith
 *      <zsmith876@gmail.com>
 */


        require_once 'template.php';
	$currpage = basename($_SERVER['PHP_SELF'], '.php');

	echo "<br/><p class='welcome'></p>";

	if (!empty($_FILES['file']['name'])) {
		if ($_FILES['file']['error']>0) {
			echo "<p>Error: " . $_FILES['file']['error'] . "</p>";
		} else {
			if (file_exists("upload/" . $_FILES['file']['name'])) {
				echo "<p>" . $_FILES['file']['name'] . " already exists.</p>";
			} elseif (move_uploaded_file($_FILES['file']['tmp_name'], "upload/" . $_FILES['file']['name'])) {
				echo "<p>Operation Successful</p>";
			} else {
				echo "<p>Operation Failed</p>";
			}
		}
	}
?>

<form action="upload" method="post" enctype="multipart/form-data">
<table>
	<tr><td>Filename</td><td><input type="file" name="file" id="file"></td></tr>
	<tr><td colspan=2><div class="center"><button type="submit" name="submit">Upload</button></div></td></tr>
</table>
</form>

<?php
	require_once 'footer.php';
?>
