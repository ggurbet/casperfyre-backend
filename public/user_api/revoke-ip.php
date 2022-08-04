<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /user/revoke-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param int $ip_id  ID of the CIDR. Returned when /user/get-ips is called.
 *
 */
class UserRevokeIp extends Endpoints {
	function __construct(
		$ip_id = 0
	) {
		global $db, $helper;

		require_method('POST');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';
		$ip_id = (int)(parent::$params['ip_id'] ?? 0);

		$query = "
			UPDATE ips
			SET active = 0
			WHERE id = $ip_id
			AND guid = '$guid'
		";

		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				'Successfully disabled IP range from whitelist'
			);
		}

		_exit(
			'error',
			'Failed to disable IP from whitelist',
			500,
			'Failed to disable IP from whitelist'
		);
	}
}
new UserRevokeIp();