<?php
//----------------------------------------------------------------------
// Copyright (c) 2012-2015 Raytheon BBN Technologies
//
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and/or hardware specification (the "Work") to
// deal in the Work without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Work, and to permit persons to whom the Work
// is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Work.
//
// THE WORK IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE WORK OR THE USE OR OTHER DEALINGS
// IN THE WORK.
//----------------------------------------------------------------------
?>
<?php
//--------------------------------------------------
// Site settings for GENI Identity Portal
//--------------------------------------------------

// Where to find the gcf installation. This is necessary for
// generation of slice credentials.
$portal_gcf_dir = '/usr/share/geni-ch/portal/gcf';

// Where to find the local gcf configuration directory.
$portal_gcf_cfg_dir = '/usr/share/geni-ch/portal/gcf.d';

// Set to true for demo situations to auto approve new accounts.
$portal_auto_approve = false;

// Portal certificate file
$portal_cert_file = '/usr/share/geni-ch/portal/portal-cert.pem';

// Portal private key file
$portal_private_key_file = '/usr/share/geni-ch/portal/portal-key.pem';

// set to match the current GENI CH SA
$portal_max_slice_renewal_days = 185;

// Portal version
$portal_version = "3.0";

// URL to the Flack loader. Used in flack.php
$flack_url = "https://www.emulab.net/protogeni/flack-stable/loader.js";
//$flack_url = "https://portal.geni.net/flack-stable/loader.js";

// URL to the Jacks root
// Stable
$jacks_stable_url = "https://www.emulab.net/protogeni/jacks-stable/js/jacks";
//$jacks_stable_url = "https://portal.geni.net/jacks-stable/js/jacks";

// Sources for external libraries the portal uses 

// Version 2.1.4 most recent as of 6/2015
$portal_jquery_url = 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js';

// Version 1.11.4 most recent as of 6/2015
$portal_jqueryui_js_url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';

// Version 1.11.4 most recent as of 6/2015
$portal_jqueryui_css_url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/humanity/jquery-ui.css';

// Version 2.1.4 most recent as of 6/2015
$portal_datatablesjs_url = 'https://cdn.datatables.net/1.10.7/js/jquery.dataTables.js';

//----------------------------------------------------------------------
// Set error level to include user errors (generated by the portal).
//----------------------------------------------------------------------
// Add E_USER_ERROR to the current set
error_reporting(error_reporting() | E_USER_ERROR);

?>
