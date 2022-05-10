<?php
/**
 *
 * GET /cron/schedule
 *
 * HEADER Authorization: Token
 *
 * System cron will use curl or wget to ping this endpoint every 60 seconds to handle email scheduler.
 *
 */
include_once('../../core.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

global $helper, $db;

authenticate_cron();

$emailer = new PHPMailer(true);
// $emailer->SMTPDebug = SMTP::DEBUG_SERVER;
$emailer->isSMTP();
$emailer->Host = getenv('EMAIL_HOST');
$emailer->Port = getenv('EMAIL_PORT');
$emailer->SMTPKeepAlive = true;
// $emailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$emailer->SMTPSecure = 'tls';
$emailer->SMTPAuth = true;
$emailer->Username = getenv('EMAIL_USER');
$emailer->Password = getenv('EMAIL_PASS');
$emailer->setFrom(getenv('EMAIL_FROM'), getenv('APP_NAME'));
$emailer->addReplyTo(getenv('EMAIL_FROM'), getenv('APP_NAME'));
$emailer->isHTML(true);

$query = "
	SELECT *
	FROM schedule
	WHERE complete = 0
	LIMIT 10
";
$selection = $db->do_select($query);

if($selection) {
	foreach ($selection as $s) {
		$sid = $s['id'] ?? 0;
		$template_id = $s['template_id'] ?? '';
		$subject = $s['subject'] ?? '';
		$body = $s['body'] ?? '';
		$link = $s['link'] ?? '';
		$email = $s['email'] ?? '';
		$sent_at = $helper->get_datetime();

		switch ($template_id) {
			case 'welcome': $template = file_get_contents(__DIR__.'/../../templates/welcome.html'); break;
			case 'approved': $template = file_get_contents(__DIR__.'/../../templates/approved.html'); break;
			case 'denied': $template = file_get_contents(__DIR__.'/../../templates/denied.html'); break;
			case 'twofa': $template = file_get_contents(__DIR__.'/../../templates/twofa.html'); break;
			case 'register': $template = file_get_contents(__DIR__.'/../../templates/register.html'); break;
			case 'register-admin': $template = file_get_contents(__DIR__.'/../../templates/register-admin.html'); break;
			case 'forgot-password': $template = file_get_contents(__DIR__.'/../../templates/forgot-password.html'); break;
			default: $template = file_get_contents(__DIR__.'/../../templates/register.html'); break;
		}

		$template = str_replace(
			'[LOGO_URL]', 
			"https://".getenv('FRONTEND_URL').'/images/logo.png', 
			$template
		);

		$template = str_replace('[SUBJECT]', $subject, $template);
		$template = str_replace('[BODY]', $body, $template);
		$template = str_replace('[LINK]', $link, $template);

		try {
			$emailer->addAddress($email);
			$emailer->Subject = $subject;
			$emailer->Body = $template;
			$emailer->send();
			elog("SENT Scheduled '".$template_id."' email ID# ".$sid." sent to: ".$email);

			$query = "
				UPDATE schedule
				SET complete = 1, sent_at = '$sent_at'
				WHERE id = $sid
			";
			$db->do_query($query);
		} catch (Exception $e) {
			// elog($e);
			$emailer->getSMTPInstance()->reset();
			elog("Failed to send scheduled '".$template_id."' email ID# ".$sid);
		}

		$emailer->clearAddresses();
		$emailer->clearAttachments();
	}
}