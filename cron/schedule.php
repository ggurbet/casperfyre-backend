<?php
include_once('../core.php');

global $helper, $db;

$query = "
	SELECT *
	FROM schedule
	WHERE complete = 0
	LIMIT 10
";
$selection = $db->do_select($query);

if($selection) {
	foreach ($selection as $s) {
		$template_id = $s['template_id'] ?? '';
		$subject = $s['subject'] ?? '';
		$body = $s['body'] ?? '';
		$link = $s['link'] ?? '';
		$email = $s['email'] ?? '';

		switch ($template_id) {
			case 'register': $template = file_get_contents(__DIR__.'/../templates/register.html'); break;
			case 'forgot-password': $template = file_get_contents(__DIR__.'/../templates/forgot-password.html'); break;
			default: $template = file_get_contents('../templates/register.html'); break;
		}

		$template = str_replace(
			'[LOGO_URL]', 
			"https://".getenv('FRONTEND_URL').'/img/logo.png', 
			$template
		);

		$template = str_replace('[SUBJECT]', $subject, $template);
		$template = str_replace('[BODY]', $body, $template);
		$template = str_replace('[LINK]', $link, $template);

		////
	}
}