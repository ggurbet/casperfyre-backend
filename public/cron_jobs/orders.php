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
	LIMIT 5
";

$selection = $db->do_select($query);

if($selection) {
	foreach($selection as $s) {
		$order_id = $s['id'];
		$address = $s['address'];
		$wallet_id = (int)$s['wallet_id_used'];
		$amount = (int)($s['amount'] ?? 0);
		$sent_at = $helper->get_datetime();
		$ready_to_deploy = true;
		$stdout = '{}';

		if(
			ctype_xdigit($address) &&
			$amount > 0 
		) {
			/* generate pem from user's secret key hex */
			$secret_key_hex = $helper->get_wallet_secret_key($wallet_id);
			$secret_key_path = (
				BASE_DIR.'/tmp/sk-'.
				(string)$wallet_id.'-'.
				(string)$order_id.'-'.
				(string)time().'.pem'
			);

			try {
				$b64_key = base64_encode(hex2bin(
					'302e020100300506032b657004220420'.$secret_key_hex
				));

				file_put_contents(
					$secret_key_path,
					(
						"-----BEGIN PRIVATE KEY-----\n".
						$b64_key.
						"\n-----END PRIVATE KEY-----\n"
					)
				);
			} catch(Exception $e) {
				$ready_to_deploy = false;
				elog('Failed to translate secret key hex to pem format for order #'.$order_id);
			}

			/* send deploy */
			$command = "casper-client transfer";
			$command .= " --node-address http://".NODE_IP.":7777";
			$command .= " --transfer-id ".((string)time());
			$command .= " --secret-key ".$secret_key_path;
			$command .= " --amount ".(string)$amount."000000000";
			$command .= " --target-account ".$address;
			$command .= " --payment-amount 100000000";
			$command .= " --chain-name casper";
			elog($command);

			if($ready_to_deploy) {
				elog('READY TO DEPLOY');
				$stdout = shell_exec($command);
			}

			$success = '';

			try {
				elog($stdout);
				$json = json_decode($stdout);
				elog($json);
				$deploy_hash = '';
				$query = "
					UPDATE orders
					SET fulfilled = 1,
					sent_at = '$sent_at',
					deploy_hash = '$deploy_hash'
					WHERE id = $order_id
				";
				$db->do_query($query);
			} catch (Exception $e) {
				elog('Failed to send transfer deploy for order #'.$order_id);
				elog($e);
			}

			if(file_exists($secret_key_path)) {
				unlink($secret_key_path);
			}
		} else {
			$query = "
				UPDATE orders
				SET fulfilled = 2
				WHERE id = $order_id
			";

			$db->do_query($query);
		}
	}
}