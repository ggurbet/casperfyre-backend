<?php
include_once('../../core.php');
/**
 *
 * GET /user/logout
 *
 * HEADER Authorization: Bearer
 *
 */
class UserLogout extends Endpoints {
	function __construct() {
		global $helper;

		require_method('GET');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';

		$query = "
			DELETE FROM sessions
			WHERE guid = '$guid'
		";

		$db->do_query($query);

		_exit(
			'success',
			'Session terminated'
		);
	}
}
new UserLogout();