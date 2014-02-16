<?php
	ob_start();

	header('Content-type: application/json');

	require_once ("../../include/configuration.php");
	require_once ("../../include/class.User.php");

	session_start();

	$result = array("success" => FALSE);

	if (isset($_POST["email"]) && isset($_POST["password"]))
	{
		$result = User::login($_POST["email"], $_POST["password"]);
		if ($result["success"] == TRUE)
		{		
			$_SESSION[UID_SESSION_VAR_NAME] = $result["user_id"];
		}	
	}
	else
	{
		$result["error"] = "invalid form data";
	}

	echo json_encode($result);
	
	ob_end_flush();
?>