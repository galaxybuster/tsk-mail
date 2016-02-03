<?php
	// This handler marks the supplied database item as complete.
	// These todo tasks will still be stored,
	// but will not show up in the default loaded list.

	// WARNING: A user could update their DOM to change the id,
	// marking other user's tasks as complete.
	// Ensure that the item ID is owned by $_SESSION['user']['id']
	
	require_once("../lib/init.php");
	require_once("../lib/database.class.php");

	$auth = true; // etc etc session user

	$msg = "";

	$r = array(
		"response"=>"",
		"error"=>0
	);

	if ($auth) {
		$db = Database::getInstance();

		$itemID = $_POST['id'];

		$db->query("UPDATE todo_items SET completed=1 WHERE id=?", array($itemID));
		if ($db->error()) {
			$r['response'] = "Error updating todo item.";
			$r['error'] = 1; // General DB error
		} else {
			$r['response'] = "Successfully marked item as complete.";
		}
	} else {
		$r['response'] = "Please sign in.";
		$r['error'] = 4; // Missing auth
	}

	die(json_encode($r));