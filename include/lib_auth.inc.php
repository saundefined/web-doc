<?php
/**
 * +----------------------------------------------------------------------+
 * | PHP Documentation Site Source Code                                   |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 1997-2005 The PHP Group                                |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 3.0 of the PHP license,       |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/3_0.txt.                                  |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors: Jacques Marneweck <jacques@php.net>                         |
 * |          Nuno Lopes <nlopess@php.net>                                |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

require_once 'cvs-auth.inc';

if (!$idx = sqlite_open(SQLITE_DIR . 'users.sqlite')) {
 	die ('auth DB not available');
}

//list of docweb admins that have 'special' rights
$admins = array(
	'didou',
	'goba',
	'jacques',
	'nlopess',
	'philip',
	'sean',
	'vincent',
);


/**
 * read the magic cookie and return array(user, pass)
 */
function read_magic_cookie()
{
	return explode(':', base64_decode(@$_COOKIE['MAGIC_COOKIE']));
}


/**
 * Credential checking of the $_COOKIE['MAGIC_COOKIE']
 */
function auth()
{
	if (isset($_COOKIE['MAGIC_COOKIE'])) {
		list($user, $pw) = read_magic_cookie();

		if (!verify_password($user, $pw)) {
			header ('Location: http://doc.php.net/login.php');
			exit;
		}
	} elseif (isset($_POST['username']) && isset($_POST['passwd'])) {
		if (!verify_password($_POST['username'], $_POST['passwd'])) {
			header ('Location: http://doc.php.net/login.php');
			exit;
		}

		setcookie(
			'MAGIC_COOKIE',
			base64_encode("{$_POST['username']}:{$_POST['passwd']}"),
			time()+3600*24*12,
			'/',
			'.php.net'
		);
	} else {
		header ('Location: http://doc.php.net/login.php');
		exit;
	}
}


/**
 * Checks if a user has admin rights
 */
function is_admin()
{
	if (!isset($_COOKIE['MAGIC_COOKIE']))
		return false;

	list($user) = read_magic_cookie();

	return in_array($user, $GLOBALS['admins']);
}


/**
 * read user info from the DB
 */
function user_info()
{
	global $idx;

	list($user) = read_magic_cookie();

	$result = @sqlite_unbuffered_query($idx, "SELECT * FROM users WHERE username='" . sqlite_escape_string($user) . "'");
	if (!$result) {
		return false;
	}

	return sqlite_fetch_array($result, SQLITE_ASSOC);
}
