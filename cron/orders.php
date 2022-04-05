<?php
include_once('../core.php');

global $helper;

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
		$amount = (int)($s['amount'] ?? 0);
		$sent_at = $helper->get_datetime();

		if(
			ctype_xdigit($address) &&
			$amount > 0 
		) {
			$command = "casper-client transfer";
			$command .= " --node-address http://".NODE_IP.":7777";
			$command .= " --transfer-id ".time();
			$command .= " --secret-key ".SECRET_KEY_PATH;
			$command .= " --amount ".$amount."000000000";
			$command .= " --targer--account ".$address;
			$command .= " --payment-amount 100000000";
			$command .= " --chain-name casper"
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