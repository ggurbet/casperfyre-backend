<?php

class DB {
	public $connect = null;

	function __construct() {
		$this->connect = new mysqli(
			DB_HOST,
			DB_USER,
			DB_PASS,
			DB_NAME
		);

		if($this->connect->connect_error)
			$this->connect = null;
	}

	function __destruct() {
		if($this->connect)
			$this->connect->close();
	}

	/**
	 * Do DB selection
	 *
	 * @param string $query
	 * @return $return array
	 */
	public function do_select($query) {
		$return = null;

		if($this->connect) {
			$result = $this->connect->query($query);

			if($result != null && $result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$return[] = $row;
				}
			}
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
		$flag = false;

		if($this->connect)
			$flag = $this->connect->query($query);

		return $flag;
	}

	/**
	 * Check DB integrity
	 */
	public function check_integrity() {	
		global $helper;

		$query = "SHOW TABLES";
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
					`per_limit` int DEFAULT '0',
					`day_limit` int DEFAULT '0',
					`week_limit` int DEFAULT '0',
					`month_limit` int DEFAULT '0'
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
					`admin_approved` int DEFAULT '0',
					`deny_reason` text,
					PRIMARY KEY (`guid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created user table');
			$created_email = 'thomas+admin@ledgerleap.com';
			$random_password = $helper->generate_hash();
			$random_password_hash = hash('sha256', $random_password);
			$query = "
				INSERT INTO `users` VALUES (
					'5a199618-682d-2006-4c4c-c0cde9e672d5',
					'admin',
					'$created_email',
					1,
					'thomas',
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
					NULL
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
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
			";
			$this->do_query($query);
			elog('DB: Created wallets table');
		}
	}
}
?>