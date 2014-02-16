<?php
	ob_start();

	header('Content-type: application/json');

	require_once ("../../include/configuration.php");
	require_once ("../../include/class.User.php");
	require_once ("../../include/class.Document.php");

	session_start();

	$result = array("success" => FALSE);

	if (User::is_logged())
	{
		$result = array("success" => TRUE, "tags" => Document::get_user_tags());
	}
	else
	{
		$result["error"] = "access denied";
	}

	echo json_encode($result);
	
	ob_end_flush();	
?>