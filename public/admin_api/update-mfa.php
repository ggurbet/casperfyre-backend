<?php
/**
 *
 * PUT /admin/update-mfa
 *
 * HEADER Authorization: Bearer
 *
 * @param bool    active
 * @param string  mfa_code
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('PUT');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$twofa_on = (int)($auth['twofa'] ?? 0);
$params = get_params();
$active = isset($params['active']) ? (bool)$params['active'] : null;
$mfa_code = $params['mfa_code'] ?? '';

if($active === true) {
	if(!$mfa_code) {
		_exit(
			'error',
			'MFA code required for turning on MFA. Please try again',
			400,
			'MFA code missing from request'
		);
	}

	$verified = $helper->verify_mfa($admin_guid, $mfa_code);

	if($verified == 'success') {
		// turn on mfa
		$query = "
			UPDATE users
			SET twofa = 1
			WHERE guid = '$admin_guid'
		";
		$db->do_query($query);

		_exit(
			'success',
			'Successfully turned on MFA settings'
		);
	}

	if($verified == 'expired') {
		$query = "
			DELETE FROM twofa
			WHERE guid = '$admin_guid'
		";
		$db->do_query($query);

		_exit(
			'error',
			'MFA code expired. Please try updating your settings again',
			400,
			'MFA code expired'
		);
	}

	_exit(
		'error',
		'MFA code incorrect',
		400,
		'MFA code incorrect'
	);
}

if($active === false) {
	// check 2fa code, if on
	if($twofa_on == 1) {
		if(!$mfa_code) {
			_exit(
				'error',
				'MFA code required for turning off MFA. Please try again',
				400,
				'MFA code missing from request'
			);
		}

		$verified = $helper->verify_mfa($admin_guid, $mfa_code);

		if($verified == 'expired') {
			_exit(
				'error',
				'MFA code expired. Please try updating your settings again',
				400,
				'MFA code expired'
			);
		}

		if($verified == 'incorrect') {
			_exit(
				'error',
				'MFA code incorrect',
				400,
				'MFA code incorrect'
			);
		}
	}

	$query = "
		UPDATE users
		SET twofa = 0
		WHERE guid = '$admin_guid'
	";
	$db->do_query($query);

	_exit(
		'success',
		'Successfully turned off MFA settings'
	);
}

_exit(
	'error',
	'Failed to update MFA settings',
	400,
	'Failed to update MFA settings'
);