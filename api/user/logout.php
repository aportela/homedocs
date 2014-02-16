<?php
	ob_start();

	header('Content-type: application/json');
	
	session_start();
	$_SESSION = array();
	if (ini_get("session.use_cookies")) {
	    $params = session_get_cookie_params();
	    setcookie(session_name(), '', time() - 42000,
	        $params["path"], $params["domain"],
	        $params["secure"], $params["httponly"]
	    );
	}
	session_destroy();

	$result = array("success" => TRUE);

	echo json_encode($result);
	
	ob_end_flush();
?>