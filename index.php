<?php
	// check tables setup?
	include("inc/class_database.php");
	$db = new DATABASE();
	$db_connect = $db->connect();
	$tables_exist = $db->table_exists("users");
	if ( empty ( $tables_exist )) { 
		//echo 'NO CONNECTION!!';exit;
		header('Location:admin/setup.php'); exit;
	}

	include("inc/class_template1.php");
	$layout = new theme();
	$layout->output_curl();
	// output the page.
	echo  html_entity_decode (  $layout->webpage , ENT_COMPAT, 'UTF-8');

?>