<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /admin/get-users
 *
 * HEADER Authorization: Bearer
 *
 */
class AdminGetUsers extends Endpoints {
	function __construct() {
		global $db;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';

		$query = "
			SELECT *
			FROM users
			WHERE role = 'user'
		";
		$selection = $db->do_select($query);

		_exit(
			'success',
			$selection
		);
	}
}
new AdminGetUsers();