<?php
	ob_start();

	header('Content-type: application/json');

	require_once ("../../include/configuration.php");
	require_once ("../../include/class.User.php");

	session_start();

	$result = array("success" => FALSE);

	if (isset($_POST["email"]) && isset($_POST["password"]))
	{
		if (defined('ALLOW_NEW_ACCOUNTS') && ALLOW_NEW_ACCOUNTS)
		{
			$result = User::check_existence($_POST["email"]);
			if ($result["success"] == TRUE)
			{
				if ($result["exists"] != TRUE)
				{
					$result = User::add($_POST["email"], $_POST["password"]);
				}
				else
				{
					$result["success"] = FALSE;
					$result["error"] = "email used";
				}
			}
			else
			{
				$result["error"] = "error checking email";
			}
		}
		else
		{
			$result["error"] = "new account creation disabled";
		}
	}
	else
	{
		$result["error"] = "invalid form data";
	}

	echo json_encode($result);
	
	ob_end_flush();
?>