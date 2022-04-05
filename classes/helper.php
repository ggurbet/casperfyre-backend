<?php

class Helper {
	private $cipher = "AES-256-CBC";

	function __construct() {
		//
	}

	function __destruct() {
		//
	}

	public function generate_guid() {
		$b1 = bin2hex(random_bytes(4));
		$b2 = bin2hex(random_bytes(2));
		$b3 = bin2hex(random_bytes(2));
		$b4 = "4c4c";
		$b5 = bin2hex(random_bytes(6));
		return $b1.'-'.$b2.'-'.$b3.'-'.$b4.'-'.$b5;
	}

	public function generate_apikey() {
		$ret = bin2hex(openssl_random_pseudo_bytes(32));
		return $ret;
	}

	public function generate_session_token() {
		$ret = bin2hex(openssl_random_pseudo_bytes(128));
		return $ret;
	}

	public function generate_hash($length = 10) {
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

	public function get_datetime($future = 0) {
		return(date('Y-m-d H:i:s', time() + $future));
	}

	public function schedule_email(
		$template_id,
		$recipient,
		$subject,
		$body,
		$link = ''
	) {
		global $db;

		switch ($template_id) {
			case 'register': $template = file_get_contents('../templates/register.html'); break;
			default: $template = file_get_contents('../templates/register.html'); break;
		}

		$template = str_replace('[SUBJECT]', $subject, $template);
		$template = str_replace('[BODY]', $body, $template);
		$template = str_replace('[LINK]', $link, $template);
		$created_at = $this->get_datetime();

		$query = "
			INSERT INTO schedule (
				template_id,
				subject,
				body,
				email,
				created_at
			) VALUES (
				'$template_id',
				'$subject',
				'$template',
				'$recipient',
				'$created_at'
			)
		";
		return $db->do_query($query);
	}

	public function generate_wallet() {
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

	public function b_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	public function b_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

	public function aes_encrypt($data) {
		$iv = openssl_random_pseudo_bytes(16);

		$ciphertext = openssl_encrypt(
			$data,
			$this->cipher,
			hex2bin(MASTER_KEY),
			0,
			$iv
		);

		$ciphertext = $this->b_encode($this->b_encode($ciphertext).'::'.bin2hex($iv));

		return $ciphertext;
	}

	public function aes_decrypt($data) {
		$decoded = $this->b_decode($data);
		$split = explode('::', $decoded);
		$iv = $split[1] ?? '';

		if(strlen($iv) % 2 == 0 && ctype_xdigit($iv)) {
			$iv = hex2bin($iv);
		} else {
			return $this->b_decode($data);
		}

		$data = $this->b_decode($split[0]);

		$decrypted = openssl_decrypt(
			$data,
			$this->cipher,
			hex2bin(MASTER_KEY),
			OPENSSL_ZERO_PADDING,
			$iv
		);

		return rtrim($decrypted, "\0..\32");
	}

	public function get_real_ip() {
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if($ip == '::1')
			return '127.0.0.1';

		if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
			return '127.0.0.1';

		return $ip;
	}

	public function correct_validator_id_format($vid) {
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

	public function in_CIDR_range($ip, $iprange) {
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