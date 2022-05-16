<?php
include_once('../../../core.php');
/**
 *
 * POST /v1/dispense
 *
 * HEADER Authorization: Token
 *
 * This is the main endpoint that devs will use to request tokens from their faucet API.
 *
 * @api
 * @version  1.0.0   CasperFYRE
 * @param    string  $address
 * @param    int     $amount
 *
 * @response
 * 200 - Success. Dispensing $amount CSPR to $address
 *
 */
class Dispense extends Endpoints {
	function __construct(
		string $address = '',
		int    $amount = 0
	) {
		require_method('POST');
		$auth = authenticate_api();
		$guid = $auth['guid'] ?? '';
		$params = get_params();
		$address = $params['address'] ?? null;
		$amount = (int)($params['amount'] ?? 0);

		process_order(
			$guid, 
			$address, 
			$amount,
			$auth
		);
	}
}

new Dispense();