<?php
	define('VERSION', 0.2);
	define('DATABASE_HOST', 'localhost');
	define('DATABASE_PORT', 3306);
	define('DATABASE_NAME', 'homedocs');
	define('DATABASE_USERNAME', 'foo');
	define('DATABASE_PASSWORD', 'bar');
	define('PDO_CONNECTION_STRING', 'mysql:host=' . DATABASE_HOST . ';port=' . DATABASE_PORT . ';dbname=' . DATABASE_NAME);

	define('DATABASE_ENCODING', 'utf8_unicode');

	define('DEBUG', 0);

	define('ALLOW_NEW_ACCOUNTS', TRUE);

	define('LOCAL_STORAGE_PATH', '/var/www/homedocs/storage/');
	define('WEB_STORAGE_PATH', '/homedocs/storage/');

	define('ONLY_ALLOWED_MIMES', TRUE);

	define('ALLOWED_MIME_TYPES', 
			serialize(
					array(
						"pdf", 
						"jpg", 
						"jpeg", 
						"bmp", 
						"tiff", 
						"png", 
						"gif", 
						"txt", 
						"doc", 
						"docx", 
						"html", 
						"xml", 
						"rtf", 
						"xls", 
						"csv", 
						"ppt"
					)
			)
	);

	if (DEBUG == 0)
	{
		error_reporting(0);
	}

?>