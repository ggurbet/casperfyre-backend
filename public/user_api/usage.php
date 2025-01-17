<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /user/usage
 *
 * HEADER Authorization: Bearer
 *
 */
class UserUsage extends Endpoints {
	function __construct() {
		global $helper;

		require_method('GET');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';
		$usage = get_usage($guid);

		_exit(
			'success',
			$usage
		);
	}
}
new UserUsage();