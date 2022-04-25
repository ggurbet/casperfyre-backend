<?php
/**
 * Http throttling class intended to mitigate brute force attacks. 
 * Especially for endpoints that call the auto-mailer, like forgot-password.
 *
 * @param  string  $real_ip
 */
class Throttle {
	/**
	 * Instantiating the class immediately causes the throttling to take effect.
	 * Exits with code 429 if the client fails based on IP address.
	 */
	function __construct($real_ip = '127.0.0.1') {
		if(
			$real_ip == '127.0.0.1' ||
			$real_ip == 'localhost' ||
			$real_ip == '::1' ||
			$real_ip == '0:0:0:0:0:0:0:1' ||
			DEV_MODE
		) {
			return true;
		}

		global $db;

		$this->now = (int)time();
		$this->ip = $real_ip;
		$this->uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		$this->endpoints = array(
			'/user/confirm-registration' => 10,
			'/user/create-apikey' => 5,
			'/user/create-ip' => 10,
			'/user/create-wallet' => 5,
			'/user/forgot-password' => 5,
			'/user/get-apikey' => 100,
			'/user/get-apikeys' => 100,
			'/user/get-ips' => 100,
			'/user/get-wallet' => 100,
			'/user/get-wallets' => 100,
			'/user/history' => 100,
			'/user/login' => 10,
			'/user/logout' => 100,
			'/user/me' => 150,
			'/user/name-by-email' => 20,
			'/user/register' => 3,
			'/user/resend-code' => 3,
			'/user/reset-password' => 3,
			'/user/revoke-apikey' => 10,
			'/user/revoke-ip' => 20,
			'/user/revoke-wallet' => 10,
			'/user/submit-mfa' => 10,
			'/user/update-email' => 5,
			'/user/usage' => 100,
			'/admin/approve-user' => 30,
			'/admin/deny-user' => 30,
			'/admin/get-apikey' => 100,
			'/admin/get-apikeys' => 100,
			'/admin/get-applications' => 100,
			'/admin/get-ips' => 100,
			'/admin/get-wallets' => 100,
			'/admin/history' => 100,
			'/admin/update-limits' => 10,
			'/v1/dispense' => 30
		);

		$endpoint_throttle = $this->endpoints[$this->uri] ?? 30;

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
