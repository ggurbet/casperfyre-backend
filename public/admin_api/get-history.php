<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /admin/get-history
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 */
class AdminGetHistory extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';

		if ($user_guid) {
			$query = "
				SELECT * 
				FROM orders
				WHERE guid = '$user_guid'
				ORDER BY created_at DESC
			";
		} else {
			$query = "
				SELECT * 
				FROM orders
				ORDER BY created_at DESC
			";
		}

		$results = $db->do_select($query);

		_exit(
			'success',
			$results
		);
	}
}
new AdminGetHistory();