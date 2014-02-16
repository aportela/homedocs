<?php

	require_once("configuration.php");
	require_once("class.DatabaseHelper.php");

	define('UID_SESSION_VAR_NAME', 'user_id');
	
	class User
	{
		var $id;
		var $email;
		var $password;

		static function is_logged()
		{
			return(isset($_SESSION) && isset($_SESSION[UID_SESSION_VAR_NAME]) ? $_SESSION[UID_SESSION_VAR_NAME] : FALSE);
		}

		static function get_session_user_id()
		{	
			if (isset($_SESSION))		
			{
				return($_SESSION[UID_SESSION_VAR_NAME]);	
			}
			else
			{
				return(NULL);
			}
		}
		
		static function check_existence($email)
		{
			$result = array("success" => FALSE, "exists" => FALSE);
			try
			{
				$sql = " SELECT HEX(ID) AS USER_ID FROM USER WHERE EMAIL = :EMAIL ";
				$rows = Database::exec_with_result($sql, 
					array(
						DatabaseParam::str(":EMAIL", $email)
					)
				);
				if (count($rows) > 0)
				{
					$result["exists"] = TRUE;
				}
				$result["success"] = TRUE;
			}
			catch (PDOException $e)
			{
				$result["exception_message"] = $e->getMessage();
				if (defined("DEBUG"))
				{
					$result["sql_query"] = $sql;
				}
			}		
			return($result);			
		}
		
		static function add($email, $password)
		{			
			$result = array("success" => FALSE);
			try
			{
				$sql = " INSERT INTO USER (ID, EMAIL, PASSWORD) VALUES (UNHEX(REPLACE(UUID(),'-','')), :EMAIL, :PASSWORD) ";
				Database::exec_without_result($sql, 
					array(
						DatabaseParam::str(":EMAIL", $email),
						DatabaseParam::str(":PASSWORD", password_hash($password, PASSWORD_BCRYPT, [ "cost" => 12 ]))
					)
				);
				$result["success"] = TRUE;
			}
			catch (PDOException $e)
			{
				$result["exception_message"] = $e->getMessage();
				if (defined("DEBUG"))
				{
					$result["sql_query"] = $sql;
				}
			}		
			return($result);
		}

		static function login($email, $password)
		{
			$result = array("success" => FALSE);
			try
			{

				$sql = " SELECT HEX(ID) AS USER_ID, PASSWORD FROM USER WHERE EMAIL = :EMAIL ";
				$rows = Database::exec_with_result($sql, 
					array(
						DatabaseParam::str(":EMAIL", $email)
					)
				);				
				if (count($rows) == 1)
				{
					if (password_verify($password, $rows[0]["PASSWORD"]))
					{
						$result["success"] = TRUE;
						$result["user_id"] = $rows[0]["USER_ID"];
					}
					else
					{
						$result["error"] = "Invalid password";
					}
				}
				else
				{
					$result["error"] = "Invalid account";
				}
			}
			catch (PDOException $e)
			{
				$result["exception_message"] = $e->getMessage();
				if (defined("DEBUG"))
				{
					$result["sql_query"] = $sql;
				}
			}		
			return($result);			
		}
	}
?>
