<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>HomeDocs installer</title>
        <link rel="author" href="humans.txt">
    	<link href="templates/bootstrap-3.1.0/css/bootstrap.min.css" rel="stylesheet">
    	<style>
    		div#main
    		{
				margin: 20px auto;
				max-width: 640px;
				padding: 10px 10px 50px;
				position: relative;
				background-color: #F0F0F0;
				border: 1px solid #BFBFBF;
				border-radius: 3px;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
				padding: 20px;
			}
    	</style>
    </head>
    <body>
    	<div id="main">
    	<h1>HomeDocs installer</h1>
<?php
	// v5.5.0 required because native password hashing functions
	if (PHP_VERSION >= 5.5)
	{
		$required_extensions = array("PDO", "pdo_mysql", "imagick");
		$loaded_extensions = get_loaded_extensions();
		$not_found_extensions = array();
		for ($i = 0; $i < 3; $i++)
		{
			if (! in_array($required_extensions[$i], $loaded_extensions))
			{
				$not_found_extensions[] = $required_extensions[$i];
			}
		}
		if (count($not_found_extensions) == 0)
		{
		
			require_once ("include/configuration.php");
			if (isset($_GET["confirmation"]))
			{
				$errors = FALSE;
				require_once ("include/class.DatabaseHelper.php");
				require_once ("include/class.Storage.php");
				try
				{
					Storage::create_directories();
					echo '<div class="alert alert-success alert-dismissable">
			      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			      			<strong>Storage OK: </strong> storage paths created successfully
			    		</div>';
				}
				catch (Exception $e)
				{
					echo '<div class="alert alert-danger alert-dismissable">
			      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		      			<strong>Storage Error: </strong> ' . $e->getMessage() . ' (check permissions)
			    		</div>';
			    	$errors = TRUE;
				}
				try
				{
					Database::exec_without_result(file_get_contents("homedocs.sql"));
					echo '<div class="alert alert-success alert-dismissable">
			      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			      			<strong>SQL OK: </strong> database created successfully
			    		</div>';
				}
				catch (PDOException $e)
				{
					echo '<div class="alert alert-danger alert-dismissable">
			      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		      			<strong>SQL Error: </strong> ' . $e->getMessage() . '
			    		</div>';
			    		$errors = TRUE;
				}		
				catch (Exception $e)
				{
					echo '<div class="alert alert-danger alert-dismissable">
			      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		      			<strong>General Error: </strong> ' . $e->getMessage() . '
			    		</div>';
			    		$errors = TRUE;
				}		
				if (! $errors)
				{
					echo '<div class="alert alert-info alert-dismissable">
			      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			      			<p><strong>WARNING: </strong> delete (not needed anymore) this installer file:</p><p><strong>' . __FILE__ . '</strong></p>
			    		</div><form method="get" action="index.php">
			    		<div class="form-group">
						<button type="submit" class="btn btn-primary">create account & login into HomeDocs</button>
						</div>
			    		</form>';
			    }
			    else
			    {
					echo '<div class="alert alert-info alert-dismissable">
			      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			      			<p><strong>WARNING: </strong> errors found, <a href="' , basename(__FILE__) , '">launch installer again</a></p>
			    		</div><form method="get" action="index.php">
			    		<div class="form-group">
						<button type="submit" class="btn btn-primary">ignore errors & login into HomeDocs</button>
						</div>
			    		</form>';			    	
			    }
			}
			else
			{
				echo '
				<h2>Installation values (configuration.php):</h2>
				<h3>Storage path</h3>
				<ul>
					<li>' . LOCAL_STORAGE_PATH . '</li>
				</ul>
				<h3>Database settings</h3>
				<ul>
					<li>Host: ' . DATABASE_HOST . '</li>
					<li>Port: ' . DATABASE_PORT . '</li>
					<li>Username: ' . DATABASE_USERNAME . '</li>
					<li>Password: ' . DATABASE_PASSWORD . '</li>
					<li>Database name: ' . DATABASE_NAME . ' (<strong>WARNING - install script will not create database, <a href="http://dev.mysql.com/doc/refman/5.0/es/create-database.html">create before run</a></strong>)</li>
				</ul>
				<form method="get" role="form" action="install.php">
					<input type="hidden" name="confirmation" value="1" />
					<div class="form-group">
						<button type="submit" class="btn btn-primary">accept & install</button>
					</div>
				</form>
				';
			}
		}
		else
		{
			echo '<div class="alert alert-danger alert-dismissable">
      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      			<strong>Error</strong> the following required php modules not found:
      			' , implode(", ", $not_found_extensions) , '

    		</div>';
		}
	}
	else
	{
		echo '<div class="alert alert-danger alert-dismissable">
      			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      			<strong>Error</strong> required PHP version >= 5.5
    		</div>';
	}
?>        
    	</div>
    <script src="templates/jquery-2.1.0.min.js"></script>
    <script src="templates/bootstrap-3.1.0/js/bootstrap.min.js"></script>
    </body>
</html>
