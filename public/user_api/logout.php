<?php
include_once('../../core.php');

global $helper;

require_method('GET');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';

$query = "
	DELETE FROM sessions
	WHERE guid = '$guid'
";

$db->do_query($query);

_exit(
	'success'
	'Session terminated'
);