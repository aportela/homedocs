<?php
	ob_start();

	header('Content-type: application/json');

	require_once ("../../include/configuration.php");
	require_once ("../../include/class.User.php");
	require_once ("../../include/class.File.php");
	require_once ("../../include/class.Document.php");

	session_start();

	$result = array("success" => FALSE);

	if (User::is_logged())
	{
		if ($_FILES != NULL && isset($_FILES['userfile']))
		{
			$d = new Document();
			$d->tags = array();
			$d->files = array();
			$total_files = count($_FILES['userfile']['name']);
			if ($total_files > 0)
			{
				$d->title = $total_files > 1 ? implode(", ", $_FILES['userfile']['name']) : $_FILES['userfile']['name'][0];
				for ($i = 0; $i < $total_files; $i++)
				{
					$f = new File();
					$f->name = $_FILES['userfile']['name'][$i];
					$allowed = FALSE;
					if (defined('ONLY_ALLOWED_MIMES'))
					{
						$allowed = ONLY_ALLOWED_MIMES ? $f->allowed_mime() : TRUE;
					}
					else
					{
						$allowed = TRUE;
					}
					if ($allowed)
					{
						$result = $f->add($_FILES['userfile']['tmp_name'][$i]);
						if ($result["success"] == TRUE)
						{
							$d->files[] = clone($result["file"]);
						}
						else
						{
							// exit on errors
							$i = $total_files;
						}
					}
					else
					{
						$result = array("success" => FALSE, "error" => "invalid file mime type");
						// exit on errors
						$i = $total_files;
					}
				}
				if (count($d->files) == $total_files)
				{
					$result = $d->add();
					if ($result["success"] == TRUE)
					{
						$result = array("success" => TRUE, "document" => $d);
					}
					else
					{
						$result = array("success" => FALSE, "error" => "error creating document");
					}
				}
			}

		}
		else
		{
			$result = array("success" => FALSE, "error" => "form file not found");
		}
	}
	else
	{
		$result = array("success" => FALSE, "error" => "access denied");
	}

	if ($result["success"] == FALSE)
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	}
	
	echo json_encode($result);

	ob_end_flush();
?>