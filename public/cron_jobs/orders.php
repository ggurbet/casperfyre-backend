<?php
/**
 *
 * GET /cron/orders
 *
 * HEADER Authorization: Token
 *
 * System cron will use curl or wget to ping this endpoint for processing orders and getting deploy hashes
 *
 */
include_once('../../core.php');

global $helper;

authenticate_cron();

$query = "
	SELECT *
	FROM orders
	WHERE fulfilled = 0
	AND return_code = 200
	LIMIT 3
";

$selection = $db->do_select($query);

if($selection) {
	foreach($selection as $s) {
		$order_id = $s['id'];
		$address = $s['address'];
		$wallet_id = (int)$s['wallet_id_used'];
		$amount = (int)($s['amount'] ?? 0);
		$sent_at = $helper->get_datetime();

		if(
			ctype_xdigit($address) &&
			$amount > 0 
		) {
			/* generate pem from user's secret key hex */
			$secret_key_hex = $heper->get_user_secret_key($wallet_id);
			$secret_key_path = '';

			$command = "casper-client transfer";
			$command .= " --node-address http://".NODE_IP.":7777";
			$command .= " --transfer-id ".((string)time());
			$command .= " --secret-key ".$secret_key_path;
			$command .= " --amount ".(string)$amount."000000000";
			$command .= " --target-account ".$address;
			$command .= " --payment-amount 100000000";
			$command .= " --chain-name casper";
			$stdout = shell_exec($command);
			$success = '';

			try {
				$json = json_decode($stdout);
				elog($json);
			} catch (Exception $e) {
				elog($e);
			}

			$deploy_hash = '';
			$query = "
				UPDATE orders
				SET fulfilled = 1,
				sent_at = '$sent_at',
				deploy_hash = '$deploy_hash'
				WHERE id = $order_id
			";

			$db->do_query($query);
		}
	}
}