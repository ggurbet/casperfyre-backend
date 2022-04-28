<?php
include_once('../../core.php');
/**
 *
 * POST /admin/enable-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param int $ip_id
 */
class AdminEnableIp extends Endpoints {
	function __construct(
		$ip_id = 0
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$ip_id = (int)(parent::$params['ip_id'] ?? 0);

		$query = "
			UPDATE ips
			SET active = 1
			WHERE id = $ip_id
		";
		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				'Successfully enabled IP address'
			);
		}

		_exit(
			'error',
			'Failed to enable IP address',
			400,
			'Failed to enable IP address'
		);
	}
}
new AdminEnableIp();