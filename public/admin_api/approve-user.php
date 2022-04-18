<?php
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();
$user_guid = $params['guid'] ?? '';
$created_at = $helper->get_datetime();

if($user_guid && $admin_guid) {
	$query = "
		UPDATE users
		SET admin_approved = 1
		WHERE guid = '$user_guid'
	";
	$db->do_query($query);

	/* check status of new user first */
	$query = "
		SELECT address
		FROM wallets
		WHERE guid = '$user_guid' 
	";
	$selection = $db->do_select($query);
	$address = $selection[0]['address'] ?? null;

	if(!$address) {
		/* create/attach user's operator wallet */
		$wallet = $helper->generate_wallet();
		$address = $wallet['public'] ?? '';
		$secret_key_enc = $wallet['secret'] ? $helper->aes_encrypt($wallet['secret']) : '';

		$query = "
			INSERT INTO wallets (
			guid,
			address,
			secret_key_enc,
			created_at
			) VALUES (
			'$user_guid',
			'$address',
			'$secret_key_enc',
			'$created_at'
			)
		";
		$db->do_query($query);
	}

	$query = "
		SELECT api_key
		FROM api_keys
		WHERE guid = '$user_guid' 
	";
	$selection = $db->do_select($query);
	$api_key = $selection[0]['api_key'] ?? null;

	if(!$api_key) {
		$new_api_key = $helper->generate_apikey();

		$query = "
			INSERT INTO api_keys (
			guid,
			api_key,
			created_at
			) VALUES (
			'$user_guid',
			'$new_api_key',
			'$created_at'
			)
		";
		$db->do_query($query);
	}

	/* send email to approved user */
	$query = "
		SELECT email, first_name
		FROM users
		WHERE guid = '$user_guid'
	";
	$selection = $db->do_select($query);
	$user_email = $selection[0]['email'] ?? '';
	$first_name = $selection[0]['first_name'] ?? '';
	$subject = 'CasperFYRE Application Status';
	$body = 'Great news, '.$first_name.'. You have been <b>approved</b> and granted access to your dashboard.<br><br>';

	if($user_email) {
		$helper->schedule_email(
			'application_approved',
			$user_email,
			$subject,
			$body,
			getenv('FRONTEND_URL').'/auth/login'
		);
	}

	_exit(
		'success',
		'Successfully approved user'
	);
}

_exit(
	'error',
	'Please provide guid of the user to approve',
	400
);
