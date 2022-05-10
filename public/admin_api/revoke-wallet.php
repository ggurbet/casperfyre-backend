<?php
include_once('../../core.php');
/**
 *
 * POST /admin/revoke-wallet
 *
 * HEADER Authorization: Bearer
 *
 * @param string $address
 * @param string $guid
 *
 */
class AdminRevokeWallet extends Endpoints {
	function __construct(
		$address = '',
		$guid = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';
		$address = parent::$params['address'] ?? '';
		$revoked_at = $helper->get_datetime();

		$query = "
			UPDATE wallets
			SET active = 0, inactive_at = '$revoked_at'
			WHERE address = '$address'
			AND guid = '$user_guid'
		";

		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				'Successfully revoked wallet address'
			);
		}

		_exit(
			'error',
			'Failed to revoke this wallet',
			400,
			'Failed to revoke a wallet'
		);
	}
}
new AdminRevokeWallet();