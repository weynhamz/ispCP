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


/**
 * @todo use DB prepared statements!
 */
require '../include/ispcp-lib.php';

check_login(__FILE__);

$cfg = ispCP_Registry::get('Config');

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'mail_catchall.tpl';


// dynamic page data.
if (isset($_SESSION['email_support']) && $_SESSION['email_support'] == "no") {
	$tpl->assign('NO_MAILS', '');
}

gen_page_lists($tpl, $sql, $_SESSION['user_id']);

// static page messages.
gen_logged_from($tpl);
check_permissions($tpl);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'				=> tr('ispCP - Client/Manage Users'),
		'TR_STATUS'					=> tr('Status'),
		'TR_ACTION'					=> tr('Action'),
		'TR_CATCHALL_MAIL_USERS'	=> tr('Catch all account'),
		'TR_DOMAIN'					=> tr('Domain'),
		'TR_CATCHALL'				=> tr('Catch all'),
		'TR_MESSAGE_DELETE'			=> tr('Are you sure you want to delete %s?', true, '%s')
	)
);

gen_client_mainmenu($tpl, 'main_menu_email_accounts.tpl');
gen_client_menu($tpl, 'menu_email_accounts.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

// page functions.
function gen_user_mail_action($mail_id, $mail_status) {

	$cfg = ispCP_Registry::get('Config');

	if ($mail_status === $cfg->ITEM_OK_STATUS) {
		return array(tr('Delete'), "mail_delete.php?id=$mail_id", "mail_edit.php?id=$mail_id");
	} else {
		return array(tr('N/A'), '#', '#');
	}
}

function gen_user_catchall_action($mail_id, $mail_status) {

	$cfg = ispCP_Registry::get('Config');

	if ($mail_status === $cfg->ITEM_ADD_STATUS) {
		return array(tr('N/A'), '#'); // Addition in progress
	} else if ($mail_status === $cfg->ITEM_OK_STATUS) {
		return array(tr('Delete CatchAll'), "mail_catchall_delete.php?id=$mail_id");
	} else if ($mail_status === $cfg->ITEM_CHANGE_STATUS) {
		return array(tr('N/A'), '#');
	} else if ($mail_status === $cfg->ITEM_DELETE_STATUS) {
		return array(tr('N/A'), '#');
	} else {
		return null;
	}
}

/**
 * @param ispCP_TemplateEngine $tpl
 * @param string $action
 * @param int $dmn_id
 * @param string $dmn_name
 * @param int $mail_id
 * @param string $mail_acc
 * @param string $mail_status
 * @param string $ca_type
 */
function gen_catchall_item($tpl, $action, $dmn_id, $dmn_name, $mail_id, $mail_acc, $mail_status, $ca_type) {
	$show_dmn_name = decode_idna($dmn_name);

	if ($action === 'create') {
		$tpl->append(
			array(
				'CATCHALL_DOMAIN'			=> tohtml($show_dmn_name),
				'CATCHALL_ACC'				=> tr('None'),
				'CATCHALL_STATUS'			=> tr('N/A'),
				'CATCHALL_ACTION'			=> tr('Create catch all'),
				'CATCHALL_ACTION_SCRIPT'	=> "mail_catchall_add.php?id=$dmn_id;$ca_type"
			)
		);
	} else {
		list($catchall_action, $catchall_action_script) = gen_user_catchall_action($mail_id, $mail_status);

		$show_dmn_name = decode_idna($dmn_name);
		$show_mail_acc = decode_idna($mail_acc);

		$tpl->append(
			array(
				'CATCHALL_DOMAIN' => tohtml($show_dmn_name),
				'CATCHALL_ACC' => tohtml($show_mail_acc),
				'CATCHALL_STATUS' => translate_dmn_status($mail_status),
				'CATCHALL_ACTION' => $catchall_action,
				'CATCHALL_ACTION_SCRIPT' => $catchall_action_script
			)
		);
	}
}

/**
 * @todo use db prepared statements
 * @param ispCP_TemplateEngine $tpl
 * @param ispCP_Database $sql
 * @param int $dmn_id
 * @param string $dmn_name
 */
function gen_page_catchall_list($tpl, $sql, $dmn_id, $dmn_name) {
	global $counter;

	$tpl->assign('CATCHALL_MESSAGE', '');

		$query = "
			SELECT
				`mail_id`, `mail_acc`, `status`
			FROM
				`mail_users`
			WHERE
				`domain_id` = '$dmn_id'
			AND
				`sub_id` = 0
			AND
				`mail_type` = 'normal_catchall'
		";

		$rs = execute_query($sql, $query);

		if ($rs->recordCount() == 0) {
			gen_catchall_item($tpl, 'create', $dmn_id, $dmn_name, '', '', '', 'normal');
		} else {
			gen_catchall_item($tpl,
				'delete',
				$dmn_id,
				$dmn_name,
				$rs->fields['mail_id'],
				$rs->fields['mail_acc'],
				$rs->fields['status'], 'normal');
		}
		$tpl->assign(
			array(
				'ITEM_CLASS' => 'content',
			)
		);

		$query = "
			SELECT
				`alias_id`, `alias_name`
			FROM
				`domain_aliasses`
			WHERE
				`domain_id` = '$dmn_id'
			AND
				`alias_status` = 'ok'
		";

		$rs = execute_query($sql, $query);

		while (!$rs->EOF) {
			$tpl->assign('ITEM_CLASS', ($counter % 2 == 0) ? 'content2' : 'content');

			$als_id = $rs->fields['alias_id'];

			$als_name = $rs->fields['alias_name'];

			$query = "
				SELECT
					`mail_id`, `mail_acc`, `status`
				FROM
					`mail_users`
				WHERE
					`domain_id` = '$dmn_id'
				AND
					`sub_id` = '$als_id'
				AND
					`mail_type` = 'alias_catchall'
			";

			$rs_als = execute_query($sql, $query);

			if ($rs_als->recordCount() == 0) {
				gen_catchall_item($tpl, 'create', $als_id, $als_name, '', '', '', 'alias');
			} else {
				gen_catchall_item(
					$tpl,
					'delete',
					$als_id,
					$als_name,
					$rs_als->fields['mail_id'],
					$rs_als->fields['mail_acc'],
					$rs_als->fields['status'], 'alias'
				);
			}

			$rs->moveNext();
			$counter++;
		}

		$query = "
			SELECT
				a.`subdomain_alias_id`, CONCAT(a.`subdomain_alias_name`,'.',b.`alias_name`) AS `subdomain_name`
			FROM
				`subdomain_alias` AS a, `domain_aliasses` AS b
			WHERE
				b.`alias_id` IN (SELECT `alias_id` FROM `domain_aliasses` WHERE `domain_id` = '$dmn_id')
			AND
				a.`alias_id` = b.`alias_id`
			AND
				a.`subdomain_alias_status` = 'ok'
		";

		$rs = execute_query($sql, $query);

		while (!$rs->EOF) {
			$tpl->assign('ITEM_CLASS', ($counter % 2 == 0) ? 'content2' : 'content');

			$als_id = $rs->fields['subdomain_alias_id'];

			$als_name = $rs->fields['subdomain_name'];

			$query = "
				SELECT
					`mail_id`, `mail_acc`, `status`
				FROM
					`mail_users`
				WHERE
					`domain_id` = '$dmn_id'
				AND
					`sub_id` = '$als_id'
				AND
					`mail_type` = 'alssub_catchall'
			";

			$rs_als = execute_query($sql, $query);

			if ($rs_als->recordCount() == 0) {
				gen_catchall_item($tpl, 'create', $als_id, $als_name, '', '', '', 'alssub');
			} else {
				gen_catchall_item(
					$tpl,
					'delete',
					$als_id,
					$als_name,
					$rs_als->fields['mail_id'],
					$rs_als->fields['mail_acc'],
					$rs_als->fields['status'], 'alssub'
				);
			}

			$rs->moveNext();
			$counter++;
		}

		$query = "
			SELECT
				a.`subdomain_id`, CONCAT(a.`subdomain_name`,'.',b.`domain_name`) AS `subdomain_name`
			FROM
				`subdomain` AS a, `domain` AS b
			WHERE
				a.`domain_id` = '$dmn_id'
			AND
				a.`domain_id` = b.`domain_id`
			AND
				a.`subdomain_status` = 'ok'
		";

		$rs = execute_query($sql, $query);

		while (!$rs->EOF) {
			$tpl->assign('ITEM_CLASS', ($counter % 2 == 0) ? 'content2' : 'content');

			$als_id = $rs->fields['subdomain_id'];

			$als_name = $rs->fields['subdomain_name'];

			$query = "
				SELECT
					`mail_id`, `mail_acc`, `status`
				FROM
					`mail_users`
				WHERE
					`domain_id` = '$dmn_id'
				AND
					`sub_id` = '$als_id'
				AND
					`mail_type` = 'subdom_catchall'
			";

			$rs_als = execute_query($sql, $query);

			if ($rs_als->recordCount() == 0) {
				gen_catchall_item($tpl, 'create', $als_id, $als_name, '', '', '', 'subdom');
			} else {
				gen_catchall_item($tpl,
					'delete',
					$als_id,
					$als_name,
					$rs_als->fields['mail_id'],
					$rs_als->fields['mail_acc'],
					$rs_als->fields['status'], 'subdom');
			}

			$rs->moveNext();
			$counter++;
		}
}

function gen_page_lists($tpl, $sql, $user_id)
{
	list($dmn_id,$dmn_name) = get_domain_default_props($sql, $user_id);

	gen_page_catchall_list($tpl, $sql, $dmn_id, $dmn_name);
}
?>