<?php
include_once('../../core.php');
/**
 *
 * POST /user/send-mfa
 *
 * HEADER Authorization: Bearer
 *
 */
class UserSendMfa extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(1);
		$user_guid = $auth['guid'] ?? '';
		$sent = $helper->send_mfa($user_guid);

		if($sent) {
			_exit(
				'success',
				'Check your email for an MFA code'
			);
		}

		_exit(
			'error',
			'Failed to send MFA code',
			500,
			'Failed to send MFA code'
		);
	}
}
new UserSendMfa();