<?php
	/*
	 * submit.php
	 *
	 * This script submits a post
	 *
	*/
	require_once("../lib/init.php");
	require_once("../lib/database.class.php");

	// First, we want to make sure the user is authenticated
	$auth = true; //isset($_SESSION['user']);

	$msg = "";

	$r = array(
		"response"=>"",
		"error"=>0
	);

	if ($auth) {
		$db = Database::getInstance();
		
		$required = array('todo', 'duedate');
		// Loop over field names, make sure each one exists and is not empty
		$error = false;
		foreach($required as $field) {
			if (empty($_POST[$field])) {
				$error = true;
			}
		}

		if (!$error) {
			$dd = new DateTime('+'.$_POST['duedate'].' day');
			$due = $dd->format('Y-m-d');

			$db->query("INSERT INTO todo_items(text, duedate, date_added) VALUES (?, ?, now())", array($_POST['todo'], $due));
			if ($db->error()) {
				$r['response'] = "Error creating todo item.";
				$r['error'] = 1; // General database error.
			} else {
				$r['response'] = "Successfully created new item.";
				$r['error'] = 0; // Missing required field
				die(json_encode($r));
			}
			die(json_encode($r));
		} else {
			$r['response'] = "Missing required field.";
			$r['error'] = 3; // Missing required field
			die(json_encode($r));
		}
	} else {
		$r['response'] = "Please sign in.";
		$r['error'] = 4; // Missing AUTH
		die(json_encode($r));
	}
