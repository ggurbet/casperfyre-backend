<?php
include_once('../../core.php');
/**
 *
 * POST /user/revoke-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param int $ipid  ID of the CIDR. Returned when /user/get-ips is called.
 *
 */
class UserRevokeIp extends Endpoints {
	function __construct(
		$ipid = 0
	) {
		global $db, $helper;

		require_method('POST');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';
		$ipid = (int)(parent::$params['ipid'] ?? 0);

		$query = "
			DELETE FROM ips
			WHERE id = $ipid
			AND guid = '$guid'
		";

		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				'Successfully removed IP range from whitelist'
			);
		}

		_exit(
			'error',
			'Failed to remove IP from whitelist',
			500,
			'Failed to remove IP from whitelist'
		);
	}
}
new UserRevokeIp();