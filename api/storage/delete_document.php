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
		if (! isset($_POST["id"]))
		{
			header('HTTP/1.0 500 Server Error');
			exit;
		}
		else
		{
			$d = new Document();
			$d->id = $_POST["id"];
			$result = $d->delete();
		}
	}
	else
	{
		$result["error"] = "access denied";
	}

	echo json_encode($result);
	
	ob_end_flush();	

?>