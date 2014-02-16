<?php
	ob_start();

	header('Content-type: application/json');
	
	session_start();

	$result = array("success" => TRUE);

	echo json_encode($result);
	
	ob_end_flush();
?>