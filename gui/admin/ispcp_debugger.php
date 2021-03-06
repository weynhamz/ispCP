<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2011 by ispCP | http://isp-control.net
 * @version 	SVN: $Id$
 * @link 		http://isp-control.net
 * @author 		ispCP Team
 *
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 * Portions created by the ispCP Team are Copyright (C) 2006-2011 by
 * isp Control Panel. All Rights Reserved.
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

$cfg = ispCP_Registry::get('Config');

$exec_count = count_requests($sql, 'domain_status', 'domain');

$exec_count = $exec_count + count_requests(
	$sql, 'alias_status', 'domain_aliasses'
);

$exec_count = $exec_count + count_requests(
	$sql, 'subdomain_status', 'subdomain'
);

$exec_count = $exec_count + count_requests(
	$sql, 'subdomain_alias_status', 'subdomain_alias'
);

$exec_count = $exec_count + count_requests($sql, 'status', 'mail_users');
$exec_count = $exec_count + count_requests($sql, 'status', 'htaccess');
$exec_count = $exec_count + count_requests($sql, 'status', 'htaccess_groups');
$exec_count = $exec_count + count_requests($sql, 'status', 'htaccess_users');

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'ispcp_debugger.tpl';

if (isset($_GET['action'])) {
	if ($_GET['action'] == 'run_engine' && $exec_count > 0) {
		$code = send_request();
		set_page_message(
			tr('Daemon returned %d as status code', $code),
			'notice'
		);
	} elseif($_GET['action'] == 'change_status' &&
		(isset($_GET['id']) && isset($_GET['type']))) {

		switch ($_GET['type']) {
			case 'domain':
				$query = "
					UPDATE
						`domain`
					SET
						`domain_status` = 'change'
					WHERE
						`domain_id` = ?
				;";
				break;
			case 'alias':
				$query = "
					UPDATE
						`domain_aliasses`
					SET
						`alias_status` = 'change'
					WHERE
						`alias_id` = ?
				;";
				break;
			case 'subdomain':
				$query = "
					UPDATE
						`subdomain`
					SET
						`subdomain_status` = 'change'
					WHERE
						`subdomain_id` = ?
				;";
				break;
			case 'subdomain_alias':
				$query = "
					UPDATE
						`subdomain_alias`
					SET
						`subdomain_alias_status` = 'change'
					WHERE
						`subdomain_alias_id` = ?
				;";
				break;
			case 'mail':
				$query = "
					UPDATE
						`mail_users`
					SET
						`status` = 'change'
					WHERE
						`mail_id` = ?
				;";
				break;
			case 'htaccess':
			case 'htaccess_users':
			case 'htaccess_groups':
				$query = "
					UPDATE
						`". $_GET['type']."`
					SET
						`status` = 'change'
					WHERE
						`id` = ?
				;";
				break;
			default:
				set_page_message(tr('Unknown type!'), 'warning');
				user_goto('ispcp_debugger.php');
		}

		$rs = exec_query($sql, $query, $_GET['id']);

		if ($rs !== false) {
			set_page_message(tr('Done'), 'success');
			user_goto('ispcp_debugger.php');
		} else {
			$msg = tr('Unknown Error') . '<br />' . $sql->errorMsg();
			set_page_message($msg, 'error');
			user_goto('ispcp_debugger.php');
		}
	}
}

$errors = get_error_domains($sql, $tpl);
$errors += get_error_aliases($sql, $tpl);
$errors += get_error_subdomains($sql, $tpl);
$errors += get_error_alias_subdomains($sql, $tpl);
$errors += get_error_mails($sql, $tpl);
$errors += get_error_htaccess($sql, $tpl);

// static page messages
$tpl->assign(
	array(
		'TR_PAGE_TITLE' => tr('ispCP - Virtual Hosting Control System'),
		'TR_DEBUGGER_TITLE' => tr('ispCP debugger'),
		'TR_DOMAIN_ERRORS' => tr('Domain errors'),
		'TR_ALIAS_ERRORS' => tr('Domain alias errors'),
		'TR_SUBDOMAIN_ERRORS' => tr('Subdomain errors'),
		'TR_SUBDOMAIN_ALIAS_ERRORS' => tr('Alias subdomain errors'),
		'TR_MAIL_ERRORS' => tr('Mail account errors'),
		'TR_HTACCESS_ERRORS' => tr('.htaccess related errors'),
		'TR_DAEMON_TOOLS' => tr('ispCP Daemon tools'),
		'TR_EXEC_REQUESTS' => tr('Execute requests'),
		'TR_CHANGE_STATUS' => tr('Set status to \'change\''),
		'EXEC_COUNT' => $exec_count,
		'TR_ERRORS' => tr('%s Errors in database', $errors)
	)
);

gen_admin_mainmenu($tpl, 'main_menu_system_tools.tpl');
gen_admin_menu($tpl, 'menu_system_tools.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/**
 * Returns the number of requests that still to run
 *
 * @param  ispCP_Database $sql ispCP_Database instance
 * @param  string $statusField status database field name
 * @param  string $tableName ispCP database table name
 * @return int Number of request
 */
function count_requests($sql, $statusField, $tableName) {

	$cfg = ispCP_Registry::get('Config');

	$query = "
		SELECT
			`$statusField`
		FROM
			`$tableName`
		WHERE
			`$statusField` IN (?, ?, ?, ?, ?, ?, ?)
		;
	";

	$rs = exec_query(
			$sql, $query,
			array(
				$cfg->ITEM_ADD_STATUS, $cfg->ITEM_CHANGE_STATUS,
				$cfg->ITEM_DELETE_STATUS, $cfg->ITEM_RESTORE_STATUS,
				$cfg->ITEM_TOENABLE_STATUS, $cfg->ITEM_TODISABLED_STATUS,
				$cfg->ITEM_DNSCHANGE_STATUS

			)
	);

	$count = $rs->recordCount();

	return $count;
}

/**
 * Get domain errors generated by a engine request
 *
 * @param  ispCP_Database $sql ispCP_Database instance
 * @param  ispCP_TemplateEngine $tpl ispCP_TemplateEngine instance
 * @return void
 */
function get_error_domains($sql, $tpl) {

	$cfg = ispCP_Registry::get('Config');

	$ok_status = $cfg->ITEM_OK_STATUS;
	$disabled_status = $cfg->ITEM_DISABLED_STATUS;
	$delete_status = $cfg->ITEM_DELETE_STATUS;
	$add_status = $cfg->ITEM_ADD_STATUS;
	$restore_status = $cfg->ITEM_RESTORE_STATUS;
	$change_status = $cfg->ITEM_CHANGE_STATUS;
	$dnschange_status = $cfg->ITEM_DNSCHANGE_STATUS;
	$toenable_status = $cfg->ITEM_TOENABLE_STATUS;
	$todisable_status = $cfg->ITEM_TODISABLED_STATUS;

	$dmn_query = "
		SELECT
			`domain_name`, `domain_status`, `domain_id`
		FROM
			`domain`
		WHERE
			`domain_status` NOT IN (?, ?, ?, ?, ?, ?, ?, ?, ?)
		;
	";

	$rs = exec_query(
		$sql, $dmn_query,
		array(
			$ok_status, $disabled_status, $delete_status, $add_status,
			$restore_status, $change_status, $toenable_status,
			$todisable_status, $dnschange_status
		)
	);

	$errors = $rs->recordCount();

	if ($errors == 0) {
		$tpl->assign(
			array(
				'DOMAIN_LIST' => '',
				'TR_DOMAIN_MESSAGE' => tr('No domain system errors'),
			)
		);
	} else {
		$i = 1;
		while (!$rs->EOF) {
			$tpl->assign(
				array('CONTENT' => ($i % 2 == 0) ? 'content2' : 'content')
			);

			$tpl->assign(
				array(
					'DOMAIN_MESSAGE' => '',
					'TR_DOMAIN_NAME' => tohtml($rs->fields['domain_name']),
					'TR_DOMAIN_ERROR' => tohtml($rs->fields['domain_status']),
					'CHANGE_ID' => tohtml($rs->fields['domain_id']),
					'CHANGE_TYPE' => 'domain',
				)
			);


			$i++;
			$rs->moveNext();
		}
	}

	return $errors;
}

/**
 * Get domain aliases errors generated by a engine request
 *
 * @param  ispCP_Database $sql ispCP_Database instance
 * @param  ispCP_TemplateEngine $tpl ispCP_TemplateEngine instance
 * @return void
 */
function get_error_aliases($sql, $tpl) {

	$cfg = ispCP_Registry::get('Config');

	$ok_status = $cfg->ITEM_OK_STATUS;
	$disabled_status = $cfg->ITEM_DISABLED_STATUS;
	$delete_status = $cfg->ITEM_DELETE_STATUS;
	$add_status = $cfg->ITEM_ADD_STATUS;
	$restore_status = $cfg->ITEM_RESTORE_STATUS;
	$change_status = $cfg->ITEM_CHANGE_STATUS;
	$dnschange_status = $cfg->ITEM_DNSCHANGE_STATUS;
	$toenable_status = $cfg->ITEM_TOENABLE_STATUS;
	$todisable_status = $cfg->ITEM_TODISABLED_STATUS;
	$ordered_status = $cfg->ITEM_ORDERED_STATUS;

	$dmn_query = "
		SELECT
			`alias_name`, `alias_status`, `alias_id`
		FROM
			`domain_aliasses`
		WHERE
			`alias_status`
		NOT IN
			(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
		;
	";

	$rs = exec_query(
		$sql, $dmn_query,
		array(
			$ok_status, $disabled_status, $delete_status, $add_status,
			$restore_status, $change_status, $toenable_status, $todisable_status,
			$ordered_status, $dnschange_status
		)
	);

	$errors = $rs->recordCount();

	if ($errors == 0) {
		$tpl->assign(
			array(
				'ALIAS_LIST' => '',
				'TR_ALIAS_MESSAGE' => tr('No domain alias system errors'),
			)
		);

	} else {
		$i = 1;
		while (!$rs->EOF) {
			$tpl->assign(
				array(
					'CONTENT' => ($i % 2 == 0) ? 'content' : 'content2',
				)
			);

			$tpl->assign(
				array(
					'ALIAS_MESSAGE' => '',
					'TR_ALIAS_NAME' => tohtml($rs->fields['alias_name']),
					'TR_ALIAS_ERROR' => tohtml($rs->fields['alias_status']),
					'CHANGE_ID' => $rs->fields['alias_id'],
					'CHANGE_TYPE' => 'alias',
				)
			);


			$i++;
			$rs->moveNext();
		}
	}

	return $errors;
}

/**
 * Get subdomains errors generated by a engine request
 *
 * @param  ispCP_Database $sql ispCP_Database instance
 * @param  ispCP_TemplateEngine $tpl ispCP_TemplateEngine instance
 * @return void
 */
function get_error_subdomains($sql, $tpl) {

	$cfg = ispCP_Registry::get('Config');

	$ok_status = $cfg->ITEM_OK_STATUS;
	$disabled_status = $cfg->ITEM_DISABLED_STATUS;
	$delete_status = $cfg->ITEM_DELETE_STATUS;
	$add_status = $cfg->ITEM_ADD_STATUS;
	$restore_status = $cfg->ITEM_RESTORE_STATUS;
	$change_status = $cfg->ITEM_CHANGE_STATUS;
	$toenable_status = $cfg->ITEM_TOENABLE_STATUS;
	$todisable_status = $cfg->ITEM_TODISABLED_STATUS;

	$dmn_query = "
		SELECT
			`subdomain_name`, `subdomain_status`, `subdomain_id`
		FROM
			`subdomain`
		WHERE
			`subdomain_status`
		NOT IN
			(?, ?, ?, ?, ?, ?, ?, ?)
	";

	$rs = exec_query(
		$sql, $dmn_query,
		array(
			$ok_status, $disabled_status, $delete_status, $add_status,
			$restore_status, $change_status, $toenable_status, $todisable_status
		)
	);

	$errors = $rs->recordCount();

	if ($errors == 0) {
		$tpl->assign(
			array(
				'SUBDOMAIN_LIST' => '',
				'TR_SUBDOMAIN_MESSAGE' => tr('No subdomain system errors'),
			)
		);

	} else {
		$i = 1;
		while (!$rs->EOF) {
			$tpl->assign(
				array('CONTENT' => ($i % 2 == 0) ? 'content' : 'content2')
			);

			$tpl->assign(
				array(
					'SUBDOMAIN_MESSAGE' => '',
					'TR_SUBDOMAIN_NAME' => tohtml($rs->fields['subdomain_name']),
					'TR_SUBDOMAIN_ERROR' => tohtml(
						$rs->fields['subdomain_status']
					),
					'CHANGE_ID' => $rs->fields['subdomain_id'],
					'CHANGE_TYPE' => 'subdomain'
				)
			);


			$i++;
			$rs->moveNext();
		}
	}
	return $errors;
}

/**
 * Get domains aliases errors generated by a engine request
 *
 * @param  ispCP_Database $sql ispCP_Database instance
 * @param  ispCP_TemplateEngine $tpl ispCP_TemplateEngine instance
 * @return void
 */
function get_error_alias_subdomains($sql, $tpl) {

	$cfg = ispCP_Registry::get('Config');

	$ok_status = $cfg->ITEM_OK_STATUS;
	$disabled_status = $cfg->ITEM_DISABLED_STATUS;
	$delete_status = $cfg->ITEM_DELETE_STATUS;
	$add_status = $cfg->ITEM_ADD_STATUS;
	$restore_status = $cfg->ITEM_RESTORE_STATUS;
	$change_status = $cfg->ITEM_CHANGE_STATUS;
	$toenable_status = $cfg->ITEM_TOENABLE_STATUS;
	$todisable_status = $cfg->ITEM_TODISABLED_STATUS;

	$dmn_query = "
		SELECT
			`subdomain_alias_name`, `subdomain_alias_status`,
			`subdomain_alias_id`
		FROM
			`subdomain_alias`
		WHERE
			`subdomain_alias_status`
		NOT IN
			(?, ?, ?, ?, ?, ?, ?, ?)
		;
	";

	$rs = exec_query(
		$sql, $dmn_query,
		array(
			$ok_status, $disabled_status, $delete_status, $add_status,
			$restore_status, $change_status, $toenable_status, $todisable_status
		)
	);

	$errors = $rs->recordCount();

	if ($errors == 0) {
		$tpl->assign(
			array(
				'SUBDOMAIN_ALIAS_LIST' => '',
				'TR_SUBDOMAIN_ALIAS_MESSAGE' => tr(
					'No alias subdomain system errors'
				)
			)
		);

	} else {
		$i = 1;
		while (!$rs->EOF) {
			$tpl->assign(array('CONTENT' => ($i % 2 == 0) ? 'content' : 'content2'));

			$tpl->assign(
				array(
					'SUBDOMAIN_ALIAS_MESSAGE' => '',
					'TR_SUBDOMAIN_ALIAS_NAME' => tohtml(
						$rs->fields['subdomain_alias_name']
					),
					'TR_SUBDOMAIN_ALIAS_ERROR' => tohtml(
						$rs->fields['subdomain_alias_status']
					),
					'CHANGE_ID' => $rs->fields['subdomain_alias_id'],
					'CHANGE_TYPE' => 'subdomain_alias'
				)
			);


			$i++;
			$rs->moveNext();
		}
	}
	return $errors;
}

/**
 * Get mails errors generated by a engine request
 *
 * @param  ispCP_Database $sql ispCP_Database instance
 * @param  ispCP_TemplateEngine $tpl ispCP_TemplateEngine instance
 * @return void
 */
function get_error_mails($sql, $tpl) {

	$cfg = ispCP_Registry::get('Config');

	$ok_status = $cfg->ITEM_OK_STATUS;
	$disabled_status = $cfg->ITEM_DISABLED_STATUS;
	$delete_status = $cfg->ITEM_DELETE_STATUS;
	$add_status = $cfg->ITEM_ADD_STATUS;
	$restore_status = $cfg->ITEM_RESTORE_STATUS;
	$change_status = $cfg->ITEM_CHANGE_STATUS;
	$toenable_status = $cfg->ITEM_TOENABLE_STATUS;
	$todisable_status = $cfg->ITEM_TODISABLED_STATUS;
	$ordered_status = $cfg->ITEM_ORDERED_STATUS;

	$dmn_query = "
		SELECT
			`mail_acc`, `domain_id`, `mail_type`, `status`, `mail_id`
		FROM
			`mail_users`
		WHERE
			`status`
		NOT IN
			(?, ?, ?, ?, ?, ?, ?, ?, ?)
		;
	";

	$rs = exec_query(
		$sql, $dmn_query,
		array(
			$ok_status, $disabled_status, $delete_status, $add_status,
			$restore_status, $change_status, $toenable_status, $todisable_status,
			$ordered_status
		)
	);

	$errors = $rs->recordCount();

	if ($errors == 0) {
		$tpl->assign(
			array(
				'MAIL_LIST' => '',
				'TR_MAIL_MESSAGE' => tr('No email account system errors'),
			)
		);

	} else {
		$i = 1;
		while (!$rs->EOF) {
			$searched_id	= $rs->fields['domain_id'];
			$mail_acc		= $rs->fields['mail_acc'];
			$mail_type		= $rs->fields['mail_type'];
			$mail_id		= $rs->fields['mail_id'];
			$mail_status	= $rs->fields['status'];

			switch ($mail_type) {
				case 'normal_mail':
				case 'normal_forward':
				case 'normal_mail,normal_forward':
					$query = "
						SELECT
							CONCAT('@', `domain_name`) AS `domain_name`
						FROM
							`domain`
						WHERE
							`domain_id` = ?
						;
					";
					break;
				case 'subdom_mail':
				case 'subdom_forward':
				case 'subdom_mail,subdom_forward':
					$query = "
						SELECT
							CONCAT('@', `subdomain_name`, '.', IF(t2.`domain_name` IS NULL,'".tr('missing domain')."',t2.`domain_name`)) AS 'domain_name'
						FROM
							`subdomain` AS t1
						LEFT JOIN
							`domain` AS t2
						ON
							t1.`domain_id` = t2.`domain_id`
						WHERE
							`subdomain_id` = ?
						;
					";
					break;
				case 'alssub_mail':
				case 'alssub_forward':
				case 'alssub_mail,alssub_forward':
					$query = "
						SELECT
							CONCAT('@', t1.`subdomain_alias_name`, '.', IF(t2.`alias_name` IS NULL,'".tr('missing alias')."',t2.`alias_name`)) AS `domain_name`
						FROM
							`subdomain_alias` AS t1
						LEFT JOIN
							`domain_aliasses` AS t2
						ON
							t1.`alias_id` = t2.`alias_id`
						WHERE
							`subdomain_alias_id` = ?
						;
					";
					break;
				case 'normal_catchall':
				case 'alias_catchall':
				case 'alssub_catchall':
				case 'subdom_catchall':
					$query = "
						SELECT
							`mail_addr` AS `domain_name`
						FROM
							`mail_users`
						WHERE
							`mail_id` = ?
						;
					";
					$searched_id	= $mail_id;
					$mail_acc		= '';
					break;
				case 'alias_mail':
				case 'alias_forward':
				case 'alias_mail,alias_forward':
					$query = "
						SELECT
							CONCAT('@', `alias_name`) AS `domain_name`
						FROM
							`domain_aliasses`
						WHERE
							`alias_id` = ?
						;
					";
					break;
				default:
					write_log(
						sprintf(
							'FIXME: %s:%d' . "\n" . 'Unknown mail type %s',
							__FILE__, __LINE__, $mail_type
						)
					);

					throw new ispCP_Exception(
						'FIXME: ' . __FILE__ . ':' . __LINE__ . ' ' . $mail_type
					);
			}

			$sr = exec_query($sql, $query, $searched_id);
			$domain_name = $sr->fields['domain_name'];

			$tpl->assign(
				array(
					'CONTENT' => ($i % 2 == 0) ? 'content' : 'content2',
					)
			);

			$tpl->assign(
				array(
					'MAIL_MESSAGE'	=> '',
					'TR_MAIL_NAME'	=> tohtml(
						$mail_acc . ($domain_name == '' ? '@ ' . tr('orphan entry') : $domain_name)
					),
					'TR_MAIL_ERROR'	=> tohtml($mail_status),
					'CHANGE_ID'		=> $mail_id,
					'CHANGE_TYPE' => 'mail',
				)
			);


			$i++;
			$rs->moveNext();
		}
	}
	return $errors;
}

/**
 * @param ispCP_Database $sql
 * @param ispCP_TemplateEngine $tpl
 * @return int number of errors
 */
function get_error_htaccess($sql, $tpl) {

	$cfg = ispCP_Registry::get('Config');

	$ok_status = $cfg->ITEM_OK_STATUS;
	$delete_status = $cfg->ITEM_DELETE_STATUS;
	$add_status = $cfg->ITEM_ADD_STATUS;
	$change_status = $cfg->ITEM_CHANGE_STATUS;

	$dmn_query = "
		SELECT
			`id`, `dmn_id`, `status`, 'htaccess' as `type`, `domain_name`
		FROM
			`htaccess`
		LEFT JOIN
			`domain`
		ON
			`dmn_id` = `domain_id`
		WHERE
			`status`
		NOT IN
			(?, ?, ?, ?)
		UNION
		SELECT
			`id`, `dmn_id`, `status`, 'htaccess_groups' as `type`, `domain_name`
		FROM
			`htaccess_groups`
		LEFT JOIN
			`domain`
		ON
			`dmn_id` = `domain_id`
		WHERE
			`status`
		NOT IN
			(?, ?, ?, ?)
		UNION
		SELECT
			`id`, `dmn_id`, `status`, 'htaccess_users' as `type`, `domain_name`
		FROM
			`htaccess_users`
		LEFT JOIN
			`domain`
		ON
			`dmn_id` = `domain_id`
		WHERE
			`status`
		NOT IN
			(?, ?, ?, ?)
		;
	";

	$rs = exec_query(
		$sql, $dmn_query,
		array(
			$ok_status, $delete_status, $add_status, $change_status,
			$ok_status, $delete_status, $add_status, $change_status,
			$ok_status, $delete_status, $add_status, $change_status
		)
	);

	$errors = $rs->recordCount();

	if ($errors == 0) {
		$tpl->assign(
			array(
				'HTACCESS_LIST' => '',
				'TR_HTACCESS_MESSAGE' => tr('No htaccess related system errors'),
			)
		);

	} else {
		$i = 1;
		while (!$rs->EOF) {

			$tpl->assign(
				array(
					'CONTENT' => ($i % 2 == 0) ? 'content' : 'content2',
					)
			);

			$tpl->assign(
				array(
					'HTACCESS_MESSAGE'	=> '',
					'TR_HTACCESS_NAME'	=> $rs->fields['domain_name'] == null ? tr('Missing domain') : tohtml($rs->fields['domain_name']) ,
					'TR_HTACCESS_ERROR'	=> tohtml($rs->fields['status']),
					'CHANGE_ID'		=> $rs->fields['id'],
					'CHANGE_TYPE' => $rs->fields['type'],
				)
			);


			$i++;
			$rs->moveNext();
		}
	}

	return $errors;
}
?>