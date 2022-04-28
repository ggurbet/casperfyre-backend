<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-wallet
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 */
class AdminGetWallet extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';

		$query = "
			SELECT guid, active, created_at, inactive_at, address, balance
			FROM wallets
			WHERE guid = '$user_guid'
			AND active = 1
		";
		$selection = $db->do_select($query);
		$selection = $selection[0] ?? array();

		_exit(
			'success',
			$selection
		);
	}
}
new AdminGetWallet();