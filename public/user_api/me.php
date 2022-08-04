<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /user/me
 *
 * HEADER Authorization: Bearer
 *
 */
class UserMe extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';

		$query = "
			SELECT
			guid, role, email, verified, first_name, last_name, api_key_active,
			created_at, last_ip, company, description, cspr_expectation, cspr_actual, 
			admin_approved, deny_reason, twofa, totp
			FROM users
			WHERE guid = '$guid'
		";

		$me = $db->do_select($query);
		$me = $me[0] ?? null;

		if($me) {
			_exit(
				'success',
				$me
			);
		}

		_exit(
			'error',
			'Unauthorized',
			403,
			'Unauthorized'
		);
	}
}
new UserMe();