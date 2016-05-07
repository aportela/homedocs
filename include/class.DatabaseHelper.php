<?php
	require_once("configuration.php");

	class DatabaseParam
	{
		var $name;
		var $value;
		var $type;

		function DatabaseParam($name, $value, $type)
		{
			$this->name = $name;
			$this->value = $value;
			$this->type = $type;
		}

		static function bool($name, $value)
		{
			return(new DatabaseParam($name, $value, PDO::PARAM_BOOL));
		}

		static function null($name)
		{
			return(new DatabaseParam($name, NULL, PDO::PARAM_NULL));
		}

		static function int($name)
		{
			return(new DatabaseParam($name, $value, PDO::PARAM_INT));
		}

		static function str($name, $value)
		{
			return(new DatabaseParam($name, $value, PDO::PARAM_STR));
		}
	}

	class Database
	{		
		static function exec_without_result($sql, $params = array())
		{
			try
			{
				$dbh = new PDO(PDO_CONNECTION_STRING, DATABASE_USERNAME, DATABASE_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				$stmt = $dbh->prepare($sql);
				$total_params = count($params);
				if ($total_params > 0)
				{
					for ($i = 0; $i < $total_params; $i++)
					{						
						$stmt->bindValue($params[$i]->name, $params[$i]->value, $params[$i]->type);
					}
				}				
				$stmt->execute();
				$dbh = NULL;
			}
			catch (PDOException $e)
			{
				throw $e;
			}		
		}

		static function exec_with_result($sql, $params = array())
		{
			$rows = array();
			try
			{
				$dbh = new PDO(PDO_CONNECTION_STRING, DATABASE_USERNAME, DATABASE_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				$stmt = $dbh->prepare($sql);
				$total_params = count($params);
				if ($total_params > 0)
				{
					for ($i = 0; $i < $total_params; $i++)
					{
						$stmt->bindValue($params[$i]->name, $params[$i]->value, $params[$i]->type);
					}
				}				
				if ($stmt->execute())
				{
					while ($row = $stmt->fetch())
					{
						$rows[] = $row;
					}
				}
				$dbh = NULL;
			}
			catch (PDOException $e)
			{
				throw $e;
			}		
			return($rows);
		}

		static function exec_with_result_export($sql, $params = array())
		{
			$rows = array();
			try
			{
				$dbh = new PDO(PDO_CONNECTION_STRING, DATABASE_USERNAME, DATABASE_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				$stmt = $dbh->prepare($sql);
				$total_params = count($params);
				if ($total_params > 0)
				{
					for ($i = 0; $i < $total_params; $i++)
					{
						$stmt->bindValue($params[$i]->name, $params[$i]->value, $params[$i]->type);
					}
				}				
				if ($stmt->execute())
				{
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$rows[] = $row;
					}
				}
				$dbh = NULL;
			}
			catch (PDOException $e)
			{
				throw $e;
			}		
			return($rows);
		}

		public static function get_uuid()
		{
			$rows = Database::exec_with_result(" SELECT REPLACE(UUID(),'-','') AS ID ");
			if (count($rows) > 0)
			{
				return($rows[0]["ID"]);
			}
			else
			{
				return(NULL);
			}
		}
	}
?>