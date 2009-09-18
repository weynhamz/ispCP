<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright	2001-2006 by moleSoftware GmbH
 * @copyright	2006-2009 by ispCP | http://isp-control.net
 * @version		SVN: $Id: alias_delete.php 1952 2009-08-30 08:50:02Z kilburn $
 * @link		http://isp-control.net
 * @author		ispCP Team
 *
 * @license
 *   This program is free software; you can redistribute it and/or modify it under
 *   the terms of the MPL General Public License as published by the Free Software
 *   Foundation; either version 1.1 of the License, or (at your option) any later
 *   version.
 *   You should have received a copy of the MPL Mozilla Public License along with
 *   this program; if not, write to the Open Source Initiative (OSI)
 *   http://opensource.org | osi@opensource.org
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

if (isset($_GET['id']) && $_GET['id'] !== '') {
	$als_id = $_GET['id'];
	$dmn_id = get_user_domain_id($sql, $_SESSION['user_id']);

	$query = "
		SELECT
			`alias_id`
			`alias_name`
		FROM
			`domain_aliasses`
		WHERE
			`domain_id` = ?
		AND
			`alias_id` = ?
	";

	$rs = exec_query($sql, $query, array($dmn_id, $als_id));
	$alias_name = $rs->fields['alias_name'];

	if ($rs->RecordCount() == 0) {
		user_goto('domains_manage.php');
	}

	// check for subdomains
	$query = "
		SELECT
			COUNT(`subdomain_alias_id`) AS `count`
		FROM
			`subdomain_alias`
		WHERE
			`alias_id` = ?
	";

	$rs = exec_query($sql, $query, array($als_id));
	if ($rs->fields['count'] > 0) {
		set_page_message(tr('Domain alias you are trying to remove has subdomains!<br>First remove them!'));
		user_goto('domains_manage.php');
	}

	// check for mail accounts
	$query = "
		SELECT
			COUNT(`mail_id`) AS `cnt`
		FROM
			`mail_users`
		WHERE
			(`sub_id` = ?
			AND
			`mail_type` LIKE '%alias_%')
		OR
			(`sub_id` IN (SELECT `subdomain_alias_id` FROM `subdomain_alias` WHERE `alias_id` = ?)
			AND
			`mail_type` LIKE '%alssub_%')
	";

	$rs = exec_query($sql, $query, array($als_id,$als_id));

	if ($rs->fields['cnt'] > 0) {
		set_page_message(tr('Domain alias you are trying to remove has email accounts !<br>First remove them!'));
		user_goto('domains_manage.php');
	}

	// check for ftp accounts
	$query = "
		SELECT
			COUNT(`fg`.`gid`) AS `ftpnum`
		FROM
			`ftp_group` `fg`,
			`domain` `dmn`,
			`domain_aliasses` `d`
		WHERE
			`d`.`alias_id` = ?
		AND
			`fg`.`groupname` = `dmn`.`domain_name`
		AND
			`fg`.`members` RLIKE `d`.`alias_name`
		AND
			`d`.`domain_id` = `dmn`.`domain_id`
	";

	$rs = exec_query($sql, $query, array($als_id));
	if ($rs->fields['ftpnum'] > 0) {
		set_page_message(tr('Domain alias you are trying to remove has FTP accounts!<br>First remove them!'));
		user_goto('domains_manage.php');
	}

	$query = "
		UPDATE
			`domain_aliasses`
		SET
			`alias_status` = 'delete'
		WHERE
			`alias_id` = ?
	";

	$rs = exec_query($sql, $query, array($als_id));

	update_reseller_c_props(get_reseller_id($dmn_id));

	send_request();
	write_log($_SESSION['user_logged'].": delete alias ".$alias_name."!");
	set_page_message(tr('Alias scheduled for deletion!'));
	user_goto('domains_manage.php');
} else {
	user_goto('domains_manage.php');
}
