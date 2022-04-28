<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-apikey-holders
 *
 * HEADER Authorization: Bearer
 *
 */
class AdminGetApikeyHolders extends Endpoints {
	function __construct() {
		global $db;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';

		$query = "
			SELECT 
			a.*, 
			SUM(b.total_calls) AS total_calls
			FROM users AS a
			JOIN api_keys AS b
			ON b.guid = a.guid
			WHERE a.role = 'user'
			GROUP BY a.guid
		";
		$selection = $db->do_select($query);

		_exit(
			'success',
			$selection
		);
	}
}
new AdminGetApikeyHolders();
