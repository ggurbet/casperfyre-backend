<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /admin/create-admin
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $email
 * @param string  $role  enum ["admin", "sub-admin"] default "sub-admin"
 *
 */
class AdminCreateAdmin extends Endpoints {
	function __construct(
		$email = '',
		$role = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(3);
		$admin_guid = $auth['guid'] ?? '';
		$new_admin_email = parent::$params['email'] ?? '';
		$role = parent::$params['role'] ?? 'sub-admin';
		$selected_role = 'sub-admin';

		if(!filter_var($new_admin_email, FILTER_VALIDATE_EMAIL)) {
			_exit(
				'error',
				'Invalid email address',
				400,
				'Invalid email address'
			);
		}

		/* check pre-existing email */
		$query = "
			SELECT guid
			FROM users
			WHERE email = '$new_admin_email'
		";
		$check = $db->do_select($query);

		if($check) {
			_exit(
				'error',
				'An account with this email address already exists',
				400,
				'An account with this email address already exists'
			);
		}

		switch ($role) {
			case 'admin': $selected_role = 'admin'; break;
			case 'sub-admin': $selected_role = 'sub-admin'; break;
			default: $selected_role = 'sub-admin'; break;
		}

		$guid = $helper->generate_guid();
		$created_at = $helper->get_datetime();
		$confirmation_code = $helper->generate_hash(6);
		$reset_auth_code = $helper->generate_hash();
		$password_hash = '';
		$registration_ip = $helper->get_real_ip();

		$query = "
			INSERT INTO users (
				guid, 
				role,
				email,
				verified,
				first_name, 
				last_name, 
				password, 
				created_at, 
				confirmation_code,
				last_ip,
				company,
				description,
				cspr_expectation,
				admin_approved
			) VALUES (
				'$guid',
				'$selected_role',
				'$new_admin_email',
				1,
				'',
				'',
				'$password_hash',
				'$created_at',
				'$confirmation_code',
				'$registration_ip',
				'',
				'',
				0,
				1
			)
		";
		$db->do_query($query);

		// record auth code so we can de-auth after single use
		$query = "
			INSERT INTO password_resets (
				guid,
				code
			) VALUES (
				'$guid',
				'$reset_auth_code'
			)
		";
		$db->do_query($query);

		$subject = 'Welcome to '.APP_NAME.' Admin';
		$body = 'Welcome to '.APP_NAME.' Admin. Follow the link below to confirm your account and set a password:<br><br>';
		$uri = $helper->aes_encrypt($guid.'::'.$confirmation_code.'::'.(string)time().'::'.$reset_auth_code.'::register-admin');
		$link = 'https://'.getenv('FRONTEND_URL').'/auth/reset-password/'.$uri.'?email='.$new_admin_email;

		$helper->schedule_email(
			'register-admin',
			$new_admin_email,
			$subject,
			$body,
			$link
		);

		_exit(
			'success',
			'Successfully sent invite to '.$new_admin_email
		);

		_exit(
			'error',
			'Failed to a create new api key',
			500,
			'Failed to a create new api key'
		);
	}
}
new AdminCreateAdmin();