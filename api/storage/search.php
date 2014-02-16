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
		$words = isset($_POST["q"]) ? $_POST["q"] : NULL;
		$tags = (isset($_POST["tags"]) && strlen($_POST["tags"]) > 0) ? explode(",", $_POST["tags"]): array();
		$creation_date = isset($_POST["creation_date"]) ? intval($_POST["creation_date"]) : 0;
		$result = Document::search($words, $tags, $creation_date);
	}
	else
	{
		$result["error"] = "access denied";
	}

	echo json_encode($result);
	
	ob_end_flush();	
?>