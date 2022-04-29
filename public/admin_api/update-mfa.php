<?php
include_once('../../core.php');
/**
 *
 * PUT /admin/update-mfa
 *
 * HEADER Authorization: Bearer
 *
 * Requires MFA code to be sent and confirmed prior to requesting this endpoint.
 * After confirming MFA, user will have 5 minutes to submit request.
 *
 * @param bool  $active
 *
 */
class AdminUpdateMfa extends Endpoints {
	function __construct(
		$active = true
	) {
		global $db, $helper;

		require_method('PUT');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$active = isset(parent::$params['active']) ? (bool)parent::$params['active'] : null;
		$mfa_response = $helper->consume_mfa_allowance($admin_guid);

		if(!$mfa_response) {
			_exit(
				'error',
				'Requires MFA confirmation first',
				401,
				'Requires MFA confirmation first'
			);
		}

		if($active === true) {
			$query = "
				UPDATE users
				SET twofa = 1
				WHERE guid = '$admin_guid'
			";
			$db->do_query($query);

			_exit(
				'success',
				'Successfully turned MFA settings on'
			);
		}

		if($active === false) {
			$query = "
				UPDATE users
				SET twofa = 0
				WHERE guid = '$admin_guid'
			";
			$db->do_query($query);

			_exit(
				'success',
				'Successfully turned MFA settings off'
			);
		}

		_exit(
			'error',
			'Failed to update MFA settings',
			400,
			'Failed to update MFA settings'
		);
	}
}
new AdminUpdateMfa();