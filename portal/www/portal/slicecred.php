<?php
//----------------------------------------------------------------------
// Copyright (c) 2011-2016 Raytheon BBN Technologies
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
require_once("settings.php");
require_once("user.php");
$user = geni_loadUser();
if (! $user->isActive()) {
  relative_redirect("home.php");
}

function no_slice_error() {
  header('HTTP/1.1 404 Not Found');
  print 'No slice id specified.';
  exit();
}

if (! count($_GET)) {
  // No parameters. Return an error result?
  // For now, return nothing.
  no_slice_error();
}
$slice = null;
include("tool-lookupids.php");
if (is_null($slice) || $slice == '') {
  no_slice_error();
}

if (isset($slice_expired) && convert_boolean($slice_expired)) {
  if (! isset($slice_name)) {
    $slice_name = "";
  }
  $_SESSION['lasterror'] = "Slice " . $slice_name . " is expired.";
  relative_redirect('dashboard.php#slices');
}

if (!$user->isAllowed(SA_ACTION::GET_SLICE_CREDENTIAL, CS_CONTEXT_TYPE::SLICE, $slice_id)) {
  relative_redirect('home.php');
}

// TODO: Pass expiration to slicecred.py

$outside_key = db_fetch_outside_private_key_cert($user->account_id);
if (! $outside_key) {
  include("header.php");
  show_header('GENI Portal: Slices');
  include("tool-breadcrumbs.php");
  print "<h2>Cannot Download Slice Credential</h2>\n";
  print "This page allows you to download a slice credential file,"
    . " for use in other tools (e.g. Omni).\n"
    . "This is advanced functionality, not required for typical GENI users.\n"
    . "Please"
    . " <button onClick=\"window.location='"
    . relative_url("downloadkeycert.php")
    . "'\">Download your key and certificate</button>"
    . " so that a credential can be retrieved.";
  include("footer.php");
  exit();
}

// Get the slice credential from the SA using the outside certificate
$slice_credential = get_slice_credential($sa_url, $user, $slice_id,
                                         $outside_key['certificate']);

// FIXME: slice name only unique within project. Need slice URN?
/* FIXME COMMENT: The URN would suck as part of a filename. Too many
 *                special characters.
 */
$cred_filename = $slice_name . "-cred.xml";

// Set headers for download
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=$cred_filename");
header("Content-Type: text/xml");
header("Content-Transfer-Encoding: binary");
print $slice_credential;
?>
