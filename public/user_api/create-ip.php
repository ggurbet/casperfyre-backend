<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /user/create-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param string $ip  Can be CIDR range or single IP address
 *
 */
class UserCreateIp extends Endpoints {
	function __construct(
		$ip = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';
		$ip = parent::$params['ip'] ?? '';
		$ip = preg_replace("/([^A-Fa-f0-9.\/])/", '', $ip);
		$created_at = $helper->get_datetime();

		if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			_exit(
				'error',
				'Invalid IP address',
				400,
				'Invalid IP address'
			);
		}

		$query = "
			INSERT INTO ips (
				guid,
				ip,
				created_at
			) VALUES (
				'$guid',
				'$ip',
				'$created_at'
			)
		";

		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				'Successfully added IP to whitelist'
			);
		}

		_exit(
			'error',
			'Failed to add IP to whitelist',
			500,
			'Failed to add IP to whitelist'
		);
	}
}
new UserCreateIp();