<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /user/get-ips
 *
 * HEADER Authorization: Bearer
 *
 */
class UserGetIps extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';

		$query = "
			SELECT id, ip, created_at
			FROM ips
			WHERE guid = '$guid'
		";

		$selection = $db->do_select($query);

		_exit(
			'success',
			$selection
		);
	}
}
new UserGetIps();