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
			a.guid, a.role, a.email, a.verified, a.first_name, a.last_name,
			a.api_key_active as account_active, a.created_at, a.confirmation_code,
			a.last_ip, a.company, a.description, a.cspr_expectation, a.cspr_actual,
			a.admin_approved, a.deny_reason, a.twofa,
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