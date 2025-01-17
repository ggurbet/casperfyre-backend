<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /admin/get-user
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 */
class AdminGetUser extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';

		$query = "
			SELECT *
			FROM users
			WHERE guid = '$user_guid'
		";
		$selection = $db->do_select($query);
		$selection = $selection[0] ?? null;

		_exit(
			'success',
			$selection
		);
	}
}
new AdminGetUser();