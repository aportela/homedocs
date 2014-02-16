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
					}
					else
					{
						//$result["error"] = "can not upload this type of file (mime unsupported)";
					}
				}
				if (count($d->files) == $total_files)
				{
					$result = $d->add();
					if ($result["success"] == TRUE)
					{
						$result = array("success" => TRUE, "document" => $d, "a" => 11);
					}
					else
					{
						$result = array("success" => FALSE);
					}
				}
				else
				{
					$result = array("success" => FALSE);
				}
			}

		}
		else
		{
			$result["error"] = "form file not found";
		}
	}
	else
	{
		$result["error"] = "access denied";
	}

	echo json_encode($result);

	ob_end_flush();
?>