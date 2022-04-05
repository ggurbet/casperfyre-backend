<?php
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';
$created_at = $helper->get_datetime();
$wallet = $helper->generate_wallet();
$address = $wallet['public'] ?? '';
$secret = $wallet['secret'] ?? '';
$secret_key_enc = $helper->aes_encrypt($secret);

$query = "
	UPDATE wallets
	SET active = 0
	WHERE guid = '$guid'
";

$db->do_query($query);

$query = "
	INSERT INTO wallets (
	guid,
	address,
	secret_key_enc,
	active,
	created_at
	) VALUES (
	'$guid',
	'$address',
	'$secret_key_enc',
	1,
	'$created_at'
	)
";

$result = $db->do_query($query);

if($result) {
	_exit(
		'success',
		$address
	);
}

_exit(
	'error',
	'Failed to create a new wallet address. Please contact administration',
	500
);