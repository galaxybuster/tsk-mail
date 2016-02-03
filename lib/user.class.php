<?php
	require_once(__DIR__ . "/config.class.php");
	require_once(__DIR__ . "/database.class.php");
	require_once(__DIR__ . "/email.class.php");

	class User {

		public $_data = array(
			"id"=>0,
			"email"=>"",
			"join_date"=>""
		);

		function __construct() {
			
		}

		/**
		 * public login($email, $password)
		 * 
		 * Attempts to authenticate the user with the given email/password combo.
		 * Returns true if successfully logged in (session will be stored)
		 */
		public function login($email, $password) {
			$db = Database::getInstance();
			$query = "SELECT * FROM users WHERE (email = ?)";
			$db->query($query, array($email));
			$result = $db->firstResult();
			
			if ($result != null) {	
				// Compare the given password with salt+password from that email record
				$checkpass = hash('sha256', $password . $result['salt']);
				for($round = 0; $round < 65536; $round++)
				{
					$checkpass = hash('sha256', $checkpass . $result['salt']);
				}

				if ($checkpass === $result['password']) {
					// Both hashes match with the given email. Authenticate the user.
					unset($result['salt']);
					unset($result['password']);

					$_SESSION['user'] = $result;

					return true;
				} else {
					// Passwords dont match
					return false;
				}
			} else {
				return false;
			}
		}

		/**
		 * public getUserById($id)
		 *
		 * Loads user data for the given ID
		 * Call after new User();
		 */
		public function getUserById($id) {

		}

		/**
		 * public queueNewUser($email, $password)
		 *
		 * Creates a new user and stores it in the TEMP database, setting
		 * the local object's data. It then sends an email with an activation links.
		 * 
		 * Returns true on success.
		 */
		public function queueNewUser($email, $username, $pw) {
			// Send back a return code to state whether its success/fail
			// eg 1 would be success
			// 2 means "email already registered"
			$db = Database::getInstance();

			$query = "
				INSERT INTO users_confirm (
					email,
					username,
					password,
					salt,
					activation_key
				) VALUES (
					?,
					?,
					?,
					?,
					?
				)
			";

			$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
			// This hashes the password with the salt so it can be stored securely.
			$password = hash('sha256', $pw . $salt);
			// Next we hash the hash value 65536 more times.  The purpose of this is to
			// protect against brute force attacks.  Now an attacker must compute the hash 65537
			// times for each guess they make against a password, whereas if the password
			// were hashed only once the attacker would have been able to make 65537 different 
			// guesses in the same amount of time instead of only one.
			for ($round = 0; $round < 65536; $round++) {
				$password = hash('sha256', $password . $salt);
			}

			// Uncomment to actually register accounts
			$key = md5(time());
			$db->query($query, array($email, $username, $password, $salt, $key));
			$result = $db->firstResult();

			// Send email
			$em = new Email();
			$em->sendEmail($email, "Confirm your account", "This is an email test, please use this key to register: ".$key, true);

			return true;
		}
	}