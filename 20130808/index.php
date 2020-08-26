<?php
/*
 *      Homepage - index.php
 *      Author: Zach Smith
 *      <zsmith876@gmail.com>
 */


        require_once 'template.php';
        $currpage = basename($_SERVER['PHP_SELF'], '.php');

	echo "<br/><br/><br/>";
	echo "<p class='welcome'>Welcome to the Steel List home page!</p>";
	echo 	"<p>If your browser is Internet Explorer prior to version 9 or based on KHTML, " . 
		"you may encounter rendering problems. Our apologies.</p>";

	require_once 'footer.php';
?>
