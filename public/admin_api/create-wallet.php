<?php
include_once('../../core.php');
/**
 *
 * POST /admin/create-wallet
 *
 * HEADER Authorization: Bearer
 *
 * @param string $guid
 *
 */
class AdminCreateWallet extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';

		$query = "
			SELECT guid
			FROM users
			WHERE guid = '$user_guid'
		";
		$check = $db->do_select($query);

		if(!$check) {
			_exit(
				'error',
				'User does not exist',
				400,
				'User does not exist'
			);
		}

		$created_at = $helper->get_datetime();
		$wallet = $helper->generate_wallet();
		$address = $wallet['public'] ?? '';
		$secret = $wallet['secret'] ?? '';
		$secret_key_enc = $helper->aes_encrypt($secret);

		$query = "
			UPDATE wallets
			SET active = 0, inactive_at = '$created_at'
			WHERE guid = '$user_guid'
		";

		$db->do_query($query);

		$query = "
			INSERT INTO wallets (
			guid,
			address,
			secret_key_enc,
			active,
			created_at
			) VALUES (
			'$user_guid',
			'$address',
			'$secret_key_enc',
			1,
			'$created_at'
			)
		";

		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				$address
			);
		}

		_exit(
			'error',
			'Failed to create a new wallet address. Please contact administration',
			500,
			'Failed to create a new wallet address.'
		);
	}
}
new AdminCreateWallet();