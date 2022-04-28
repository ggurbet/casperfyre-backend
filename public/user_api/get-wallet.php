<?php
include_once('../../core.php');
/**
 *
 * GET /user/get-wallet
 *
 * HEADER Authorization: Bearer
 *
 */
class UserGetWallet extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? 0;

		$query = "
			SELECT address, created_at, balance
			FROM wallets
			WHERE guid = '$guid'
			AND active = 1
		";

		$selection = $db->do_select($query);
		$selection = $selection[0] ?? null;

		if($selection) {
			_exit(
				'success',
				$selection
			);
		}

		_exit(
			'error',
			'You currently have no active wallets',
			404,
			'User has no active wallets'
		);
	}
}
new UserGetWallet();