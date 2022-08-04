<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /admin/create-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 * @param string  $ip   Can be CIDR range or single IP address
 *
 */
class AdminCreateIp extends Endpoints {
	function __construct(
		$guid = '',
		$ip = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';
		$ip = parent::$params['ip'] ?? '';
		$ip = preg_replace("/([^A-Fa-f0-9.\/])/", '', $ip);
		$created_at = $helper->get_datetime();

		$check_query = "
			SELECT guid
			FROM users
			WHERE guid = '$user_guid'
		";
		$check = $db->do_select($check_query);

		if(!$check) {
			_exit(
				'error',
				'User does not exist',
				400,
				'User does not exist'
			);
		}

		$query = "
			INSERT INTO ips (
				guid,
				ip,
				created_at
			) VALUES (
				'$user_guid',
				'$ip',
				'$created_at'
			)
		";

		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				'Successfully added IP to user whitelist'
			);
		}

		_exit(
			'error',
			'Failed to add new IP to user whitelist',
			500,
			'Failed to add new IP to user whitelist'
		);
	}
}
new AdminCreateIp();