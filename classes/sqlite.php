<?php
/**
 * Sqlite database class. Purposed for unit test workflow
 */
class DB extends SQLite3 {
	function __construct() {
		$this->open(BASE_DIR.'/database/database.sqlite');
	}

	function __destruct() {
		$this->close();
	}

	/**
	 * Do DB selection
	 *
	 * @param string $query
	 * @return array $return
	 */
	public function do_select($query) {
		$return = null;
		$ret = $this->query($query);

		while($row = $ret->fetchArray(SQLITE3_ASSOC)) {
			$return[] = $row;
		}

		return $return;
	}

	/**
	 * Do DB query
	 *
	 * @param string $query
	 * @return bool
	 */
	public function do_query($query) {
		$flag = $this->exec($query);
		return $flag;
	}

	/**
	 * Check DB integrity
	 */
	public function check_integrity() {
		global $helper;

		$query = "TABLES";
		$tables = $this->do_select($query);
		$all_tables = array();

		if($tables) {
			foreach ($tables as $table) {
				$all_tables[] = $table['Tables_in_'.DB_NAME];
			}
		}

		if(!in_array('api_keys', $all_tables)) {
			$query = "
				CREATE TABLE `api_keys` (
					`id` int NOT NULL AUTO_INCREMENT,
					`guid` varchar(36) NOT NULL,
					`api_key` varchar(64) NOT NULL,
					`active` int DEFAULT '1',
					`created_at` timestamp NULL DEFAULT NULL,
					`total_calls` int DEFAULT '0',
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created api_keys table');
		}

		if(!in_array('ips', $all_tables)) {
			$query = "
				CREATE TABLE `ips` (
					`id` int NOT NULL AUTO_INCREMENT,
					`guid` varchar(36) DEFAULT NULL,
					`ip` varchar(64) DEFAULT NULL,
					`active` int DEFAULT '1',
					`created_at` timestamp NULL DEFAULT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created ips table');
		}

		if(!in_array('limits', $all_tables)) {
			$query = "
				CREATE TABLE `limits` (
					`guid` varchar(36) DEFAULT NULL,
					`per_limit` int DEFAULT '500',
					`day_limit` int DEFAULT '1000',
					`week_limit` int DEFAULT '5000',
					`month_limit` int DEFAULT '10000'
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created limits table');
		}

		if(!in_array('orders', $all_tables)) {
			$query = "
				CREATE TABLE `orders` (
					`id` int NOT NULL AUTO_INCREMENT,
					`guid` varchar(36) NOT NULL,
					`created_at` timestamp NULL DEFAULT NULL,
					`sent_at` timestamp NULL DEFAULT NULL,
					`ip` varchar(64) DEFAULT NULL,
					`return_code` int DEFAULT NULL,
					`fulfilled` int DEFAULT '0',
					`address` varchar(70) DEFAULT NULL,
					`amount` int DEFAULT NULL,
					`deploy_hash` varchar(66) DEFAULT NULL,
					`success` int DEFAULT '0',
					`api_key_id_used` int DEFAULT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created orders table');
		}

		if(!in_array('schedule', $all_tables)) {
			$query = "
				CREATE TABLE `schedule` (
					`id` int NOT NULL AUTO_INCREMENT,
					`template_id` varchar(100) DEFAULT NULL,
					`subject` varchar(255) DEFAULT '',
					`body` text,
					`link` text,
					`email` varchar(255) DEFAULT NULL,
					`created_at` timestamp NULL DEFAULT NULL,
					`sent_at` timestamp NULL DEFAULT NULL,
					`complete` int DEFAULT '0',
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created schedule table');
		}

		if(!in_array('sessions', $all_tables)) {
			$query = "
				CREATE TABLE `sessions` (
					`id` int NOT NULL AUTO_INCREMENT,
					`guid` varchar(36) NOT NULL,
					`bearer` text,
					`created_at` timestamp NULL DEFAULT NULL,
					`expires_at` timestamp NULL DEFAULT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created sessions table');
		}

		if(!in_array('settings', $all_tables)) {
			$query = "
				CREATE TABLE `settings` (
					`name` varchar(64) DEFAULT NULL,
					`value` text
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created settings table');
		}

		if(!in_array('users', $all_tables)) {
			$query = "
				CREATE TABLE `users` (
					`guid` varchar(36) NOT NULL,
					`role` varchar(16) DEFAULT 'user',
					`email` varchar(255) DEFAULT NULL,
					`verified` int DEFAULT '0',
					`first_name` varchar(255) DEFAULT NULL,
					`last_name` varchar(255) DEFAULT NULL,
					`password` varchar(255) DEFAULT NULL,
					`api_key_active` int DEFAULT '1',
					`created_at` timestamp NULL DEFAULT NULL,
					`confirmation_code` varchar(64) DEFAULT NULL,
					`last_ip` varchar(64) DEFAULT NULL,
					`company` varchar(255) DEFAULT NULL,
					`description` text,
					`cspr_expectation` int DEFAULT '0',
					`cspr_actual` int DEFAULT '0',
					`admin_approved` int DEFAULT '0',
					`deny_reason` text,
					`twofa` int DEFAULT '0',
					`totp` int DEFAULT '0',
					PRIMARY KEY (`guid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created user table');
			$created_email = getenv('ADMIN_EMAIL');
			$random_password = $helper->generate_hash();
			$random_password_hash = hash('sha256', $random_password);
			$query = "
				INSERT INTO `users` VALUES (
					'5a199618-682d-2006-4c4c-c0cde9e672d5',
					'admin',
					'$created_email',
					1,
					'admin',
					'admin',
					'$random_password_hash',
					1,
					NULL,
					NULL,
					NULL,
					'ledgerleap llc',
					' -- dev: no description -- ',
					0,
					1,
					NULL,
					0
				)
			";
			$this->do_query($query);
			elog('Created admin');
			elog('Email: '.$created_email);
			elog('Password: '.$random_password);
		}

		if(!in_array('wallets', $all_tables)) {
			$query = "
				CREATE TABLE `wallets` (
					`id` int NOT NULL AUTO_INCREMENT,
					`guid` varchar(36) NOT NULL,
					`address` varchar(70) DEFAULT NULL,
					`secret_key_enc` varchar(255) DEFAULT NULL,
					`active` int DEFAULT '1',
					`created_at` timestamp NULL DEFAULT NULL,
					`inactive_at` timestamp NULL DEFAULT NULL,
					`balance` int DEFAULT '0',
					`last_balance_check` timestamp NULL DEFAULT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created wallets table');
		}

		if(!in_array('twofa', $all_tables)) {
			$query = "
				CREATE TABLE `twofa` (
					`id` int NOT NULL AUTO_INCREMENT,
					`guid` varchar(36) NOT NULL,
					`created_at` timestamp NULL DEFAULT NULL,
					`code` varchar(12) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created twofa table');
		}

		if(!in_array('mfa_allowance', $all_tables)) {
			$query = "
				CREATE TABLE `mfa_allowance` (
					`guid` varchar(36) NOT NULL,
					`expires_at` timestamp NULL DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created mfa_allowance table');
		}

		if(!in_array('throttle', $all_tables)) {
			$query = "
				CREATE TABLE `throttle` (
					`ip` varchar(64) DEFAULT NULL,
					`uri` varchar(64) DEFAULT NULL,
					`hit` float DEFAULT NULL,
					`last_request` int DEFAULT '0'
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created throttle table');
		}

		if(!in_array('password_resets', $all_tables)) {
			$query = "
				CREATE TABLE `password_resets` (
					`guid` varchar(36) NOT NULL,
					`code` varchar(12) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created password_resets table');
		}

		if(!in_array('email_changes', $all_tables)) {
			$query = "
				CREATE TABLE `email_changes` (
					`guid` varchar(36) NOT NULL,
					`new_email` varchar(255) DEFAULT NULL,
					`code` varchar(12) NOT NULL,
					`success` int DEFAULT '0',
					`dead` int DEFAULT '0',
					`created_at` timestamp NULL DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created email_changes table');
		}

		if(!in_array('totp', $all_tables)) {
			$query = "
				CREATE TABLE `totp` (
					`guid` varchar(36) NOT NULL,
					`secret` text,
					`created_at` timestamp NULL DEFAULT NULL,
					`active` int DEFAULT '1'
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created totp table');
		}

		if(!in_array('totp_logins', $all_tables)) {
			$query = "
				CREATE TABLE `totp_logins` (
					`guid` varchar(36) NOT NULL,
					`expires_at` timestamp NULL DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created totp_logins table');
		}
	}
}
?>