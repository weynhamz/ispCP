<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2011 by ispCP | http://isp-control.net
 * @version 	SVN: $Id: orders_detailst.php 3711 2010-12-11 11:38:54Z ShadowJumper $
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

// Begin page line
require '../include/ispcp-lib.php';

check_login(__FILE__);

$cfg = ispCP_Registry::get('Config');

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'orders_details.tpl';

if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
	$order_id = $_GET['order_id'];
} else {
	set_page_message(tr('Wrong order ID!'), 'error');
	user_goto('orders.php');
}

if (isset($_POST['uaction'])) {
	update_order_details($tpl, $sql, $_SESSION['user_id'], $order_id);

	if ($_POST['uaction'] === 'update_data') {
		set_page_message(tr('Order data updated successfully!'), 'success');
	} else if ($_POST['uaction'] === 'add_user') {
		$_SESSION['domain_ip'] = @$_POST['domain_ip'];
		user_goto('orders_add.php?order_id=' . $order_id);
	}
}

gen_order_details($tpl, $sql, $_SESSION['user_id'], $order_id);

// static page messages
gen_logged_from($tpl);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'				=> tr('ispCP - Reseller/Order details'),
		'TR_MANAGE_ORDERS'			=> tr('Manage Orders'),
		'TR_DATE'					=> tr('Order date'),
		'TR_HP'						=> tr('Hosting plan'),
		'TR_HOSTING_INFO'			=> tr('Hosting details'),
		'TR_DOMAIN'					=> tr('Domain'),
		'TR_FIRST_NAME'				=> tr('First name'),
		'TR_LAST_NAME'				=> tr('Last name'),
		'TR_GENDER'					=> tr('Gender'),
		'TR_MALE'					=> tr('Male'),
		'TR_FEMALE'					=> tr('Female'),
		'TR_UNKNOWN'				=> tr('Unknown'),
		'TR_COMPANY'				=> tr('Company'),
		'TR_ZIP_POSTAL_CODE'		=> tr('Zip/Postal code'),
		'TR_CITY'					=> tr('City'),
		'TR_STATE'					=> tr('State/Province'),
		'TR_COUNTRY'				=> tr('Country'),
		'TR_STREET_1'				=> tr('Street 1'),
		'TR_STREET_2'				=> tr('Street 2'),
		'TR_EMAIL'					=> tr('Email'),
		'TR_PHONE'					=> tr('Phone'),
		'TR_FAX'					=> tr('Fax'),
		'TR_UPDATE_DATA'			=> tr('Update data'),
		'TR_ORDER_DETAILS'			=> tr('Order details'),
		'TR_CUSTOMER_DATA'			=> tr('Customer data'),
		'TR_DELETE_ORDER'			=> tr('Delete order'),
		'TR_DMN_IP'					=> tr('Domain IP'),
		'TR_CUSTOMER_ID'			=> tr('Customer ID'),
		'TR_MESSAGE_DELETE_ACCOUNT'	=> tr('Are you sure you want to delete this order?', true),
		'TR_ADD'					=> tr('Add to the system')
	)
);

gen_reseller_mainmenu($tpl, 'main_menu_orders.tpl');
gen_reseller_menu($tpl, 'menu_orders.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/*
 * Functions
 */

/**
 * @param ispCP_TemplateEngine $tpl
 * @param ispCP_Database $sql
 * @param int $user_id
 * @param int $order_id
 */
function gen_order_details($tpl, $sql, $user_id, $order_id) {
	$cfg = ispCP_Registry::get('Config');

	$query = "
		SELECT
			*
		FROM
			`orders`
		WHERE
			`id` = ?
		AND
			`user_id` = ?
	";
	$rs = exec_query($sql, $query, array($order_id, $user_id));
	if ($rs->recordCount() == 0) {
		set_page_message(tr('Permission deny!'), 'error');
		user_goto('orders.php');
	}
	$plan_id = $rs->fields['plan_id'];

	$date = date($cfg->DATE_FORMAT, $rs->fields['date']);

	if (isset($_POST['uaction'])) {
		$domain_name	= $_POST['domain'];
		$customer_id	= $_POST['customer_id'];
		$fname			= $_POST['fname'];
		$lname			= $_POST['lname'];
		$gender			= $_POST['gender'];
		$firm			= $_POST['firm'];
		$zip			= $_POST['zip'];
		$city			= $_POST['city'];
		$state			= $_POST['state'];
		$country		= $_POST['country'];
		$street1		= $_POST['street1'];
		$street2		= $_POST['street2'];
		$email			= $_POST['email'];
		$phone			= $_POST['phone'];
		$fax			= $_POST['fax'];
	} else {
		$domain_name	= $rs->fields['domain_name'];
		$customer_id	= $rs->fields['customer_id'];
		$fname			= $rs->fields['fname'];
		$lname			= $rs->fields['lname'];
		$gender			= $rs->fields['gender'];
		$firm			= $rs->fields['firm'];
		$zip			= $rs->fields['zip'];
		$city			= $rs->fields['city'];
		$state			= $rs->fields['state'];
		$country		= $rs->fields['country'];
		$email			= $rs->fields['email'];
		$phone			= $rs->fields['phone'];
		$fax			= $rs->fields['fax'];
		$street1		= $rs->fields['street1'];
		$street2		= $rs->fields['street2'];
	}
	$query = "
		SELECT
			`name`, `description`
		FROM
			`hosting_plans`
		WHERE
			`id` = ?
	";
	$rs = exec_query($sql, $query, $plan_id);
	$plan_name = $rs->fields['name'] . "<br />" . $rs->fields['description'];

	generate_ip_list($tpl, $_SESSION['user_id']);

	if ($customer_id === null) $customer_id = '';

	$tpl->assign(
		array(
			'ID'			=> tohtml($order_id),
			'DATE'			=> tohtml($date),
			'HP'			=> tohtml($plan_name),
			'DOMAINNAME'	=> tohtml($domain_name),
			'CUSTOMER_ID'	=> tohtml($customer_id),
			'FNAME'			=> tohtml($fname),
			'LNAME'			=> tohtml($lname),
			'FIRM'			=> tohtml($firm),
			'ZIP'			=> tohtml($zip),
			'CITY'			=> tohtml($city),
			'STATE'			=> tohtml($state),
			'COUNTRY'		=> tohtml($country),
			'EMAIL'			=> tohtml($email),
			'PHONE'			=> tohtml($phone),
			'FAX'			=> tohtml($fax),
			'STREET1'		=> tohtml($street1),
			'STREET2'		=> tohtml($street2),
			'VL_MALE'		=> (($gender == 'M') ? $cfg->HTML_SELECTED : ''),
			'VL_FEMALE'		=> (($gender == 'F') ? $cfg->HTML_SELECTED : ''),
			'VL_UNKNOWN'	=> ((($gender == 'U') || (empty($gender))) ? $cfg->HTML_SELECTED : '')
		)
	);
}

function update_order_details($tpl, $sql, $user_id, $order_id) {
	$domain			= strtolower($_POST['domain']);
	$domain			= encode_idna($domain);
	$customer_id	= $_POST['customer_id'];
	$fname			= $_POST['fname'];
	$lname			= $_POST['lname'];
	$gender			= in_array($_POST['gender'],array('M', 'F', 'U')) ? $_POST['gender'] : 'U';
	$firm			= $_POST['firm'];
	$zip			= $_POST['zip'];
	$city			= $_POST['city'];
	$state			= $_POST['state'];
	$country		= $_POST['country'];
	$street1		= $_POST['street1'];
	$street2		= $_POST['street2'];
	$email			= $_POST['email'];
	$phone			= $_POST['phone'];
	$fax			= $_POST['fax'];

	$query = "
		UPDATE
			`orders`
		SET
			`domain_name` = ?,
			`customer_id` = ?,
			`fname` = ?,
			`lname` = ?,
			`gender` = ?,
			`firm` = ?,
			`zip` = ?,
			`city` = ?,
			`state` = ?,
			`country` = ?,
			`email` = ?,
			`phone` = ?,
			`fax` = ?,
			`street1` = ?,
			`street2` = ?
		WHERE
			`id` = ?
		AND
			`user_id` = ?
	";
	exec_query($sql, $query, array($domain, $customer_id, $fname, $lname, $gender, $firm, $zip, $city, $state, $country, $email, $phone, $fax, $street1, $street2, $order_id, $user_id));
}
?>