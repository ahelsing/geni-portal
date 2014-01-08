<?php
//----------------------------------------------------------------------
// Copyright (c) 2012-2014 Raytheon BBN Technologies
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

// A Single Slice

require_once("user.php");
require_once("header.php");
require_once("portal.php");
require_once('util.php');
require_once('pa_constants.php');
require_once('pa_client.php');
require_once('sr_constants.php');
require_once('sr_client.php');
require_once("sa_constants.php");
require_once("sa_client.php");
require_once("settings.php");
require_once('logging_constants.php');
require_once('logging_client.php');
require_once('am_map.php');
require_once('status_constants.php');
require_once('maintenance_mode.php');

$user = geni_loadUser();
if (!isset($user) || is_null($user) || ! $user->isActive()) {
  relative_redirect('home.php');
}
unset($slice);
include("tool-lookupids.php");

$disable_buttons_str = "";

if (isset($slice_expired) && convert_boolean($slice_expired) ) {
  $disable_buttons_str = " disabled";
}

if (! isset($all_ams)) {
  $am_list = get_services_of_type(SR_SERVICE_TYPE::AGGREGATE_MANAGER);
  $all_ams = array();
  foreach ($am_list as $am) 
  {
    $single_am = array();
    $service_id = $am[SR_TABLE_FIELDNAME::SERVICE_ID];
    $single_am['name'] = $am[SR_TABLE_FIELDNAME::SERVICE_NAME];
    $single_am['url'] = $am[SR_TABLE_FIELDNAME::SERVICE_URL];
    $all_ams[$service_id] = $single_am;
  }   
}

// print_r( $all_ams);

// For comparing member records by role (low roles come before high roles)
function compare_members_by_role($mem1, $mem2)
{
  $role1 = $mem1[SA_SLICE_MEMBER_TABLE_FIELDNAME::ROLE];
  $role2 = $mem2[SA_SLICE_MEMBER_TABLE_FIELDNAME::ROLE];
  if ($role1 < $role2)
    return -1;
  else if ($role1 > $role2) 
    return 1;
  else return 0;
  
}

function compare_last_names($mem1,$mem2)
{
  $parts1 = explode(" ",$mem1);
  $name1 = array_pop($parts1);
  $parts2 = explode(" ",$mem2);
  $name2 = array_pop($parts2);
  return strcmp($name1,$name2);
}


function build_agg_table_on_slicepg() 
{
     global $am_list;
     global $slice;
     global $slice_id;
     global $renew_slice_privilege;
     global $slice_expiration;
     global $slice_date_expiration;
     global $delete_slivers_disabled;
     global $slice_name;
     global $disable_buttons_str;
     global $get_slice_credential_disable_buttons;

     $sliver_expiration = "NOT IMPLEMENTED YET";
     $slice_status = "";


     $status_url = 'sliverstatus.php?slice_id='.$slice_id;
     $listres_url = 'listresources.php?slice_id='.$slice_id;

     $updating_text = "...updating...";
     $initial_text = "not retrieved";

     // (2) create an HTML table with one row for each aggregate
     $output = "<table id='status_table'>";
     //  output .=  "<tr><th>StatusXXX</th><th colspan='2'>Slice</th><th>Creation</th><th>Expiration</th><th>Actions</th></tr>\n";
     $output .= "<tr>";
     $output .= "<th id='status'>";
     $output .= "Status<br/><button id='reload_all_button' type='button' onclick='refresh_all_agg_rows()' $get_slice_credential_disable_buttons>Get All</button>";
     $output .= "</th><th>Aggregate</th>";
     //      output .= "<th>&nbsp;</th>";
     $output .= "<th>Renew</th>";
     $output .= "<th>Actions</th></tr>\n";
     foreach ($am_list as $am) {
	    $name = $am[SR_TABLE_FIELDNAME::SERVICE_NAME];
            $am_id = $am[SR_TABLE_FIELDNAME::SERVICE_ID];
            $output .= "<tr id='".$am_id."'>";
	    $output .= "<td id='status_".$am_id."' class='notqueried'>";	
	    $output .= $initial_text;
	    $output .= "</td>";
	    $output .= "<td rowspan='2'>";	
	    $output .= $name;
	    $output .= "</td>";	
	    // sliver expiration
            $output .= "<td rowspan='2'>";
	    $output .= "Expires on <b><span class='renew_date' id='renew_sliver_".$am_id."'>".$initial_text."</span></b>";
	    if ($renew_slice_privilege) {
		$output .= "<form  method='GET' action=\"do-renew.php\">";
		$output .= "<input type=\"hidden\" name=\"slice_id\" value=\"".$slice_id."\"/>\n";
		$output .= "<input type=\"hidden\" name=\"am_id\" value=\"".$am_id."\"/>\n";
		$output .= "<input type=\"hidden\" name=\"renew\" value=\"sliver\"/>\n";
		$output .= "<input id='renew_field_".$am_id."' class='date' type='text' name='sliver_expiration' ";
		$size = strlen($slice_date_expiration) + 3;
		$output .= "size=\"$size\" value=\"".$slice_date_expiration."\"/>\n";
		$output .= "<input id='renew_button_".$am_id."' type='submit' name= 'Renew' value='Renew' title='Renew resource reservation at this aggregate until the specified date' $disable_buttons_str/>\n";
		$output .= "</form>";
	    }		
	    $output .= "</td>\n";
	    // sliver actions
	    $output .= "<td rowspan='2'>";
	    $output .= "<button id='status_button_".$am_id."' onClick=\"window.location='".$status_url."&am_id=".$am_id."'\" $get_slice_credential_disable_buttons><b>Resource Status</b></button>";
	    $output .= "<button  id='details_button_".$am_id."' title='Login info, etc' onClick=\"window.location='".$listres_url."&am_id=".$am_id."'\" $get_slice_credential_disable_buttons><b>Details</b></button>\n";
	    $output .= "<button  id='delete_button_".$am_id."' onClick=\"window.location='confirm-sliverdelete.php?slice_id=".$slice_id."&am_id=".$am_id."'\" ".$delete_slivers_disabled." $disable_buttons_str><b>Delete Resources</b></button>\n";
	    $output .= "</td></tr>";



	    $output .= "<tr><td class='status_buttons'><button id='reload_button_'".$am_id." type='button' onclick='refresh_agg_row(".$am_id.")' $get_slice_credential_disable_buttons>Get Status</button></td></tr>";




            // (3) Get the status for this slice at this aggregate
//	    update_agg_row( am_id );
     }	
     $output .= "</table>";
     return $output;
}


if (! isset($sa_url)) {
  $sa_url = get_first_service_of_type(SR_SERVICE_TYPE::SLICE_AUTHORITY);
}

if (! isset($ma_url)) {
  $ma_url = get_first_service_of_type(SR_SERVICE_TYPE::MEMBER_AUTHORITY);
}

if (isset($slice)) {
  //  $slice_name = $slice[SA_ARGUMENT::SLICE_NAME];
  //  error_log("SLICE  = " . print_r($slice, true));
  $slice_desc = $slice[SA_ARGUMENT::SLICE_DESCRIPTION];
  $slice_creation_db = $slice[SA_ARGUMENT::CREATION];
  $slice_creation = dateUIFormat($slice_creation_db);
  $slice_expiration_db = $slice[SA_ARGUMENT::EXPIRATION];
  $slice_expiration = dateUIFormat($slice_expiration_db);
  $slice_date_expiration = dateOnlyUIFormat($slice_expiration_db);
  $slice_urn = $slice[SA_ARGUMENT::SLICE_URN];
  $slice_owner_id = $slice[SA_ARGUMENT::OWNER_ID];
  $owner = $user->fetchMember($slice_owner_id);
  $slice_owner_name = $owner->prettyName();
  $owner_email = $owner->email();

  $project_name = $project[PA_PROJECT_TABLE_FIELDNAME::PROJECT_NAME];
  //error_log("slice project_name result: $project_name\n");
  // Fill in members of slice member table
  $members = get_slice_members($sa_url, $user, $slice_id);
  $member_names = lookup_member_names_for_rows($ma_url, $user, $members, 
					       SA_SLICE_MEMBER_TABLE_FIELDNAME::MEMBER_ID);
} else {
  print "Unable to load slice<br/>\n";
  $_SESSION['lasterror'] = "Unable to load slice";
  relative_redirect("home.php");
  exit();
}

$edit_url = 'edit-slice.php?slice_id='.$slice_id;
$add_url = 'slice-add-resources.php?slice_id='.$slice_id;
$res_url = 'sliceresource.php?slice_id='.$slice_id;
$proj_url = 'project.php?project_id='.$slice_project_id;
$slice_own_url = "mailto:$owner_email";
//$slice_own_url = 'slice-member.php?member_id='.$slice_owner_id . "&slice_id=" . $slice_id;
$omni_url = "tool-omniconfig.php";
$flack_url = "flack.php?slice_id=".$slice_id;
$gemini_url = "gemini.php?slice_id=" . $slice_id;
$labwiki_url = 'http://emmy9.casa.umass.edu:4000/?slice_id=' . $slice_id;

$status_url = 'sliverstatus.php?slice_id='.$slice_id;
$listres_url = 'listresources.php?slice_id='.$slice_id;
$edit_slice_members_url = 'edit-slice-member.php?slice_id='.$slice_id."&project_id=".$slice_project_id;

// String to disable button or other active element
$disabled = "disabled = " . '"' . "disabled" . '"'; 

$add_slivers_privilege = $user->isAllowed(SA_ACTION::ADD_SLIVERS,
				    CS_CONTEXT_TYPE::SLICE, $slice_id);
$add_slivers_disabled = "";
if(!$add_slivers_privilege) { $add_slivers_disabled = $disabled; }

$get_slice_credential_privilege = $user->isAllowed(SA_ACTION::GET_SLICE_CREDENTIAL, 
						   CS_CONTEXT_TYPE::SLICE, $slice_id);
$get_slice_credential_disable_buttons = "";
if(!$get_slice_credential_privilege) {$get_slice_credential_disable_buttons = $disabled; }

// String to disable button or other active element
$disabled = "disabled = " . '"' . "disabled" . '"'; 

$delete_slivers_privilege = $user->isAllowed(SA_ACTION::DELETE_SLIVERS,
				    CS_CONTEXT_TYPE::SLICE, $slice_id);
$delete_slivers_disabled = "";
if(!$delete_slivers_privilege) { $delete_slivers_disabled = $disabled; }

$renew_slice_privilege = $user->isAllowed(SA_ACTION::RENEW_SLICE,
				    CS_CONTEXT_TYPE::SLICE, $slice_id);
$renew_disabled = "";
if(!$renew_slice_privilege) { $renew_disabled = $disabled; }

$lookup_slice_privilege = $user->isAllowed(SA_ACTION::LOOKUP_SLICE, 
				    CS_CONTEXT_TYPE::SLICE, $slice_id);

if(!$lookup_slice_privilege) {
  $_SESSION['lasterror'] = 'User has no privileges to view slice ' . $slice_name;
  relative_redirect('home.php');
}

// determine maximum date of slice renewal
$renewal_days = $portal_max_slice_renewal_days;
$project_expiration = $project[PA_PROJECT_TABLE_FIELDNAME::EXPIRATION];
if ($project_expiration) {
  $project_expiration_dt = new DateTime($project_expiration);
  $now_dt = new DateTime();
  $difference = $project_expiration_dt->diff($now_dt);
  $renewal_days = $difference->days;
  // take the minimum of the two as the constraint
  $renewal_days = min($renewal_days, $portal_max_slice_renewal_days);
}

show_header('GENI Portal: Slices', $TAB_SLICES);
include("tool-breadcrumbs.php");
include("tool-showmessage.php");

?>

<!-- This belongs in the header, probably -->
<script>
var slice= "<?php echo $slice_id ?>";
var renew_slice_privilege= "<?php echo $renew_slice_privilege?>";
var slice_expiration= "<?php echo $slice_expiration?>";
var slice_date_expiration= "<?php echo $slice_date_expiration?>";
var sliver_expiration= "NOT IMPLEMENTED YET";
var delete_slivers_disabled= "<?php echo $delete_slivers_disabled ?>";
var slice_status= "";
var slice_name= "<?php echo $slice_name?>";
var slice= "<?php echo $slice_id ?>";
var all_ams= '<?php echo json_encode($all_ams) ?>';
var max_slice_renewal_days = "+" + "<?php echo $renewal_days ?>" + "d";
<?php include('status_constants_import.php'); ?>
</script>
<script src="amstatus.js"></script>
<!--<script>
$(document).ready(build_agg_table_on_slicepg());
</script>
-->
<?php 
print "<h1>GENI Slice: " . "<i>" . $slice_name . "</i>" . " </h1>\n";

if (isset($slice_expired) && convert_boolean($slice_expired) ) {
   print "<p class='warn'>This slice is expired!</p>\n";
}

// FIXME: Set add_slivers_disabled if in_lockdown_mode or otherwise disable 'Add Resources'?
// FIXME: Disable launch flack if in lockdown mode?

print "<table>\n";
print "<tr><th>Slice Actions</th><th>Renew</th></tr>\n";

/* Slice Actions */
print "<tr>";
if ($renew_slice_privilege) {
print "<td rowspan='2'>\n";
} else {
print "<td>\n";
}
print "<button onClick=\"window.location='$add_url'\" $add_slivers_disabled $disable_buttons_str><b>Add Resources</b></button>\n";

print "<button onClick=\"window.location='$status_url'\" $get_slice_credential_disable_buttons><b>Resource Status</b></button>\n";
print "<button title='Login info, etc' onClick=\"window.location='$listres_url'\" $get_slice_credential_disable_buttons><b>Details</b></button>\n";

print "<button onClick=\"window.location='confirm-sliverdelete.php?slice_id=" . $slice_id . "'\" $delete_slivers_disabled $disable_buttons_str><b>Delete Resources</b></button>\n";
print "</td>\n";

/* Renew */
if ($project_expiration) {
  $project_exp_print = dateUIFormat($project_expiration);
  $project_line = "Project expires on <b>$project_exp_print</b><br>";
} else {
  $project_line = "Project does not have an expiration date<br>";
}
print "<td>\n";
print $project_line;
print "Slice expires on <b>$slice_expiration</b>";
print "</td></tr>\n";


if ($renew_slice_privilege) {
  print "<tr><td id='renewcell'>\n";
  print "<form method='GET' action=\"do-renew.php\">";
  print "<table id='renewtable'><tr><td>";
  print "Renew ";
  print "</td><td>";
  print "<div>";
  print "<input type='radio' name='renew' value='slice'>slice only<br>";
  print "<input type='radio' name='renew' value='slice_sliver' checked>slice & all resources";
  print "</div>";
  print "</td><td>";
  print " until <br/>";
  print "</td></tr><tr>";
  print "<tr><td id='renewbutton' colspan=3>";
  print "<input type=\"hidden\" name=\"slice_id\" value=\"$slice_id\"/>\n";
  print "<input class='date' type='text' name='sliver_expiration' id='datepicker'";
  $size = strlen($slice_date_expiration) + 3;
  print " size=\"$size\" value=\"$slice_date_expiration\"/>\n";
  print "<input type='submit' name= 'Renew' value='Renew' title='Renew until the specified date' $disable_buttons_str/>\n";
  print "</td></tr></table>";
  print "</form>\n";
  print "</td></tr>\n";
}
?>
<script>
  $(function() {
    // minDate = 1 will not allow today or earlier, only future dates.
    $( "#datepicker" ).datepicker({ dateFormat: "yy-mm-dd", minDate: slice_date_expiration, maxDate: max_slice_renewal_days  });
    $( ".date" ).datepicker({ dateFormat: "yy-mm-dd", minDate: 1,  maxDate: slice_date_expiration });
  });
</script>
<?php

print "<tr><th>Tools</th><th>Ops Mgmt</th></tr>\n";
/* Tools */
print "<tr><td>\n";
/* print "To use a command line tool:<br/>"; */
$hostname = $_SERVER['SERVER_NAME'];
print "<button $add_slivers_disabled onClick=\"window.open('$flack_url')\" $disable_buttons_str><image width=\"40\" src=\"https://$hostname/images/pgfc-screenshot.jpg\"/><br/><b>Launch Flack</b> </button>\n";

  print "<button $add_slivers_disabled onClick=\"window.open('$gemini_url')\" $disable_buttons_str><b>GENI Desktop</b></button>\n";

  print "<button $add_slivers_disabled onClick=\"window.open('$labwiki_url')\" $disable_buttons_str><b>LabWiki</b></button>\n";

print "<button onClick=\"window.location='$omni_url'\" $add_slivers_disabled $disable_buttons_str><b>Use omni</b></button>\n";
//print "<button disabled='disabled'><b>Download GUSH Config</b></button>\n";
print "</td>\n";

/* Ops Management */
print "<td>\n";
print "<button title=\"not working yet\" disabled=\"disabled\" onClick=\"window.location='disable-slice.php?slice_id=" . $slice_id . "'\"><b>Disable Slice</b></button>\n";
print "<button title=\"not working yet\" disabled=\"disabled\" onClick=\"window.location='shutdown-slice.php?slice_id=" . $slice_id . "'\"><b>Shutdown Slice</b></button>\n";
print "</td></tr>\n";

print "</table>\n";

/* print "<h2>Slice Operational Monitoring</h2>\n"; */
/* print "<table>\n"; */
/* print "<tr><td><b>Slice data</b></td><td><a href='https://gmoc-db.grnoc.iu.edu/protected-openid/index.pl?method=slice_details;slice=".$slice_urn."'>Slice $slice_name</a></td></tr>\n"; */
/* print "</table>\n"; */


print "<p>Confused? Look at the <a href='help.php'>Portal Help</a> or <a href='http://groups.geni.net/geni/wiki/GENIGlossary'>GENI Glossary</a>.</p>";

print "<h2>Slice Members</h2>";
?>

<p>Slice members will be able to login to resources reserved <i>in the future</i> if:</p>
<ul>
 <li>The resources were reserved directly through the portal (by clicking <b>Add Resources</b> on the slice page), and</li>
 <li>The slice member has uploaded an ssh public key.</li>
</ul>

<table>
	<tr>
		<th>Slice Member</th>
		<th>Role</th>
	</tr>
	<?php
usort($members, 'compare_members_by_role');
// Write each row in the project member table
// Sort alphabetically by role

$member_lists = array();
$member_lists[1] = array();
$member_lists[2] = array();
$member_lists[3] = array();
$member_lists[4] = array();

foreach($members as $member) {

  $member_id = $member[SA_SLICE_MEMBER_TABLE_FIELDNAME::MEMBER_ID];
  //  error_log("MEMBER = " . print_r($member_user, true));
  $member_name = $member_names[$member_id];
  $member_ids[$member_name] = $member_id;
  $member_role_index = $member[SA_SLICE_MEMBER_TABLE_FIELDNAME::ROLE];
  $member_role = $CS_ATTRIBUTE_TYPE_NAME[$member_role_index];
  $member_lists[$member_role_index][] = $member_name;

}

foreach ($member_lists as $member_role_index => $member_names) {
  usort($member_names, 'compare_last_names');
  foreach ($member_names as $member_name) {
    $member_role = $CS_ATTRIBUTE_TYPE_NAME[$member_role_index];
    $member_id = $member_ids[$member_name];
    $member_info = $user->fetchMember($member_id);
    $member_email = $member_info->email();
    $member_url = "mailto:$member_email";

    print "<tr><td><a href=$member_url>$member_name</a></td><td>$member_role</td></tr>\n";

    /*
    print "<tr><td><a href=\"slice-member.php?slice_id=" . $slice_id . 
      "&member_id=$member_id\">$member_name</a></td>" . 
      "<td>$member_role</td></tr>\n";
    */
  }
}
	?>
</table>

<?php
$edit_members_disabled = "";
if (!$user->isAllowed(SA_ACTION::ADD_SLICE_MEMBER, CS_CONTEXT_TYPE::SLICE, $slice_id) || $in_lockdown_mode) {
  $edit_members_disabled = $disabled;
}
echo "<p><button $edit_members_disabled onClick=\"window.location='$edit_slice_members_url'\"><b>Edit Slice Membership</b></button></p>";

// ----
// Now show slice / sliver status

print "<h2>Slice Status</h2>\n";

  $slice_status='';

  print "<div id='status_table_div'/>\n";
  print build_agg_table_on_slicepg();
  print "</div>\n";
// --- End of Slice and Sliver Status table

// Slice Identifers table
print "<table>\n";
print "<tr><th colspan='2'>Slice Identifiers (public)</th></tr>\n";
print "<tr><td class='label'><b>Name</b></td><td>$slice_name</td></tr>\n";
print "<tr><td class='label'><b>Project</b></td><td><a href=$proj_url>$project_name</a></td></tr>\n";
print "<tr><td class='label deemphasize'><b>URN</b></td><td  class='deemphasize'>$slice_urn</td></tr>\n";
print "<tr><td class='label'><b>Creation</b></td><td>$slice_creation</td></tr>\n";
print "<tr><td class='label'><b>Description</b></td><td>$slice_desc ";
echo "<button disabled=\"disabled\" onClick=\"window.location='$edit_url'\"><b>Edit</b></button>";
print "</td></tr>\n";
print "<tr><th colspan='2'>Contact Information</th></tr>\n";
print "<tr><td class='label'><b>Slice Owner</b></td><td><a href=$slice_own_url>$slice_owner_name</a></td></tr>\n";
//print "<tr><td class='label'><b>Slice Owner</b></td><td><a href=$slice_own_url>$slice_owner_name</a> <a href='mailto:$owner_email'>e-mail</a></td></tr>\n";
print "</table>\n";
// ---
?>


<h2>Recent Slice Actions</h2>
<table>
	<tr>
		<th>Time</th>
		<th>Message</th>
		<th>Member</th>
		<?php
		$log_url = get_first_service_of_type(SR_SERVICE_TYPE::LOGGING_SERVICE);
                $entries = get_log_entries_for_context($log_url, $user,
						       CS_CONTEXT_TYPE::SLICE, $slice_id);
                $entry_member_names = lookup_member_names_for_rows($ma_url, $user, $entries, 
								   LOGGING_TABLE_FIELDNAME::USER_ID);

                usort($entries, 'compare_log_entries');
		foreach($entries as $entry) {
		  $message = $entry[LOGGING_TABLE_FIELDNAME::MESSAGE];
		  $time = dateUIFormat($entry[LOGGING_TABLE_FIELDNAME::EVENT_TIME]);
		  $member_id = $entry[LOGGING_TABLE_FIELDNAME::USER_ID];
		  $member_name = $entry_member_names[$member_id];
		  //    error_log("ENTRY = " . print_r($entry, true));
		  //		  print "<tr><td>$time</td><td>$message</td><td><a href=\"slice-member.php?slice_id=" . $slice_id . "&member_id=$member_id\">$member_name</a></td></tr>\n";
		  // FIXME: Want a mailto link
		  print "<tr><td>$time</td><td>$message</td><td>$member_name</td></tr>\n";
  }
?>

</table>

<?php
include("footer.php");
?>
