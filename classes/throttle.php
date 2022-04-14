<?php

class Throttle {
	function __construct($real_ip) {
		global $db;

		$this->now = (int)time();
		$this->ip = $real_ip;
		$this->uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		$this->endpoints = array(
			'/user/confirm-registration' => 10,
			'/user/create-apikey' => 10,
			'/user/create-ip' => 50,
			'/user/create-wallet' => 20,
			'/user/forgot-password' => 10,
			'/user/get-apikey' => 100,
			'/user/get-apikeys' => 100,
			'/user/get-ips' => 100,
			'/user/get-wallet' => 100,
			'/user/get-wallets' => 100,
			'/user/history' => 100,
			'/user/login' => 10,
			'/user/logout' => 100,
			'/user/me' => 200,
			'/user/name-by-email' => 20,
			'/user/register' => 5,
			'/user/resend-code' => 3,
			'/user/reset-password' => 3,
			'/user/revoke-apikey' => 20,
			'/user/revoke-ip' => 20,
			'/user/revoke-wallet' => 20,
			'/user/submit-mfa' => 20,
			'/user/update-email' => 10,
			'/user/usage' => 100,
			'/admin/approve-user' => 30,
			'/admin/deny-user' => 30,
			'/admin/get-apikey' => 100,
			'/admin/get-apikeys' => 100,
			'/admin/get-applications' => 100,
			'/admin/get-ips' => 100,
			'/admin/get-wallets' => 100,
			'/admin/history' => 100,
			'/admin/update-limits' => 30,
			'/v1/dispense' => 50
		);

		$endpoint_throttle = $this->endpoints[$this->uri] ?? 100;

		$query = "
			SELECT hit, last_request
			FROM throttle
			WHERE ip = '$this->ip'
			AND uri = '$this->uri'
		";

		$selection = $db->do_select($query);

		if(!$selection) {
			$query = "
				INSERT INTO throttle (
					ip,
					uri
				) VALUES (
					'$this->ip',
					'$this->uri'
				)
			";
			$db->do_query($query);
		}

		$minute = 60;
		$minute_limit = $this->endpoints[$this->uri] ?? 100;
		$last_api_request = (int)($selection[0]['last_request'] ?? 0);
		$last_api_diff = $this->now - $last_api_request;
		$minute_throttle = (float)($selection[0]['hit'] ?? 0);
		$new_minute_throttle = $minute_throttle - $last_api_diff;
		$new_minute_throttle = $new_minute_throttle < 0 ? 0 : $new_minute_throttle;
		$new_minute_throttle += $minute / $minute_limit;
		$minute_hits_remaining = floor(($minute - $new_minute_throttle) * $minute_limit / $minute);
		$minute_hits_remaining = $minute_hits_remaining >= 0 ? $minute_hits_remaining : 0;

		if($new_minute_throttle > $minute) {
			_exit(
				"error",
				"Too many requests to this resource",
				429,
				"Too many requests to this resource"
			);
		}

		$query = "
			UPDATE throttle
			SET hit = $new_minute_throttle, last_request = $this->now
			WHERE ip = '$this->ip'
			AND uri = '$this->uri'
		";
		$db->do_query($query);


	}

	function __destruct() {
		//
	}

}
