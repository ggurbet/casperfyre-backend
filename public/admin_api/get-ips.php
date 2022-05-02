<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-ips
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 */
class AdminGetIps extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';

		$query = "
			SELECT id, ip, active, created_at
			FROM ips
		";

		if($user_guid) $query .= " WHERE guid = '$user_guid'";

		$selection = $db->do_select($query);

		_exit(
			'success',
			$selection
		);
	}
}
new AdminGetIps();