<?php

	require_once("configuration.php");
	require_once("class.DatabaseHelper.php");
	require_once("class.User.php");
	require_once("class.Storage.php");

	class File
	{
		var $id = NULL;
		var $hash = NULL;
		var $name = NULL;
		var $uploaded = NULL;
		var $size = 0;
		var $thumbnail_url = NULL;

		function allowed_mime()
		{			
			return(
				in_array(
					strtolower(
						pathinfo($this->name, PATHINFO_EXTENSION)
					), 
					unserialize(ALLOWED_MIME_TYPES)
				)
			);
		}

		/*
			store file metadata
			(don't store dups for files with identical hash)
		*/
		private function add_metadata()
		{
			$sql = " INSERT INTO FILE (ID, USER_ID, SHA1_HASH, NAME, UPLOADED, SIZE) VALUES (UNHEX(:ID), UNHEX(:USER_ID), :SHA1_HASH, :NAME, CURRENT_TIMESTAMP, :SIZE) ";
			Database::exec_without_result($sql, array(
					DatabaseParam::str(":ID", $this->id),
					DatabaseParam::str(":USER_ID", User::get_session_user_id()),
					DatabaseParam::str(":SHA1_HASH", $this->hash),
					DatabaseParam::str(":NAME", $this->name),
					DatabaseParam::str(":SIZE", $this->size)
				)
			);
		}

		/*
			get file metadata
		*/
		function get_metadata()
		{
			$sql = " SELECT SHA1_HASH, NAME, UPLOADED, SIZE FROM FILE WHERE ID = UNHEX(:ID) ";
			$rows = Database::exec_with_result($sql, 
				array(
					DatabaseParam::str(":ID", $this->id)
				)
			);
			if (count($rows) == 1)
			{
				$this->hash = $rows[0]["CREATED"];
				$this->name = $rows[0]["NAME"];
				$this->uploaded = $rows[0]["UPLOADED"];
				$this->size = $rows[0]["SIZE"];
				if (file_exists(Storage::get_thumbnail_local_path($this->hash)))
				{
					$this->thumbnail_url = Storage::get_thumbnail_url_path($this->hash);
				}								
			}				
			else
			{
				// TODO - launch exception ?
			}			
		}

		/*
			get file metadata (for download)
		*/
		function get_download_metadata()
		{
			$allowed = FALSE;
			$sql = " SELECT SHA1_HASH, NAME, UPLOADED, SIZE FROM DOCUMENT_FILE INNER JOIN DOCUMENT ON DOCUMENT.ID = DOCUMENT_FILE.DOCUMENT_ID INNER JOIN FILE ON DOCUMENT_FILE.FILE_ID = FILE.ID WHERE DOCUMENT_FILE.FILE_ID = UNHEX(:FILE_ID) AND DOCUMENT.USER_ID = UNHEX(:USER_ID) ";
			$rows = Database::exec_with_result($sql, 
				array(
					DatabaseParam::str(":FILE_ID", $this->id),
					DatabaseParam::str(":USER_ID", User::get_session_user_id())
				)
			);
			if (count($rows) == 1)
			{
				$this->hash = $rows[0]["SHA1_HASH"];
				$this->name = $rows[0]["NAME"];
				$this->uploaded = $rows[0]["UPLOADED"];
				$this->size = $rows[0]["SIZE"];
				if (file_exists(Storage::get_thumbnail_local_path($this->hash)))
				{
					$this->thumbnail_url = Storage::get_thumbnail_url_path($this->hash);
				}			
				$allowed = TRUE;					
			}				
			return($allowed);
		}

		/*
			delete file metadata
		*/
		private function delete_metadata()
		{
			$sql = " DELETE FROM FILE WHERE ID = UNHEX(:ID) ";
			Database::exec_without_result($sql, array(
					DatabaseParam::str(":ID", $this->id)
				)
			);			
		}

		/*
			move temporal uploaded file to storage path
		*/
		private function move_uploaded_to_storage($tmp_file_path)
		{
			$moved = FALSE;
			if (is_dir(LOCAL_STORAGE_PATH))
			{

				if (file_exists($tmp_file_path))
				{
					$moved = move_uploaded_file($tmp_file_path, Storage::get_storage_path($this->hash));
				}
			}
			return($moved);
		}

		/*
			create file thumbnail
		*/
		private function create_thumbnail()
		{
			$source_path = Storage::get_storage_path($this->hash);
			switch(strtolower(pathinfo($this->name, PATHINFO_EXTENSION)))
			{
				case "jpg":
				case "jpeg":
				case "gif":
				case "png":
				case "bmp":
				case "tiff":
					return(Storage::create_imagick_thumbnail($source_path, $source_path . "-thumb.jpg"));
				break;
				case "pdf":
					return(Storage::create_imagick_thumbnail($source_path, $source_path . "-thumb.jpg", TRUE));
				break;
				default:
					return(FALSE);
				break;
			}
		}

		/*
			add file
		*/
		function add($temporal_path)
		{
			$result = array("success" => FALSE);
			if (file_exists($temporal_path))
			{
				if ($this->id == NULL)
				{
					$this->id = Database::get_uuid();
				}				
				$this->hash = sha1_file($temporal_path);
				$this->size = filesize($temporal_path);
				try
				{
					if ($this->move_uploaded_to_storage($temporal_path))
					{
						$this->add_metadata();
						$this->create_thumbnail();
						if (file_exists(Storage::get_thumbnail_local_path($this->hash)))
						{
							$this->thumbnail_url = Storage::get_thumbnail_url_path($this->hash);
						}								
						$result["file"] = $this;
						$result["success"] = TRUE;
					}
					else
					{
						$result["error"] = "Error moving uploaded file to storage"; 	
					}
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
			}
			else
			{
				$result["error"] = "Upload error (file not found)"; 
			}
			return($result);
		}
	}
?>