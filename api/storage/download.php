<?php
	ob_start();

	require_once ("../../include/configuration.php");
	require_once ("../../include/class.User.php");
	require_once ("../../include/class.Storage.php");
	require_once ("../../include/class.File.php");

	session_start();

	if (User::is_logged())
	{
		if (isset($_GET["id"]))
		{
			$f = new File();
			$f->id = $_GET["id"];
			if ($f->get_download_metadata())
			{
				$storage_path = Storage::get_storage_path($f->hash);
				if (file_exists($storage_path))
				{
					header('Content-Description: File Transfer');
			    	header('Content-Type: application/octet-stream');
			    	header('Content-Disposition: attachment; filename=' . basename($f->name));
			    	header('Expires: 0');
			    	header('Cache-Control: must-revalidate');
			    	header('Pragma: public');
			    	header('Content-Length: ' . $f->size);
			    	ob_clean();
			    	flush();
			    	readfile($storage_path);
			    	exit;
			    }
			    else
			    {
			    	header('HTTP/1.0 404 Not found');
			    }				
			}
			else 
			{
		    	header('HTTP/1.0 403 Forbidden');
			}
		}
		else
		{
			header('HTTP/1.0 500 Server Error');
		}
	}
	else
	{
		header('HTTP/1.0 403 Forbidden');
	}

	ob_end_flush();	
?>
