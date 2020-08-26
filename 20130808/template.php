<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">       <!-- IE compatibility -->
<html>
<script src="jquery-1.10.1.min.js"></script>
<script src="//use.edgefonts.net/stardos-stencil.js"></script>
<div class="content">
<head><div class="header"><span>
<link type="text/css" rel="stylesheet" href="style.css"/>
<script type="text/javascript" src="script.js"></script>
<script type="text/javascript" src="jquery.tablesorter.min.js"></script>
<title>Steel List</title>
<h1>Steel List</h1>
<ul>
        <li><a href='/'>Home</a></li>
	<li><a href='browse'>Browse</a></li>
        <li><a href='search'>Search</a></li>
        <li><a href='contact'>Contact</a></li>
<?php
        require_once '../config.php';

        if ($sessact) {
		echo "<li><a href='checkout'>Cart</a></li>";
                echo "<li><a href='userhome'>$useract_name</a></li>";
        } else {
                echo "<li><a href='register'>Login</a></li>";
        }
?>
</ul>
</span></div></head>

<body>
