<?php
include_once('../../core.php');
/**
 *
 * GET /user/get-wallets
 *
 * HEADER Authorization: Bearer
 *
 */
class UserGetWallets extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';

		$query = "
			SELECT address, active, created_at, inactive_at, balance
			FROM wallets
			WHERE guid = '$guid'
			ORDER BY id DESC
		";

		$selection = $db->do_select($query);

		_exit(
			'success',
			$selection
		);
	}
}
new UserGetWallets();