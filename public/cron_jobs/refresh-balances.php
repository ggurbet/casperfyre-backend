<?php
/**
 *
 * GET /cron/refresh-balances
 *
 * HEADER Authorization: Token
 *
 * System cron will use curl or wget to ping this endpoint for refreshing active wallet balances
 *
 */
include_once('../../core.php');

global $helper, $db;

authenticate_cron();

$now = $helper->get_datetime();

$query = "
	SELECT id, address
	FROM wallets
	WHERE active = 1
	ORDER BY last_balance_check ASC
	LIMIT 10
";

$selection = $db->do_select($query);

if($selection) {
	foreach($selection as $s) {
		$new_balance = $helper->get_wallet_balance($s['address']);
		$wallet_id = $s['id'] ?? 0;

		$query = "
			UPDATE wallets
			SET balance = $new_balance, last_balance_check = '$now'
			WHERE id = $wallet_id
		";
		$db->do_query($query);
	}
}

/* also do inactive, but only up to three months old */
$three_months_ago = $helper->get_datetime(-7889400);

$query = "
	SELECT id, address
	FROM wallets
	WHERE active = 0
	AND inactive_at > '$three_months_ago' 
	ORDER BY last_balance_check ASC
	LIMIT 5
";

$selection2 = $db->do_select($query);

if($selection2) {
	foreach($selection2 as $s2) {
		$new_balance = $helper->get_wallet_balance($s2['address']);
		$wallet_id = $s2['id'] ?? 0;

		$query = "
			UPDATE wallets
			SET balance = $new_balance, last_balance_check = '$now'
			WHERE id = $wallet_id
		";
		$db->do_query($query);
	}
}