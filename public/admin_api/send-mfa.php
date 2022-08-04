<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /admin/send-mfa
 *
 * HEADER Authorization: Bearer
 *
 */
class AdminSendMfa extends Endpoints {
	function __construct() {
		global $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$sent = $helper->send_mfa($admin_guid);

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
new AdminSendMfa();