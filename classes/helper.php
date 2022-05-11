<?php
/**
 * Helper class contains vital methods for the functionality of the portal.
 * Made to be static for PHPUnit tests.
 *
 */


class Helper {
	private const cipher = "AES-256-CBC";

	function __construct() {
		//
	}

	function __destruct() {
		//
	}

	/**
	 *
	 * Generate GUID
	 *
	 * 4th set of bytes is always "4c4c", or LL (LedgerLeap)
	 *
	 * @return string
	 *
	 */
	public static function generate_guid() {
		$b1 = bin2hex(random_bytes(4));
		$b2 = bin2hex(random_bytes(2));
		$b3 = bin2hex(random_bytes(2));
		$b4 = "4c4c";
		$b5 = bin2hex(random_bytes(6));
		return $b1.'-'.$b2.'-'.$b3.'-'.$b4.'-'.$b5;
	}

	/**
	 *
	 * Generate Api key
	 *
	 * 32 bytes, crypto safe
	 *
	 * @return string
	 *
	 */
	public static function generate_apikey() {
		$ret = bin2hex(openssl_random_pseudo_bytes(32));
		return $ret;
	}

	/**
	 *
	 * Generate Session token
	 *
	 * 128 bytes, crypto safe
	 *
	 * @return string
	 *
	 */
	public static function generate_session_token() {
		$ret = bin2hex(openssl_random_pseudo_bytes(128));
		return $ret;
	}

	/**
	 *
	 * Generate User friedly hash. For MFA, confirmation codes, etc
	 *
	 * Default 10 char length
	 *
	 * @param  int    $length
	 * @return string
	 *
	 */
	public static function generate_hash($length = 10) {
		$seed = str_split(
			'ABCDEFGHJKLMNPQRSTUVWXYZ'.
			'2345678923456789'
		);
		// dont use 0, 1, o, O, l, I
		shuffle($seed);
		$hash = '';

		foreach(array_rand($seed, $length) as $k) {
			$hash .= $seed[$k];
		}

		return $hash;
	}

	/**
	 *
	 * Get standard format date/time
	 *
	 * Behaves similar to epoch timestamp when compared with <=> operators
	 *
	 * @param  int    $future  Can be positive for future timestamp, negative for past timestamp
	 * @return string
	 *
	 */
	public static function get_datetime($future = 0) {
		return(date('Y-m-d H:i:s', time() + $future));
	}

	/**
	 *
	 * Schedule an email
	 *
	 * Instead of sending emails immediately, this feeds a cron job that scheduled sends mail every 60 seconds
	 *
	 * @param  string  $template_id
	 * @param  string  $recipient
	 * @param  string  $subject
	 * @param  string  $body
	 * @param  string  $link
	 * @return bool    $return   Indicating if queue was successful
	 *
	 */
	public static function schedule_email(
		$template_id,
		$recipient,
		$subject,
		$body,
		$link = ''
	) {
		global $db;

		/* Check exploitable endpoints for similar mail requests */
		$partial_email = explode('+', $recipient);
		$partial_email1 = $partial_email[0];
		$partial_email2 = $partial_email[1] ?? '';
		$partial_email2 = explode('@', $partial_email2);
		$partial_email2 = $partial_email2[1] ?? '';
		$query = "
			SELECT *
			FROM schedule
			WHERE template_id = '$template_id'
			AND complete = 0
			AND (
				email = '$recipient' OR (
					email LIKE '%$partial_email1%' AND
					email LIKE '%$partial_email2%'
				)
			)
			ORDER BY id DESC
		";
		$similarity_check = $db->do_select($query) ?? array();

		if(count($similarity_check) > 2) {
			for($i = 1; $i < count($similarity_check); $i++) {
				$sid = $similarity_check[$i]['id'] ?? 0;
				$query = "
					UPDATE schedule
					SET complete = 1
					WHERE id = $sid
				";
				$db->do_query($query);
			}
		}

		/* Create schedule item */
		$created_at = self::get_datetime();

		$query = "
			INSERT INTO schedule (
				template_id,
				subject,
				body,
				link,
				email,
				created_at
			) VALUES (
				'$template_id',
				'$subject',
				'$body',
				'$link',
				'$recipient',
				'$created_at'
			)
		";
		return $db->do_query($query);
	}

	/**
	 *
	 * Send MFA code
	 *
	 * For MFA authenticated functions
	 *
	 * @param  string  $guid
	 * @return bool
	 *
	 */
	public static function send_mfa($guid) {
		global $db;

		$query = "
			SELECT email, first_name
			FROM users
			WHERE guid = '$guid'
		";

		$selection = $db->do_select($query);
		$email = $selection[0]['email'] ?? '';
		$first_name = $selection[0]['first_name'] ?? '';
		$code = self::generate_hash(6);
		$created_at = self::get_datetime();

		if($selection) {
			$query = "
				DELETE FROM twofa
				WHERE guid = '$guid'
			";
			$db->do_query($query);

			$query = "
				INSERT INTO twofa (
					guid,
					created_at,
					code
				) VALUES (
					'$guid',
					'$created_at',
					'$code'
				)
			";
			$db->do_query($query);

			self::schedule_email(
				'twofa',
				$email,
				APP_NAME.' - Multi Factor Authentication',
				'Hello, '.$first_name.'. Please find your MFA code for '.APP_NAME.'. This code expires in 5 minutes.',
				$code
			);

			return true;
		}
		return false;
	}

	/**
	 *
	 * Verfiy MFA code
	 *
	 * Once successfully verified, MFA allowance lasts 5 minutes
	 *
	 * @param  string  $guid
	 * @param  string  $mfa_code
	 * @return string
	 *
	 */
	public static function verify_mfa($guid, $mfa_code) {
		global $db;

		if(strlen($mfa_code) > 8) {
			return 'incorrect';
		}

		// check mfa type first
		$query = "
			SELECT totp
			FROM users
			WHERE guid = '$guid'
		";
		$mfa_type = $db->do_select($query);
		$mfa_type = (int)($mfa_type[0]['totp'] ?? 0);

		// totp type mfa
		if($mfa_type == 1) {
			$verified = Totp::check_code($guid, $mfa_code);

			if($verified) {
				self::create_mfa_allowance($guid);
				return 'success';
			}

			return 'incorrect';
		}

		// email type mfa
		$query = "
			SELECT code, created_at
			FROM twofa
			WHERE guid = '$guid'
			AND code = '$mfa_code'
		";

		$selection = $db->do_select($query);
		$fetched_code = $selection[0]['code'] ?? '';
		$created_at = $selection[0]['created_at'] ?? 0;
		$expire_time = self::get_datetime(-300); // 5 minutes ago

		if($selection) {
			if($mfa_code == $fetched_code) {
				$query = "
					DELETE FROM twofa
					WHERE guid = '$guid'
				";
				$db->do_query($query);

				if($expire_time < $created_at) {
					self::create_mfa_allowance($guid);
					return 'success';
				} else {
					return 'expired';
				}
			}
		}
		return 'incorrect';
	}

	/**
	 *
	 * Create an MFA Allowance
	 *
	 * Happens when MFA is successfully verified.
	 * Lasts 5 minutes.
	 * Purposed for user ability to submit MFA and then submit authenticated request sequentially. 
	 *
	 * @param  string  $guid
	 * @return string
	 *
	 */
	public static function create_mfa_allowance($guid) {
		global $db;

		$expires_at = self::get_datetime(300); // 5 minutes from now

		$query = "
			DELETE FROM mfa_allowance
			WHERE guid = '$guid'
		";
		$db->do_query($query);

		$query = "
			INSERT INTO mfa_allowance (
				guid,
				expires_at
			) VALUES (
				'$guid',
				'$expires_at'
			)
		";
		$db->do_query($query);
	}

	/**
	 *
	 * Consume MFA Allowance
	 *
	 * Once successfully consumed, MFA allowance is purged.
	 * If allowance is attempted to be consumed and found to be expired, it purges record and returns false.
	 *
	 * @param  string  $guid
	 * @return bool
	 *
	 */
	public static function consume_mfa_allowance($guid) {
		global $db;

		$query = "
			SELECT expires_at
			FROM mfa_allowance
			WHERE guid = '$guid'
		";
		$selection = $db->do_select($query);

		if(!$selection) {
			return false;
		}

		$expires_at = $selection[0]['expires_at'] ?? '';
		$now_time = self::get_datetime();

		if($now_time > $expires_at) {
			$return = false;
		} else {
			$return = true;
		}

		$query = "
			DELETE FROM mfa_allowance
			WHERE guid = '$guid'
		";
		$db->do_query($query);

		return $return;
	}

	/**
	 *
	 * Search for Apikey ID by an actual API key
	 *
	 * Returns 0 if no record found
	 *
	 * @param  string  $api_key
	 * @return int     $id
	 *
	 */
	public static function get_apikey_id_by_apikey($api_key) {
		global $db;

		$query = "
			SELECT id
			FROM api_keys
			WHERE api_key = '$api_key'
		";
		$id = $db->do_select($query);
		$id = (int)($id[0]['id'] ?? 0);
		return $id;
	}

	/**
	 *
	 * Generate CasperLabs standard ED25519 wallet
	 *
	 * Using Libsodium
	 *
	 * @return array
	 *
	 */
	public static function generate_wallet() {
		$keypair = \Sodium\crypto_sign_keypair('ed25519');
		$secret_bytes = \Sodium\crypto_sign_secretkey($keypair);
		$public_bytes = \Sodium\crypto_sign_publickey($keypair);
		$public = bin2hex($public_bytes);
		$secret = bin2hex($secret_bytes);
		$secret = substr($secret, 0, 64);

		return array(
			"public" => '01'.$public,
			"secret" => $secret
		);
	}

	/**
	 *
	 * Get wallet balance
	 *
	 * Using PHP Casper Client SDK. Returns 0 if no record found
	 *
	 * @param  string  $validator_id
	 * @return int     $balance
	 *
	 */
	public static function get_wallet_balance($validator_id) {
		global $casper_client;

		if(!self::correct_validator_id_format($validator_id)) {
			return 0;
		}

		$balance = 0;

		try {
			$recipient_public_key = Casper\Serializer\CLPublicKeySerializer::fromHex($validator_id);
			$latest_block = $casper_client->getLatestBlock();
			$block_hash = $latest_block->getHash();
			$state_root_hash = $casper_client->getStateRootHash($block_hash);
			$account = $casper_client->getAccount($block_hash, $recipient_public_key);
			$balance_object = $casper_client->getAccountBalance($state_root_hash, $account->getMainPurse());
			$balance_motes =  gmp_intval($balance_object);
			$balance = (int)($balance_motes / 1000000000);
		} catch (Exception $e) {
			elog('Failed to get balance of '.$validator_id);
			// elog($e);
			return $balance;
		}

		return $balance;
	}

	public static function get_user_secret_key($wallet_id) {
		global $db;

		$query = "
			SELECT secret_key_enc
			FROM wallets
			WHERE id = $wallet_id
		";
		$selection = $db->do_select($query);
		$secret_key_enc = $selection[0]['secret_key_enc'] ?? '';
		$secret_key = '';

		if($secret_key_enc) {
			$secret_key = self::aes_decrypt($secret_key_enc);
		}

		return $secret_key;
	}

	/**
	 *
	 * Base64 encode data quickly
	 *
	 * @param  string  $data
	 * @return string
	 *
	 */
	public static function b_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 *
	 * Base64 decode data quickly
	 *
	 * @param  string  $data
	 * @return string
	 *
	 */
	public static function b_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

	/**
	 *
	 * Encrypt data quickly. Crypto safe
	 *
	 * @param  string  $data
	 * @return string  $ciphertext
	 *
	 */
	public static function aes_encrypt($data) {
		$iv = openssl_random_pseudo_bytes(16);

		$ciphertext = openssl_encrypt(
			$data,
			self::cipher,
			hex2bin(MASTER_KEY),
			0,
			$iv
		);

		$ciphertext = self::b_encode(self::b_encode($ciphertext).'::'.bin2hex($iv));

		return $ciphertext;
	}

	/**
	 *
	 * Decrypt data quickly. Crypto safe
	 *
	 * @param  string  $data
	 * @return string
	 *
	 */
	public static function aes_decrypt($data) {
		$decoded = self::b_decode($data);
		$split = explode('::', $decoded);
		$iv = $split[1] ?? '';

		if(strlen($iv) % 2 == 0 && ctype_xdigit($iv)) {
			$iv = hex2bin($iv);
		} else {
			return self::b_decode($data);
		}

		$data = self::b_decode($split[0]);

		$decrypted = openssl_decrypt(
			$data,
			self::cipher,
			hex2bin(MASTER_KEY),
			OPENSSL_ZERO_PADDING,
			$iv
		);

		return rtrim($decrypted, "\0..\32");
	}

	/**
	 *
	 * Get Dir Contents
	 *
	 * Recursively get all files/folders in the specied directory $dir.
	 * Returns list of items relative to base $__dir supplied to method, meant as __DIR__.
	 *
	 * @param  string  $__dir
	 * @param  string  $dir
	 * @return array   $result
	 *
	 */
	public static function get_dir_contents(
		$__dir, 
		$dir, 
		&$result = array()
	) {
		$files = scandir($dir);

		foreach($files as $key => $val) {
			$path = realpath($dir.DIRECTORY_SEPARATOR.$val);
			$path = str_replace($__dir.'/' , '', $path);

			if(!is_dir($path)) {
				$result[] = $path;
			} elseif(
				$val != '.' &&
				$val != '..'
			) {
				self::get_dir_contents($__dir, $path, $result);
				$result[] = $path;
			}
		}

		return $result;
	}

	/**
	 *
	 * Get real IP address
	 *
	 * @return string
	 *
	 */
	public static function get_real_ip() {
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		}

		if($ip == '::1')
			return '127.0.0.1';

		if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
			return '127.0.0.1';

		return $ip;
	}

	/**
	 *
	 * Verify validity of a CasperLabs public key / Validator ID
	 *
	 * @param  string  $vid
	 * @return bool
	 *
	 */
	public static function correct_validator_id_format($vid) {
		if(gettype($vid) != 'string') {
			return false;
		}

		if(!preg_match('/^[0-9a-fA-F]+$/', $vid)) {
			return false;
		}

		$firstbyte = substr($vid, 0, 2);

		if($firstbyte === '01') {
			if(strlen($vid) === 66) {
				return true;
			}
		} elseif($firstbyte === '02') {
			if(strlen($vid) === 68) {
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 * Verify provided IP is in a provided CIDR range
	 *
	 * @param  string  $ip
	 * @param  string  $iprange
	 * @return bool
	 *
	 */
	public static function in_CIDR_range($ip, $iprange) {
		if(!$iprange || $iprange == '') return true;

		if(strpos($iprange, '/') === false) {
			if(inet_pton($ip) == inet_pton($iprange)) return true;
		} else {
			list($subnet, $bits) = explode('/', $iprange);
			// Convert subnet to binary string of $bits length
			$subnet = unpack('H*', inet_pton($subnet)); // Subnet in Hex
			foreach($subnet as $i => $h) $subnet[$i] = base_convert($h, 16, 2); // Array of Binary
			$subnet = substr(implode('', $subnet), 0, $bits); // Subnet in Binary, only network bits
			// Convert remote IP to binary string of $bits length
			$ip = unpack('H*', inet_pton($ip)); // IP in Hex
			foreach($ip as $i => $h) $ip[$i] = base_convert($h, 16, 2); // Array of Binary
			$ip = substr(implode('', $ip), 0, $bits); // IP in Binary, only network bits
			// Check network bits match
			if($subnet == $ip) return true;
		}
		return false;
	}
}