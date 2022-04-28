<?php
/**
 * Http throttling class intended to mitigate brute force attacks on th API. 
 * Especially for endpoints that call the auto-mailer, eg. forgot-password.
 *
 * Instantiating the class immediately causes the throttling to take effect.
 * Exits with code 429 if the client fails based on IP address.
 *
 * @param  string  $real_ip
 */
class Throttle {
	/** 
	 * Known endpoints array. Referenced by PHPUnit
	 */
	public const endpoints = array(
		'/user/confirm-registration' => 10,
		'/user/create-apikey' => 5,
		'/user/create-ip' => 10,
		'/user/create-wallet' => 5,
		'/user/forgot-password' => 5,
		'/user/get-apikey' => 60,
		'/user/get-apikeys' => 60,
		'/user/get-ips' => 60,
		'/user/get-wallet' => 60,
		'/user/get-wallets' => 60,
		'/user/history' => 60,
		'/user/login' => 5,
		'/user/logout' => 100,
		'/user/me' => 150,
		'/user/name-by-email' => 20,
		'/user/register' => 3,
		'/user/resend-code' => 3,
		'/user/reset-password' => 3,
		'/user/update-password' => 5,
		'/user/revoke-apikey' => 10,
		'/user/revoke-ip' => 20,
		'/user/revoke-wallet' => 10,
		'/user/submit-mfa' => 10,
		'/user/send-mfa' => 5,
		'/user/update-mfa' => 3,
		'/user/update-email' => 5,
		'/user/usage' => 100,
		'/admin/send-mfa' => 5,
		'/admin/update-mfa' => 3,
		'/admin/update-email' => 3,
		'/admin/approve-user' => 30,
		'/admin/deny-user' => 30,
		'/admin/create-wallet' => 10,
		'/admin/disable-ip' => 30,
		'/admin/enable-ip' => 30,
		'/admin/create-apikey' => 30,
		'/admin/disable-apikey' => 30,
		'/admin/enable-apikey' => 30,
		'/admin/disable-user' => 30,
		'/admin/enable-user' => 30,
		'/admin/reset-user-password' => 10,
		'/admin/update-password' => 5,
		'/admin/get-apikey' => 60,
		'/admin/get-apikeys' => 60,
		'/admin/get-applications' => 60,
		'/admin/get-ips' => 60,
		'/admin/get-wallets' => 60,
		'/admin/get-wallet' => 60,
		'/admin/get-limits' => 60,
		'/admin/get-user' => 100,
		'/admin/get-users' => 100,
		'/admin/get-admins' => 100,
		'/admin/create-admin' => 10,
		'/admin/history' => 100,
		'/admin/update-limits' => 10,
		'/v1/dispense' => 30
	);

	function __construct(string $real_ip = '127.0.0.1') {
		// forget throttling during dev
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

		// no need to go any further for unit tests
		if($real_ip == 'unittest') {
			return true;
		}

		// check hit, log hit
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
		$minute_limit = self::endpoints[$this->uri] ?? 30;
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

	/**
	 * Used by PHPUnit to verify known endpoints are accounted for in the router.
	 *
	 * @return array<string, int> endpoints
	 */
	public static function get_endpoints() {
		return self::endpoints;
	}
}
