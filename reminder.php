<?php
	// Send email to remind of tasks
	// daily cron script

	include "lib/rain.tpl.class.php";
	raintpl::configure("base_url", null );
	raintpl::configure("tpl_dir", "tpl/emails/" );
	raintpl::configure("cache_dir", "temp/" );

	$tpl = new RainTPL;

	require_once("lib/init.php");
	require_once("lib/database.class.php");
	require_once("lib/email.class.php");
	
	// get names, duedates of all tasks not completed
	$db = Database::getInstance();
	$db->query("SELECT * FROM todo_items WHERE completed=0 ORDER BY duedate ASC");
	$result = $db->result();

	$taskList = array();
	foreach ($result as $r) {

		// check if its today if so make it that [this is fricky]
		$d = date('d M Y', strtotime($r['duedate']));
		$red = "";
		if (date('d M Y') === date('d M Y', strtotime($r['duedate']))) {
			$d = 'today';
			$red = "red";
		}
		$row = '<tr><td class="td-item" data-id="'.$r['id'].'">'.$r['text'].'</td><td class="td-date '.$red.'">'.$d.'</td></tr>'; // i hate to mix but raintpl is garbage
		array_push($taskList, $row);
	}

	$tpl->assign('taskList', $taskList);
	$body = $tpl->draw('reminder', true);

	$email = $GLOBALS['config']['email'];
	// Send email
	$em = new Email();
	$em->sendEmail($email, "Update of Tasks", $body, true);