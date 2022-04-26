<?php
/**
 *
 * POST /admin/send-mfa
 *
 * HEADER Authorization: Bearer
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();

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
