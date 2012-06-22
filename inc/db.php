<?php
$title = "file sharing website";
$GLOBALS['root'] = $_SERVER["DOCUMENT_ROOT"];

mysql_connect('mysql.c.umu.li','cumuli2','ubz3144') or die(mysql_error());
mysql_select_db('cumuli2') or die(mysql_error()); 
?>