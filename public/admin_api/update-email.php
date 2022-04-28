<?php
include_once('../../core.php');
/**
 *
 * PUT /admin/update-email
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $new_email
 * @param string  $mfa_code
 *
 */
class AdminUpdateEmail extends Endpoints {
	function __construct(
		$new_email = '',
		$mfa_code = ''
	) {
		global $db, $helper;

		require_method('PUT');

		$auth = authenticate_session(2);
		$guid = $auth['guid'] ?? '';
		$twofa_on = (int)($auth['twofa'] ?? 0);
		$new_email = parent::$params['new_email'] ?? '';
		$mfa_code = parent::$params['mfa_code'] ?? '';

		if(!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
			_exit(
				'error',
				'Invalid email address',
				400,
				'Invalid email address'
			);
		}

		// check new email
		$check_query = "
			SELECT email
			FROM users
			WHERE email = '$new_email'
		";
		$check = $db->do_select($check_query);

		if($check) {
			_exit(
				'error',
				'New email address specified is already in use',
				400,
				'New email address specified is already in use'
			);
		}

		// also check new email in email_change table
		$check_query = "
			SELECT guid
			FROM email_changes
			WHERE new_email = '$new_email'
			AND dead = 0
		";
		$check = $db->do_select($check_query);

		if($check) {
			$fetched_guid = $check[0]['guid'] ?? '';

			if($fetched_guid == $guid) {
				_exit(
					'error',
					'You are already in the process of changing your email. Please check your new email for an MFA code',
					400,
					'Already in the process of changing email. Please check new email for an MFA code'
				);
			}

			_exit(
				'error',
				'New email address specified is already in use',
				400,
				'New email address specified is already in use'
			);
		}

		// do first mfa code
		if(!$mfa_code) {
			_exit(
				'error',
				'MFA code required for changing email address. Please try again',
				400,
				'MFA code missing from request'
			);
		}

		$verified = $helper->verify_mfa($guid, $mfa_code);

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

		// Insert new email_change request. To be confirmed with second mfa_code
		$query = "
			UPDATE email_changes
			SET dead = 1
			WHERE guid = '$guid'
		";
		$db->do_query($query);
		$new_mfa_code = $helper->generate_hash(6);
		$created_at = $helper->get_datetime();
		$query = "
			INSERT INTO email_changes (
				guid,
				new_email,
				code,
				created_at
			) VALUES (
				'$guid',
				'$new_email',
				'$new_mfa_code'
			)
		";
		$ready = $db->do_query($query);

		if($ready) {
			$helper->schedule_email(
				'twofa',
				$new_email,
				APP_NAME.' - Confirm New Email',
				'Please find your confirmation code below to verify your new email address. This code expires in 10 minutes.',
				$new_mfa_code
			);

			_exit(
				'success',
				'Please check your new email address for a confirmation code'
			);
		}

		_exit(
			'error',
			'There was a problem submitting your change emaiil request',
			500,
			'There was a problem submitting your change emaiil request'
		);
	}
}
new AdminUpdateEmail();