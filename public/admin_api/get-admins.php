<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /admin/get-admins
 *
 * HEADER Authorization: Bearer
 *
 */
class AdminGetAdmins extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');
		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';

		$query = "
			SELECT *
			FROM users
			WHERE role = 'admin'
			OR role = 'sub-admin'
		";
		$selection = $db->do_select($query);

		_exit(
			'success',
			$selection
		);
	}
}
new AdminGetAdmins();