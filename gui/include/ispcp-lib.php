<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2010 by ispCP | http://isp-control.net
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
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 */

/**
 * This is the primarly file that should be included in all the IspCP's user
 * levels scripts such as all scripts that live under gui/{admin,reseller,client}
 */

// Set default error reporting level
error_reporting(E_ALL|E_STRICT);

// Only for development
//ini_set('display_errors', 1);

// Define path for the ispCP include directory
define('INCLUDEPATH', dirname(__FILE__));

/**
 * Autoloading classes
 *
 * @todo switch to POO
 */
require_once INCLUDEPATH . '/ispcp-loader.php';
spl_autoload_register('autoload_class');

/**
 * Include primary needed librairies
 */

require_once INCLUDEPATH . '/i18n.php';
require_once INCLUDEPATH . '/system-message.php';
require_once INCLUDEPATH . '/ispcp-functions.php';

/**
 * Bootstrap the IspCP environment, and default configuration
 *
 * @see {@link IspCP_Bootstrap} class
 * @see {@link IspCP_Initializer} class
 */
include_once INCLUDEPATH . '/environment.php';

/**
 * Sql convenience* functions
 *
 * @todo Implement the Observer Design Pattern for mail delivery and logging
 * and, then remove these functions (Only POO style should be used).
 *
 * *For them that are newbies...
 */
require_once 'sql.php';

/**
 * Authentication librairies
 */
require_once 'login-functions.php';
require_once 'login.php';

/**
 * User level functions
 *
 * @todo: Must be refactored to be able to load only files that are needed
 */
require_once 'admin-functions.php';
require_once 'reseller-functions.php';
require_once 'client-functions.php';

/**
 * Some others shared librairies
 */
require_once 'deprecated.php';
require_once 'date-functions.php';
require_once 'input-checks.php';
require_once 'calc-functions.php';
require_once 'net_idna/idna_convert.class.php';
require_once 'lostpassword-functions.php';
require_once 'emailtpl-functions.php';
require_once 'layout-functions.php';
require_once 'functions.ticket_system.php';
require_once 'debug.php';
