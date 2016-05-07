<?php
	require_once("configuration.php");
	require_once("class.DatabaseHelper.php");
	require_once("class.File.php");
	require_once("class.Storage.php");
	require_once("class.User.php");

	class Document
	{
		var $id  = NULL;
		var $created = NULL;
		var $title = NULL;
		var $description = NULL;
		var $total_files = 0;
		var $files = array();
		var $tags = array();

		/*
			store document metadata
		*/
		private function add_metadata()
		{
			$sql = " INSERT INTO DOCUMENT (ID, USER_ID, CREATED, TITLE, DESCRIPTION) VALUES (UNHEX(:ID), UNHEX(:USER_ID), CURRENT_TIMESTAMP, :TITLE, :DESCRIPTION) ";
			$params = array(
				DatabaseParam::str(":ID", $this->id),
				DatabaseParam::str(":USER_ID", User::get_session_user_id()),
				DatabaseParam::str(":TITLE", $this->title)				
			);
			if ($this->description != NULL && strlen($this->description) > 0)
			{
				$params[] = DatabaseParam::str(":DESCRIPTION", $title);
			}
			else
			{
				$params[] = DatabaseParam::null(":DESCRIPTION");
			}
			Database::exec_without_result($sql, $params);
		}

		/*
			store document tags
		*/
		private function add_tags()
		{
			$total_tags = count($this->tags);
			if ($total_tags > 0)
			{
				for ($i = 0; $i < $total_tags; $i++)
				{
					$sql = " INSERT INTO DOCUMENT_TAG (DOCUMENT_ID, TAG) VALUES (UNHEX(:DOCUMENT_ID), :TAG) ";
					Database::exec_without_result($sql, 
						array(
							DatabaseParam::str(":DOCUMENT_ID", $this->id),
							DatabaseParam::str(":TAG", trim(strtolower($this->tags[$i])))
						)			
					);					
				}
			}
		}

		/*
			store document file references
		*/
		private function add_files()
		{
			$total_files = count($this->files);
			if ($total_files > 0)
			{
				for ($i = 0; $i < $total_files; $i++)
				{
					$sql = " INSERT INTO DOCUMENT_FILE (DOCUMENT_ID, FILE_ID) VALUES (UNHEX(:DOCUMENT_ID), UNHEX(:FILE_ID)) ";
					Database::exec_without_result($sql, 
						array(
							DatabaseParam::str(":DOCUMENT_ID", $this->id),
							DatabaseParam::str(":FILE_ID", $this->files[$i]->id)
						)			
					);					
				}				
			}
		}

		/*
			delete document metadata
		*/
		private function delete_metadata()
		{
			$sql = " DELETE FROM DOCUMENT WHERE ID = UNHEX(:ID) AND USER_ID = UNHEX(:USER_ID) ";
			Database::exec_without_result($sql, 
				array(
					DatabaseParam::str(":ID", $this->id),
					DatabaseParam::str(":USER_ID", User::get_session_user_id())
				)			
			);
		}

		/*
			remove document tags
		*/
		private function remove_tags()
		{
			$sql = " DELETE FROM DOCUMENT_TAG WHERE DOCUMENT_ID = UNHEX(:DOCUMENT_ID) ";
			Database::exec_without_result($sql, 
				array(
					DatabaseParam::str(":DOCUMENT_ID", $this->id)					
				)			
			);
		}

		/*
			remove document file references
		*/
		private function remove_files()
		{
			$this->get_files();
			if ($this->total_files > 0)
			{				
				for ($i = 0; $i < $this->total_files; $i++)
				{
					$thumb_path = Storage::get_thumbnail_local_path($this->files[$i]->hash);
					if (file_exists($thumb_path))
					{
						unlink($thumb_path);	
					}
					$file_path = Storage::get_storage_path($this->files[$i]->hash);
					if (file_exists($file_path))
					{
						unlink($file_path);	
					}									
					$this->files[$i]->delete_metadata();
				}
				$sql = " DELETE FROM DOCUMENT_FILE WHERE DOCUMENT_ID = UNHEX(:DOCUMENT_ID) ";
				Database::exec_without_result($sql, 
					array(
						DatabaseParam::str(":DOCUMENT_ID", $this->id)
					)			
				);
			}
		}

		private function update_metadata()
		{
			$sql = " UPDATE DOCUMENT SET TITLE = :TITLE, DESCRIPTION = :DESCRIPTION WHERE ID = UNHEX(:ID) AND USER_ID = UNHEX(:USER_ID) ";
			$params = array(
				DatabaseParam::str(":ID", $this->id),
				DatabaseParam::str(":USER_ID", User::get_session_user_id()),
				DatabaseParam::str(":TITLE", $this->title)
			);
			if ($this->description != NULL && strlen($this->description) > 0)
			{
				$params[] = DatabaseParam::str(":DESCRIPTION", $this->description);
			}
			else
			{
				$params[] = DatabaseParam::null(":DESCRIPTION");
			}
			Database::exec_without_result($sql, $params);
		}

		/*
			get document metadata
		*/
		private function get_metadata()
		{
			$sql = " SELECT CREATED, TITLE, DESCRIPTION FROM DOCUMENT WHERE ID = UNHEX(:ID) AND USER_ID = UNHEX(:USER_ID) ";
			$rows = Database::exec_with_result($sql, 
				array(
					DatabaseParam::str(":ID", $this->id),
					DatabaseParam::str(":USER_ID", User::get_session_user_id())
				)
			);
			if (count($rows) == 1)
			{
				$this->created = $rows[0]["CREATED"];
				$this->title = $rows[0]["TITLE"];
				$this->description = $rows[0]["DESCRIPTION"];				
			}		
			else
			{
				// TODO - launch exception ?
			}
		}

		private function get_tags()
		{
			$sql = " SELECT TAG FROM DOCUMENT_TAG WHERE DOCUMENT_ID = UNHEX(:DOCUMENT_ID) ";
			$rows = Database::exec_with_result($sql, 
				array(
					DatabaseParam::str(":DOCUMENT_ID", $this->id)
				)
			);
			$total_tags = count($rows);
			if ($total_tags > 0)
			{
				for ($i = 0; $i < $total_tags; $i++)
				{
					$this->tags[] = $rows[$i]["TAG"];
				}
			}		
		}

		public static function get_user_tags()
		{
			$tags = array();
			$sql = " SELECT DISTINCT TAG FROM DOCUMENT_TAG INNER JOIN DOCUMENT ON DOCUMENT.ID = DOCUMENT_TAG.DOCUMENT_ID WHERE DOCUMENT.USER_ID = UNHEX(:USER_ID) ORDER BY TAG ";
			$rows = Database::exec_with_result($sql, 
				array(
					DatabaseParam::str(":USER_ID", User::get_session_user_id())
				)
			);
			$total_tags = count($rows);
			if ($total_tags > 0)
			{
				for ($i = 0; $i < $total_tags; $i++)
				{
					$tags[] = $rows[$i]["TAG"];
				}
			}	
			return($tags);	
		}

		private function get_files()
		{
			$sql = " SELECT HEX(FILE.ID) AS ID, FILE.SHA1_HASH, FILE.NAME, FILE.UPLOADED, FILE.SIZE FROM DOCUMENT_FILE INNER JOIN FILE ON FILE.ID = DOCUMENT_FILE.FILE_ID WHERE DOCUMENT_FILE.DOCUMENT_ID = UNHEX(:DOCUMENT_ID) ORDER BY FILE.NAME ";
			$rows = Database::exec_with_result($sql, 
				array(
					DatabaseParam::str(":DOCUMENT_ID", $this->id)
				)
			);
			$total_files = count($rows);
			if ($total_files > 0)
			{
				$this->total_files = $total_files;
				for ($i = 0; $i < $total_files; $i++)
				{					
					$f = new File();
					$f->id = $rows[$i]["ID"];
					$f->hash = $rows[$i]["SHA1_HASH"];
					$f->name = $rows[$i]["NAME"];
					$f->uploaded = $rows[$i]["UPLOADED"];
					$f->size = $rows[$i]["SIZE"];
					if (file_exists(Storage::get_thumbnail_local_path($f->hash)))
					{
						$f->thumbnail_url = Storage::get_thumbnail_url_path($f->hash);
					}								
					$this->files[] = $f;
				}
			}		
		}

		/*
			add new document
		*/
		function add()
		{
			$result = array("success" => FALSE);	
			try
			{					
				if ($this->id == NULL)
				{
					$this->id = Database::get_uuid();
				}				
				$this->add_metadata();
				$this->add_tags();
				$this->add_files();
				$result["success"] = TRUE;
			}
			catch (PDOException $e)
			{
				if (defined("DEBUG"))
				{
					$result["pdo_exception_message"] = $e->getMessage();
				}
			}
			catch (Exception $e)
			{
				if (defined("DEBUG"))
				{
					$result["exception_message"] = $e->getMessage();
				}
			}
			return($result);							
		}

		/*
			update document
		*/
		function update()
		{
			$result = array("success" => FALSE);	
			try
			{		
				$this->update_metadata();
				$this->remove_tags();
				$this->add_tags();
				//$this->remove_files();
				//$this->add_files();
				$result["success"] = TRUE;
			}
			catch (PDOException $e)
			{
				if (defined("DEBUG"))
				{
					$result["pdo_exception_message"] = $e->getMessage();
				}
			}
			catch (Exception $e)
			{
				if (defined("DEBUG"))
				{
					$result["exception_message"] = $e->getMessage();
				}
			}
			return($result);				
		}

		/*
			delete document
		*/
		function delete()
		{
			$result = array("success" => FALSE);
			try
			{
				$this->delete_metadata();
				$this->remove_tags();
				$this->remove_files();
				$result["success"] = TRUE;
			}
			catch (PDOException $e)
			{
				if (defined("DEBUG"))
				{
					$result["pdo_exception_message"] = $e->getMessage();
				}
			}
			catch (Exception $e)
			{
				if (defined("DEBUG"))
				{
					$result["exception_message"] = $e->getMessage();
				}
			}
			return($result);
		}

		/*
			get document
		*/
		function get()
		{
			$result = array("success" => FALSE);
			try
			{
				$this->get_metadata();
				$this->get_tags();
				$this->get_files();
				$this->total_files = count($this->files);
				$result["success"] = TRUE;
				$result["document"] = $this;
			}
			catch (PDOException $e)
			{
				if (defined("DEBUG"))
				{
					$result["pdo_exception_message"] = $e->getMessage();
				}
			}
			catch (Exception $e)
			{
				$result["exception_message"] = $e->getMessage();
			}
			return($result);
		}

		/*
			search documents
		*/
		static function search($words = NULL, $tags = array(), $creation_date = NULL)
		{
			$result = array("success" => FALSE, "documents" => array());
			try
			{
				$creation_date_condition = NULL;
				switch($creation_date)
				{
					// TODAY
					case 1:
						$creation_date_condition = "AND DATE(DOCUMENT.CREATED) = DATE(NOW()) ";
					break;
					// YESTERDAY
					case 2:
						$creation_date_condition = "AND DATE(DOCUMENT.CREATED) = DATE(SUBDATE(NOW(), 1)) ";
					break;
					// LAST WEEK
					case 3:
						$creation_date_condition = "AND DOCUMENT.CREATED BETWEEN DATE_SUB(NOW(),INTERVAL 1 WEEK) and NOW() ";
					break;
					// LAST MONTH
					case 4:
						$creation_date_condition = "AND DOCUMENT.CREATED BETWEEN DATE_SUB(NOW(),INTERVAL 1 MONTH) and NOW() ";
					break;
					// LAST YEAR
					case 5:
						$creation_date_condition = "AND DOCUMENT.CREATED BETWEEN DATE_SUB(NOW(),INTERVAL 1 YEAR) and NOW() ";
					break;
				}
				$tags_condition = NULL;
				$total_tags = count($tags);
				if ($total_tags > 0)
				{
					$tag_param_names = array();
					for ($i = 0; $i < $total_tags; $i++)
					{
						$tags[$i] = trim(strtolower($tags[$i]));
						$tag_param_names[$i] = ":TAG_" . $i;
					}
					$tags_condition = " AND EXISTS ( " .
									  " 	SELECT DOCUMENT_TAG.DOCUMENT_ID " .
									  "		FROM DOCUMENT_TAG " .
									  "		WHERE DOCUMENT_TAG.DOCUMENT_ID = DOCUMENT.ID " .
									  "		AND DOCUMENT_TAG.TAG IN (" . implode(',', $tag_param_names) . ") " .
									  "	) ";
				}

				$rows = array();				
				if ($words != NULL)
				{
					$sql = " SELECT HEX(DOCUMENT.ID) AS ID, DOCUMENT.TITLE, DOCUMENT.CREATED, COUNT(FILE.ID) AS TOTAL_DOCUMENT_FILES " .
						   " FROM DOCUMENT_FILE " .
						   " LEFT JOIN DOCUMENT ON DOCUMENT_FILE.DOCUMENT_ID = DOCUMENT.ID " .
						   " LEFT JOIN FILE ON DOCUMENT_FILE.FILE_ID = FILE.ID " .
						   " WHERE DOCUMENT.USER_ID = UNHEX(:USER_ID) " .
						   " AND (FILE.NAME LIKE :WORDS OR DOCUMENT.TITLE LIKE :WORDS OR DOCUMENT.DESCRIPTION LIKE :WORDS) " .
						     $creation_date_condition .
						     $tags_condition .
						   " GROUP BY ID, DOCUMENT.TITLE, DOCUMENT.CREATED " .
						   " ORDER BY DOCUMENT.CREATED DESC ";
					$params = array(
								DatabaseParam::str(":WORDS", "%" . $words . "%"),
								DatabaseParam::str(":USER_ID", User::get_session_user_id())
							  );
					if ($total_tags > 0)
					{
						for ($i = 0; $i < $total_tags; $i++)
						{
							$params[] = DatabaseParam::str($tag_param_names[$i], $tags[$i]);
						}
					}
					$rows = Database::exec_with_result($sql, $params);
				}
				else
				{
					$sql = " SELECT HEX(DOCUMENT.ID) AS ID, DOCUMENT.TITLE, DOCUMENT.CREATED, COUNT(FILE.ID) AS TOTAL_DOCUMENT_FILES " .
						   " FROM DOCUMENT_FILE " .
						   " LEFT JOIN DOCUMENT ON DOCUMENT_FILE.DOCUMENT_ID = DOCUMENT.ID " .
						   " LEFT JOIN FILE ON DOCUMENT_FILE.FILE_ID = FILE.ID " .
						   " WHERE DOCUMENT.USER_ID = UNHEX(:USER_ID) " .
						     $creation_date_condition .
						     $tags_condition .						     
						   " GROUP BY ID, DOCUMENT.TITLE, DOCUMENT.CREATED " .
						   " ORDER BY DOCUMENT.CREATED DESC ";
					$params = array(
							DatabaseParam::str(":USER_ID", User::get_session_user_id())
							);
					if ($total_tags > 0)
					{
						for ($i = 0; $i < $total_tags; $i++)
						{
							$params[] = DatabaseParam::str($tag_param_names[$i], $tags[$i]);
						}
					}
					$rows = Database::exec_with_result($sql, $params);					
				}
				$result["success"] = TRUE;
				$total_rows = count($rows);
				if ($total_rows > 0)
				{
					for ($i = 0; $i < $total_rows; $i++)
					{
						$d = new Document();
						$d->id = $rows[$i]["ID"];
						$d->title = $rows[$i]["TITLE"];
						$d->created = $rows[$i]["CREATED"];
						$d->total_files = $rows[$i]["TOTAL_DOCUMENT_FILES"];						
						$result["documents"][] = $d;
					}
				}	
				$dbh = NULL;
			}
			catch (PDOException $e)
			{
				if (defined("DEBUG"))
				{
					$result["pdo_exception_message"] = $e->getMessage();
				}
			}
			catch (Exception $e)
			{
				$result["exception_message"] = $e->getMessage();
			}
			return($result);
		}
	}
?>