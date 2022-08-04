<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /admin/get-applications
 *
 * HEADER Authorization: Bearer
 *
 */
class AdminGetApplications extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';

		$query = "
			SELECT guid, email, company, last_ip, cspr_expectation, description, created_at
			FROM users
			WHERE admin_approved = 0
			AND verified = 1
			AND role = 'user'
		";
		$selection = $db->do_select($query);

		_exit(
			'success',
			$selection
		);
	}
}
new AdminGetApplications();