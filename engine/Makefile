#!/usr/bin/make -f

# ispCP ω (OMEGA) a Virtual Hosting Control Panel
# Copyright (C) 2001-2006 by moleSoftware GmbH - http://www.molesoftware.com
# Copyright (C) 2006-2011 by ispCP | http://ispcp.net
#
# Version: $Id$
#
# The contents of this file are subject to the Mozilla Public License
# Version 1.1 (the "License"); you may not use this file except in
# compliance with the License. You may obtain a copy of the License at
# http://www.mozilla.org/MPL/
#
# Software distributed under the License is distributed on an "AS IS"
# basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
# License for the specific language governing rights and limitations
# under the License.
#
# The Original Code is "VHCS - Virtual Hosting Control System".
#
# The Initial Developer of the Original Code is moleSoftware GmbH.
# Portions created by Initial Developer are Copyright (C) 2001-2006
# by moleSoftware GmbH. All Rights Reserved.
# Portions created by the ispCP Team are Copyright (C) 2006-2011 by
# isp Control Panel. All Rights Reserved.

SYSTEM_ENGINE_ROOT=$(SYSTEM_ROOT)/engine

CMD_CHOWN=/bin/chown
CMD_RM=/bin/rm -rf
CMD_INSTALL=/usr/bin/install -m 0700 -o root -g root
CMD_MAIL_INSTALL=/usr/bin/install -m 0700 -o vmail -g mail
CMD_DIR_INSTALL=/usr/bin/install -d -o root -g root

export

install:

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)
	$(CMD_DIR_INSTALL) -m 0755 $(SYSTEM_ENGINE_ROOT)

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/awstats
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/awstats

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/backup
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/backup

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/quota
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/quota

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/traffic
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/traffic

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/messenger
	$(CMD_DIR_INSTALL) -m 0755 $(SYSTEM_ENGINE_ROOT)/messenger

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/setup

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/tools
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/tools

	cd ./traffic && $(MAKE) install

	$(CMD_INSTALL) ./ispcp_common_code.pl $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp_common_methods.pl $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-load-db-keys.pl $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-apache-logger $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-rqst-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-dmn-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-sub-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-alssub-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-als-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-htusers-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-htaccess-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-htgroups-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-mbox-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-serv-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./ispcp-db-passwd $(SYSTEM_ENGINE_ROOT)

	$(CMD_INSTALL) ./awstats/awstats_buildstaticpages.pl $(SYSTEM_ENGINE_ROOT)/awstats
	$(CMD_INSTALL) ./awstats/awstats_updateall.pl $(SYSTEM_ENGINE_ROOT)/awstats

	$(CMD_INSTALL) ./backup/ispcp-bk-task $(SYSTEM_ENGINE_ROOT)/backup
	$(CMD_INSTALL) ./backup/ispcp-backup-all $(SYSTEM_ENGINE_ROOT)/backup
	$(CMD_INSTALL) ./backup/ispcp-backup-ispcp $(SYSTEM_ENGINE_ROOT)/backup

	$(CMD_INSTALL) ./quota/ispcp-dsk-quota $(SYSTEM_ENGINE_ROOT)/quota

	$(CMD_INSTALL) ./messenger/ispcp-arpl-msgr $(SYSTEM_ENGINE_ROOT)/messenger

	$(CMD_INSTALL) ./setup/ispcp-permission-functions.sh $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/set-engine-permissions.sh $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/set-gui-permissions.sh $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/ispcp-setup $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/ispcp-setup-methods.pl $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/ispcp-update $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/ispcp-uninstall $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/updDB.php $(SYSTEM_ENGINE_ROOT)/setup

	cp -dR ./setup/PerlVendor $(SYSTEM_ENGINE_ROOT)/setup
	cp -dR ./setup/PerlLib $(SYSTEM_ENGINE_ROOT)/setup

	$(CMD_INSTALL) ./tools/ispcp-del-logs.sh $(SYSTEM_ENGINE_ROOT)/tools
	$(CMD_INSTALL) ./tools/ispcp-del-phptemp.sh $(SYSTEM_ENGINE_ROOT)/tools
	$(CMD_INSTALL) ./tools/ispcp-httpd-logs-mngr $(SYSTEM_ENGINE_ROOT)/tools
	$(CMD_INSTALL) ./tools/ispcp-net-interfaces-mngr $(SYSTEM_ENGINE_ROOT)/tools

	$(CMD_INSTALL) ./traffic/ispcp-srv-traff $(SYSTEM_ENGINE_ROOT)/traffic
	$(CMD_INSTALL) ./traffic/ispcp-vrl-traff $(SYSTEM_ENGINE_ROOT)/traffic

	if test $(HOST_OS) = suse ; then \
		$(CMD_INSTALL) ./traffic/ispcp-vrl-traff-SUSE $(SYSTEM_ENGINE_ROOT)/traffic; \
	elif test $(HOST_OS) = opensuse ; then \
		$(CMD_INSTALL) ./traffic/ispcp-vrl-traff-SUSE $(SYSTEM_ENGINE_ROOT)/traffic; \
	fi

uninstall:

	rm -rf $(SYSTEM_ENGINE_ROOT)
