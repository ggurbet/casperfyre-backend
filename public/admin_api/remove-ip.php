<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /admin/remove-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param int $ip_id
 */
class AdminRemoveIp extends Endpoints {
	function __construct(
		$ip_id = 0
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$ip_id = (int)(parent::$params['ip_id'] ?? 0);

		$query = "
			DELETE FROM ips
			WHERE id = $ip_id
		";
		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				'Successfully removed IP address'
			);
		}

		_exit(
			'error',
			'Failed to remove IP address',
			400,
			'Failed to remove IP address'
		);
	}
}
new AdminRemoveIp();