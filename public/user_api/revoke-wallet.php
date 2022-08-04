<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /user/revoke-wallet
 *
 * HEADER Authorization: Bearer
 *
 * @param string $address
 *
 */
class UserRevokeWallet extends Endpoints {
	function __construct(
		$address = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';
		$address = parent::$params['address'] ?? '';
		$revoked_at = $helper->get_datetime();

		$query = "
			UPDATE wallets
			SET active = 0, inactive_at = '$revoked_at'
			WHERE address = '$address'
			AND guid = '$guid'
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
new UserRevokeWallet();