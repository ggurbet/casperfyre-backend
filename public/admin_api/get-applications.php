<?php
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';

$query = "
	SELECT guid, email, company, last_ip, cspr_expectation, description
	FROM users
	WHERE admin_approved = 0
	AND verified = 1
	AND role = 'user'
";
$selection = $db->do_select($query);

_exit(
	'success',
	$selection
);
