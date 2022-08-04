<?php
/**
 *
 * GET /cron/verify-orders
 *
 * HEADER Authorization: Token
 *
 * System cron will use curl or wget to ping this endpoint for checking order deployments
 *
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');

global $helper;

authenticate_cron();

$query = "
	SELECT id, deploy_hash, guid, amount
	FROM orders
	WHERE fulfilled = 1
	AND success = 0
	LIMIT 5
";

$selection = $db->do_select($query);

if($selection) {
	foreach($selection as $s) {
		$order_id = $s['id'];
		$deploy_hash = $s['deploy_hash'] ?? '';
		$user_guid = $s['guid'] ?? '';
		$amount = (int)($s['amount'] ?? 0);

		if(
			$deploy_hash && 
			strlen($deploy_hash) == 64 &&
			ctype_xdigit($deploy_hash)
		) {
			$command = "casper-client get-deploy";
			$command .= " --node-address ".NODE_IP;
			$command .= " ".$deploy_hash;
			$stdout = shell_exec($command);
			$success = '';

			try {
				$json = json_decode($stdout);
				$execution_results = (array)($json->result->execution_results[0]->result ?? array());
				$execution_results = array_keys($execution_results)[0] ?? '';
				$success = strtolower($execution_results);
			} catch (Exception $e) {
				elog($e);
			}

			/* Success */
			if($success == 'success') {
				$query = "
					UPDATE orders
					SET success = 1
					WHERE id = $order_id
				";
				$db->do_query($query);

				// update user's cspr_actual
				$query = "
					SELECT cspr_actual
					FROM users
					WHERE guid = '$user_guid'
				";
				$cspr_actual = $db->do_select($query);
				$cspr_actual = (int)($cspr_actual[0]['cspr_actual'] ?? 0);
				$cspr_actual += $amount;
				$query = "
					UPDATE users
					SET cspr_actual = $cspr_actual
					WHERE guid = '$user_guid'
				";
				$db->do_query($query);
			}

			/* Failure */
			if($success == 'failure') {
				$query = "
					UPDATE orders
					SET fulfilled = 2
					WHERE id = $order_id
				";
				$db->do_query($query);
			}
		}
	}
}