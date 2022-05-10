<?php
/**
 *
 * GET /cron/garbage
 *
 * HEADER Authorization: Token
 *
 * System cron will use curl or wget to ping this endpoint for cleanup of old records and junk.
 *
 */
include_once('../../core.php');

global $helper;

authenticate_cron();

/* Clear mfa_allowance */


/* Clear totp_logins */


/* Clear password_resets */


/* Clear email schedule */


/* Clear old twofa codes */


/* Clear old throttle records */
