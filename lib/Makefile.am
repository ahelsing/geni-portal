svcdatadir = $(pkgdatadir)/lib
pkgsysconfdir = $(sysconfdir)/geni-ch

edit = sed \
	-e 's|@VERSION[@]|$(VERSION)|g' \
	-e 's|@pkgsysconfdir[@]|$(pkgsysconfdir)|g'

TEMPLATES = \
	php/aggstatus.php \
	php/settings.php

TEMPLATES.IN = $(TEMPLATES:%=%.in)

$(TEMPLATES): Makefile
	rm -f $@ $@.tmp
	srcdir=''; \
		test -f ./$@.in || srcdir=$(srcdir)/; \
		$(edit) $${srcdir}$@.in >$@.tmp
	chmod a-w $@.tmp
	mv $@.tmp $@

%: $(srcdir)/%.in

# Distribute but do not install
EXTRA_DIST = $(TEMPLATES.IN)

CLEANFILES = $(TEMPLATES)

nobase_dist_svcdata_DATA = \
	php/abac.php \
	php/aggstatus.php \
	php/am_client.php \
	php/am_map.php \
	php/cert_utils.php \
	php/chapi.php \
	php/client_utils.php \
	php/cs_client.php \
	php/cs_constants.php \
	php/db-util.php \
	php/db_utils.php \
	php/file_utils.php \
	php/footer.php \
	php/gemini_rspec_routines.php \
	php/geni_syslog.php \
	php/guard.php \
	php/header.php \
	php/irods_utils.php \
	php/jacks-app.php \
	php/jacks-editor-app.php \
	php/json_util.php \
	php/logging_client.php \
	php/logging_constants.php \
	php/ma_client.php \
	php/ma_constants.php \
	php/maintenance_mode.php \
	php/map.html \
	php/message_handler.php \
	php/omni_invocation_constants.php \
	php/pa_client.php \
	php/pa_constants.php \
	php/permission_manager.php \
	php/portal.php \
	php/print-text-helpers.php \
	php/proj_slice_member.php \
	php/query-details.php \
	php/query-sliverstatus.php \
	php/response_format.php \
	php/rq_client.php \
	php/rq_constants.php \
	php/rq_controller.php \
	php/rq_utils.php \
	php/sa_client.php \
	php/sa_constants.php \
	php/services.php \
	php/session_cache.php \
	php/settings.php \
	php/signer.php \
	php/sliceresource.js \
	php/smime.php \
	php/speaksforcred.php \
	php/sr_client.php \
	php/sr_constants.php \
	php/status_constants.php \
	php/tabs.js \
	php/tool-breadcrumbs.php \
	php/tool-expired-projects.php \
	php/tool-expired-slices.php \
	php/tool-lookupids.php \
	php/tool-projects.php \
	php/tool-rspec-parse.php \
	php/tool-rspecs.js \
	php/tool-rspecs.php \
	php/tool-showmessage.php \
	php/tool-slices.php \
	php/tools-admin.php \
	php/tools-user.php \
	php/uploadsshkey.html \
	php/user.php \
	php/user-preferences.php \
	php/util.php
