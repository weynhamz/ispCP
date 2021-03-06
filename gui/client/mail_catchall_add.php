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

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'mail_catchall_add.tpl';

if (isset($_GET['id'])) {
	$item_id = $_GET['id'];
} else if (isset($_POST['id'])) {
	$item_id = $_POST['id'];
} else {
	user_goto('mail_catchall.php');
}

// dynamic page data.

gen_dynamic_page_data($tpl, $sql, $item_id);
create_catchall_mail_account($sql, $item_id);
$tpl->assign('ID', $item_id);

// static page messages.
gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'						=> tr('ispCP - Client/Create CatchAll Mail Account'),
		'TR_CREATE_CATCHALL_MAIL_ACCOUNT'	=> tr('Create catch all mail account'),
		'TR_MAIL_LIST'						=> tr('Mail accounts list'),
		'TR_CREATE_CATCHALL'				=> tr('Create catch all'),
		'TR_FORWARD_MAIL'					=> tr('Forward mail'),
		'TR_FORWARD_TO'						=> tr('Forward to')
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

/**
 * @param ispCP_TemplateEngine $tpl
 * @param ispCP_Database $sql
 * @param int $id
 */
function gen_dynamic_page_data($tpl, $sql, $id) {

	global $domain_id;
	$cfg = ispCP_Registry::get('Config');

	list($dmn_id,,,,,,,,$dmn_mailacc_limit) = get_domain_default_props($sql, $_SESSION['user_id']);

	$domain_id = $dmn_id;

	list($mail_acc_cnt) = get_domain_running_mail_acc_cnt($sql, $dmn_id);

	if ($dmn_mailacc_limit != 0 && $mail_acc_cnt >= $dmn_mailacc_limit) {
		set_page_message(tr('Mail accounts limit reached!'), 'warning');
		user_goto('mail_catchall.php');
	}

	$ok_status = $cfg->ITEM_OK_STATUS;
	$match = array();
	if (preg_match("/(\d+);(normal|alias|subdom|alssub)/", $id, $match) == 1) {
		$item_id = $match[1];
		$item_type = $match[2];

		if ($item_type === 'normal') {
			$query = "
				SELECT
					t1.`mail_id`, t1.`mail_type`, t2.`domain_name`, t1.`mail_acc`
				FROM
					`mail_users` AS t1,
					`domain` AS t2
				WHERE
					t1.`domain_id` = ?
				AND
					t2.`domain_id` = ?
				AND
					t1.`sub_id` = '0'
				AND
					t1.`status` = ?
				ORDER BY
					t1.`mail_type` DESC, t1.`mail_acc`
			";

			$rs = exec_query($sql, $query, array($item_id, $item_id, $ok_status));
			if ($rs->recordCount() == 0) {
				$tpl->assign(array('FORWARD_MAIL' => $cfg->HTML_CHECKED, 'MAIL_LIST' => '', 'DEFAULT' => 'forward'));
			} else {
				$tpl->assign(array('NORMAL_MAIL' => $cfg->HTML_CHECKED, 'NORMAL_MAIL_CHECK' => 'checked', 'FORWARD_MAIL' => '', 'DEFAULT' => 'normal'));

				while (!$rs->EOF) {
					$show_mail_acc = decode_idna($rs->fields['mail_acc']);
					$show_domain_name = decode_idna($rs->fields['domain_name']);
					$mail_acc = $rs->fields['mail_acc'];
					$domain_name = $rs->fields['domain_name'];
					$tpl->assign(
						array(
							'MAIL_ID'				=> $rs->fields['mail_id'],
							'MAIL_ACCOUNT'			=> tohtml($show_mail_acc . "@" . $show_domain_name), // this will be shown in the templates
							'MAIL_ACCOUNT_PUNNY'	=> tohtml($mail_acc . "@" . $domain_name) // this will be updated if we create catch all
						)
					);

					$rs->moveNext();
				}
			}
		} else if ($item_type === 'alias') {
			$query = "
				SELECT
					t1.`mail_id`, t1.`mail_type`, t2.`alias_name`, t1.`mail_acc`
				FROM
					`mail_users` AS t1,
					`domain_aliasses` AS t2
				WHERE
					t1.`sub_id` = t2.`alias_id`
				AND
					t1.`status` = ?
				AND
					t1.`mail_type` LIKE 'alias_%'
				AND
					t2.`alias_id` = ?
				ORDER BY
					t1.`mail_type` DESC, t1.`mail_acc`
			";

			$rs = exec_query($sql, $query, array($ok_status, $item_id));

			if ($rs->recordCount() == 0) {
				$tpl->assign(array('FORWARD_MAIL' => $cfg->HTML_CHECKED, 'MAIL_LIST' => '', 'DEFAULT' => 'forward'));
			} else {
				$tpl->assign(array('NORMAL_MAIL' => $cfg->HTML_CHECKED, 'NORMAL_MAIL_CHECK' => 'checked', 'FORWARD_MAIL' => '', 'DEFAULT' => 'normal'));

				while (!$rs->EOF) {
					$show_mail_acc = decode_idna($rs->fields['mail_acc']);
					$show_alias_name = decode_idna($rs->fields['alias_name']);
					$mail_acc = $rs->fields['mail_acc'];
					$alias_name = $rs->fields['alias_name'];
					$tpl->assign(
						array(
							'MAIL_ID'				=> $rs->fields['mail_id'],
							'MAIL_ACCOUNT'			=> tohtml($show_mail_acc . "@" . $show_alias_name), // this will be shown in the templates
							'MAIL_ACCOUNT_PUNNY'	=> tohtml($mail_acc . "@" . $alias_name) // this will be updated if we create catch all
						)
					);

					$rs->moveNext();
				}
			}
		} else if ($item_type === 'subdom') {
			$query = "
				SELECT
					t1.`mail_id`, t1.`mail_type`, CONCAT(t2.`subdomain_name`, '.', t3.`domain_name`) AS subdomain_name, t1.`mail_acc`
				FROM
					`mail_users` AS t1,
					`subdomain` AS t2,
					`domain` AS t3
				WHERE
					t1.`sub_id` = t2.`subdomain_id`
				AND
					t2.`domain_id` = t3.`domain_id`
				AND
					t1.`status` = ?
				AND
					t1.`mail_type` LIKE 'subdom_%'
				AND
					t2.`subdomain_id` = ?
				ORDER BY
					t1.`mail_type` DESC, t1.`mail_acc`
			";

			$rs = exec_query($sql, $query, array($ok_status, $item_id));

			if ($rs->recordCount() == 0) {
				$tpl->assign(array('FORWARD_MAIL' => $cfg->HTML_CHECKED, 'MAIL_LIST' => '', 'DEFAULT' => 'forward'));
			} else {
				$tpl->assign(array('NORMAL_MAIL' => $cfg->HTML_CHECKED, 'NORMAL_MAIL_CHECK' => 'checked', 'FORWARD_MAIL' => '', 'DEFAULT' => 'normal'));

				while (!$rs->EOF) {
					$show_mail_acc = decode_idna($rs->fields['mail_acc']);
					$show_alias_name = decode_idna($rs->fields['subdomain_name']);
					$mail_acc = $rs->fields['mail_acc'];
					$alias_name = $rs->fields['subdomain_name'];
					$tpl->assign(
						array(
							'MAIL_ID'				=> $rs->fields['mail_id'],
							'MAIL_ACCOUNT'			=> tohtml($show_mail_acc . "@" . $show_alias_name), // this will be shown in the templates
							'MAIL_ACCOUNT_PUNNY'	=> tohtml($mail_acc . "@" . $alias_name) // this will be updated if we create catch all
						)
					);

					$rs->moveNext();
				}
			}
		} else if ($item_type === 'alssub') {
			$query = "
				SELECT
					t1.`mail_id`, t1.`mail_type`, CONCAT(t2.`subdomain_alias_name`, '.', t3.`alias_name`) AS subdomain_name, t1.`mail_acc`
				FROM
					`mail_users` AS t1,
					`subdomain_alias` AS t2,
					`domain_aliasses` AS t3
				WHERE
					t1.`sub_id` = t2.`subdomain_alias_id`
				AND
					t2.`alias_id` = t3.`alias_id`
				AND
					t1.`status` = ?
				AND
					t1.`mail_type` LIKE 'alssub_%'
				AND
					t2.`subdomain_alias_id` = ?
				ORDER BY
					t1.`mail_type` DESC, t1.`mail_acc`
			";

			$rs = exec_query($sql, $query, array($ok_status, $item_id));

			if ($rs->recordCount() == 0) {
				$tpl->assign(array('FORWARD_MAIL' => $cfg->HTML_CHECKED, 'MAIL_LIST' => '', 'DEFAULT' => 'forward'));
			} else {
				$tpl->assign(array('NORMAL_MAIL' => $cfg->HTML_CHECKED, 'NORMAL_MAIL_CHECK' => 'checked', 'FORWARD_MAIL' => '', 'DEFAULT' => 'normal'));

				while (!$rs->EOF) {
					$show_mail_acc = decode_idna($rs->fields['mail_acc']);
					$show_alias_name = decode_idna($rs->fields['subdomain_name']);
					$mail_acc = $rs->fields['mail_acc'];
					$alias_name = $rs->fields['subdomain_name'];
					$tpl->assign(
						array(
							'MAIL_ID'				=> $rs->fields['mail_id'],
							'MAIL_ACCOUNT'			=> tohtml($show_mail_acc . "@" . $show_alias_name), // this will be shown in the templates
							'MAIL_ACCOUNT_PUNNY'	=> tohtml($mail_acc . "@" . $alias_name) // this will be updated if we create catch all
						)
					);

					$rs->moveNext();
				}
			}
		}
	} else {
		user_goto('mail_catchall.php');
	}
}

function create_catchall_mail_account($sql, $id) {

	$cfg = ispCP_Registry::get('Config');

	list($realId, $type) = explode(';', $id);
	// Check if user is owner of the domain
	if (!preg_match('(normal|alias|subdom|alssub)', $type) || who_owns_this($realId, $type) != $_SESSION['user_id']) {
		set_page_message(
			tr('User does not exist or you do not have permission to access this interface!'),
			'error'
		);
		user_goto('mail_catchall.php');
	}

	$match = array();
	if (isset($_POST['uaction']) && $_POST['uaction'] === 'create_catchall' && $_POST['mail_type'] === 'normal') {
		if (preg_match("/(\d+);(normal|alias|subdom|alssub)/", $id, $match) == 1) {
			$item_type = $match[2];
			$post_mail_id = $_POST['mail_id'];

			if (preg_match("/(\d+);([^;]+);/", $post_mail_id, $match) == 1) {
				$mail_id = $match[1];
				$mail_acc = $match[2];

				if ($item_type === 'normal') {
					$mail_type = 'normal_catchall';
				} elseif ($item_type === 'alias') {
					$mail_type = 'alias_catchall';
				} elseif ($item_type === 'subdom') {
					$mail_type = 'subdom_catchall';
				} elseif ($item_type === 'alssub') {
					$mail_type = 'alssub_catchall';
				}

				$query = "
					SELECT
						`domain_id`, `sub_id`
					FROM
						`mail_users`
					WHERE
						`mail_id` = ?
				";

				$rs = exec_query($sql, $query, $mail_id);
				$domain_id = $rs->fields['domain_id'];
				$sub_id = $rs->fields['sub_id'];
				$status = $cfg->ITEM_ADD_STATUS;

				// find the mail_addr (catchall -> "@(sub/alias)domain.tld", should be domain part of mail_acc
				$match = explode('@', $mail_acc);
				$mail_addr = '@' . $match[1];

				$query = "
					INSERT INTO `mail_users`
						(`mail_acc`,
						`mail_pass`,
						`mail_forward`,
						`domain_id`,
						`mail_type`,
						`sub_id`,
						`status`,
						`mail_auto_respond`,
						`quota`,
						`mail_addr`)
					VALUES
						(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				";

				exec_query($sql, $query, array($mail_acc, '_no_', '_no_', $domain_id, $mail_type, $sub_id, $status, '_no_', NULL, $mail_addr));

				send_request();
				write_log($_SESSION['user_logged'] . ": adds new email catch all");
				set_page_message(
					tr('Catch all account scheduled for creation!'),
					'success'
				);
				user_goto('mail_catchall.php');
			} else {
				user_goto('mail_catchall.php');
			}
		}
	} else if (isset($_POST['uaction']) && $_POST['uaction'] === 'create_catchall' && $_POST['mail_type'] === 'forward' && isset($_POST['forward_list'])) {
		if (preg_match("/(\d+);(normal|alias|subdom|alssub)/", $id, $match) == 1) {
			$item_id = $match[1];
			$item_type = $match[2];

			if ($item_type === 'normal') {
				$mail_type = 'normal_catchall';
				$sub_id = '0';
				$domain_id = $item_id;
				$query = "SELECT `domain_name` FROM `domain` WHERE `domain_id` = ?";
				$rs = exec_query($sql, $query, $domain_id);
				$mail_addr = '@' . $rs->fields['domain_name'];
			} elseif ($item_type === 'alias') {
				$mail_type = 'alias_catchall';
				$sub_id = $item_id;
				$query = "SELECT `domain_aliasses`.`domain_id`, `alias_name` FROM `domain_aliasses` WHERE `alias_id` = ?";
				$rs = exec_query($sql, $query, $item_id);
				$domain_id = $rs->fields['domain_id'];
				$mail_addr = '@' . $rs->fields['alias_name'];
			} elseif ($item_type === 'subdom') {
				$mail_type = 'subdom_catchall';
				$sub_id = $item_id;
				$query = "SELECT `subdomain`.`domain_id`, `subdomain_name`, `domain_name` FROM `subdomain`, `domain`
					WHERE `subdomain_id` = ? AND `domain`.`domain_id` = `subdomain`.`domain_id`";
				$rs = exec_query($sql, $query, $item_id);
				$domain_id = $rs->fields['domain_id'];
				$mail_addr = '@' . $rs->fields['subdomain_name'] . '.' . $rs->fields['domain_name'];
			} elseif ($item_type === 'alssub') {
				$mail_type = 'alssub_catchall';
				$sub_id = $item_id;
				$query = "
					SELECT
						t1.`subdomain_alias_name`,
						t2.`alias_name`,
						t2.`domain_id`
					FROM
						`subdomain_alias` AS t1,
						`domain_aliasses` AS t2
					WHERE
						t1.`subdomain_alias_id` = ?
					AND
						t1.`alias_id` = t2.`alias_id`
					";
				$rs = exec_query($sql, $query, $item_id);
				$domain_id = $rs->fields['domain_id'];
				$mail_addr = '@' . $rs->fields['subdomain_alias_name'] . '.' . $rs->fields['alias_name'];
			}
			$mail_forward = clean_input($_POST['forward_list']);
			$mail_acc = array();
			$faray = preg_split ("/[\n,]+/", $mail_forward);

			foreach ($faray as $value) {
				$value = trim($value);
				if (!chk_email($value) && $value !== '' || $value === '') {
					// @todo ERROR .. strange :) not email in this line - warning
					set_page_message(tr("Mail forward list error!"), 'error');
					return;
				}
				$mail_acc[] = $value;
			}

			$status = $cfg->ITEM_ADD_STATUS;

			$query = "
				INSERT INTO `mail_users`
					(`mail_acc`,
					`mail_pass`,
					`mail_forward`,
					`domain_id`,
					`mail_type`,
					`sub_id`,
					`status`,
					`mail_auto_respond`,
					`quota`,
					`mail_addr`)
				VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			";

			exec_query($sql, $query, array(implode(',', $mail_acc), '_no_', '_no_', $domain_id, $mail_type, $sub_id, $status, '_no_', NULL, $mail_addr));

			send_request();
			write_log($_SESSION['user_logged'] . ": adds new email catch all ");
			set_page_message(
				tr('Catch all account scheduled for creation!'),
				'success'
			);
			user_goto('mail_catchall.php');
		} else {
			user_goto('mail_catchall.php');
		}
	}
}
?>