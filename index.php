<?php

	ob_start();

	session_start();

	require_once "include/configuration.php";	
	require_once "include/class.User.php";
	if (! User::is_logged())
	{
		include("templates/login.tpl");    
	}
	else
	{
		include("templates/default.tpl");   			
	}

	ob_end_flush();
?>
