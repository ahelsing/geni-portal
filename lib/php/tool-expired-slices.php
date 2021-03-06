<?php
//----------------------------------------------------------------------
// Copyright (c) 2012-2016 Raytheon BBN Technologies
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

require_once("user.php");
require_once("sr_client.php");
require_once("sr_constants.php");
require_once("sa_client.php");
require_once("pa_client.php");
require_once("util.php");
require_once("proj_slice_member.php");
include("services.php");

// String to disable button or other active element
$disabled = "disabled = " . '"' . "disabled" . '"'; 

if(!isset($project_objects) || !isset($slice_objects) || 
   !isset($member_objects) || !isset($project_slice_map)) 
{
  $pid = null;
  if (isset($project_id)) { $pid = $project_id; }
  $retVal  = get_project_slice_member_info( $sa_url, $ma_url, $user, 
					    True, $pid);
  $project_objects = $retVal[0];
  $slice_objects = $retVal[1];
  $member_objects = $retVal[2];
  $project_slice_map = $retVal[3];
  $project_activeslice_map = $retVal[4];
}

$my_slice_objects = $slice_objects;
if (isset($project_id)) {
  $my_slice_objects = array();
  foreach($project_slice_map[$project_id] as $slice_id) {
    $my_slice_objects[] = $slice_objects[$slice_id];
  }
}


?>
<script type="text/javascript">
function toggleDiv(id) {
   $("#"+id).toggle();
}
</script>
<p><button type='button' disabled onclick='toggleDiv("expired")'>Expired Slices</button></p>
<div id="expired" style="display: none;">
<h2>Expired Slices</h2>
<?php

if(isset($expired_slices) && count($expired_slices) > 0) {
  print "\n<table>\n";
  print ("<tr><th>Slice Name</th>");
  print ("<th>Project</th>");
  print ("<th>Slice Creation</th>");
  print ("<th>Slice Expiration</th>");
  print ("<th>Slice Owner</th>");
  print "</tr>\n";

  $base_url = relative_url("slicecred.php?");
  $slice_base_url = relative_url("slice.php?");
  $listres_base_url = relative_url("listresources.php?");
  $resource_base_url = relative_url("slice-add-resources-jacks.php?");
  $delete_sliver_base_url = relative_url("confirm-sliverdelete.php?");
  $num_slices = count($expired_slices);
  if ($num_slices==1) {
      print "<p><i>You were a member of <b>1</b> expired slice.</i></p>";
  } else {
       print "<p><i>You were a member of <b>".$num_slices."</b> expired slices.</i></p>";
  }

  foreach ($expired_slices as $slice) {
    $slice_id = $slice[SA_SLICE_TABLE_FIELDNAME::SLICE_ID];
    $slice_expired = 'f';
    if (array_key_exists(SA_SLICE_TABLE_FIELDNAME::EXPIRED, $slice)) {
      $slice_expired = $slice[SA_SLICE_TABLE_FIELDNAME::EXPIRED];
    }
    $isSliceExpired = False;
    $disable_buttons_str = "";
    
    if (isset($slice_expired) && convert_boolean($slice_expired)) {
      $isSliceExpired = True;
      $disable_buttons_str = " disabled";
    }
    $args['slice_id'] = $slice_id;
    $query = http_build_query($args);
    $query = $query;
    $slicecred_url = $base_url . $query;
    $slice_url = $slice_base_url . $query;
    $sliceresource_url = $resource_base_url . $query;
    $delete_sliver_url = $delete_sliver_base_url . $query;
    $listres_url = $listres_base_url . $query;
    $slice_name = $slice[SA_ARGUMENT::SLICE_NAME];
    $creation = $slice[SA_ARGUMENT::CREATION];
    $expiration_db = $slice[SA_ARGUMENT::EXPIRATION];
    $expiration = dateUIFormat($expiration_db);
    $slice_project_id = $slice[SA_ARGUMENT::PROJECT_ID];

    // Determine privileges to this slice for this user
    $add_slivers_privilege = $user->isAllowed(SA_ACTION::ADD_SLIVERS,
					      CS_CONTEXT_TYPE::SLICE, 
					      $slice_id);
    $add_slivers_disabled = "";
    if(!$add_slivers_privilege or $isSliceExpired) { $add_slivers_disabled = $disabled; }
    
    $delete_slivers_privilege = $user->isAllowed(SA_ACTION::DELETE_SLIVERS,
						 CS_CONTEXT_TYPE::SLICE, 
						 $slice_id);
    $delete_slivers_disabled = "";
    if(!$delete_slivers_privilege or $isSliceExpired) { $delete_slivers_disabled = $disabled; }

    $renew_slice_privilege = $user->isAllowed(SA_ACTION::RENEW_SLICE,
					      CS_CONTEXT_TYPE::SLICE, 
					      $slice_id);
    $renew_disabled = "";
    if(!$renew_slice_privilege) { $renew_disabled = $disabled; }

    $lookup_slice_privilege = $user->isAllowed(SA_ACTION::LOOKUP_SLICE, 
					       CS_CONTEXT_TYPE::SLICE, 
					       $slice_id);

    $get_slice_credential_privilege = $user->isAllowed(SA_ACTION::GET_SLICE_CREDENTIAL, 
						       CS_CONTEXT_TYPE::SLICE, $slice_id);
    $get_slice_credential_disable_buttons = "";
    if(!$get_slice_credential_privilege) {$get_slice_credential_disable_buttons = $disabled; }



					       
    // Lookup the project for this project ID
    $slice_project_id = $slice[SA_SLICE_TABLE_FIELDNAME::PROJECT_ID];
    $project = $project_objects[ $slice_project_id ];

    $slice_project_name = $project[PA_PROJECT_TABLE_FIELDNAME::PROJECT_NAME];
    $slice_owner_id = $slice[SA_ARGUMENT::OWNER_ID];
    $slice_owner_name = $expired_slice_owner_names[$slice_owner_id];
    print "<tr>"
      . ("<td><a href=\"$slice_url\">" . htmlentities($slice_name)
         . "</a></td>");
    print "<td><a href=\"project.php?project_id=$slice_project_id\">" . htmlentities($slice_project_name) . "</a></td>";
    print "<td>" . htmlentities($creation) . "</td>";
    print "<td>" . htmlentities($expiration) . "</td>";
    // FIXME: Make this a mailto. Need to use member_objects to do init_from_record of a member and then retrieve the email address
    print "<td>" . htmlentities($slice_owner_name) . "</td>";
    //    print "<td><a href=\"slice-member.php?slice_id=$slice_id&member_id=$slice_owner_id\">" . htmlentities($slice_owner_name) . "</a></td>";
    $hostname = $_SERVER['SERVER_NAME'];
    print "</tr>\n";
  }
  print "</table>\n";

} else {
  if (isset($project_id) && uuid_is_valid($project_id)) {
    print "<p><i>You do not have access to any expired slices in this project.</i></p>\n";
  } else {
    print "<p><i>You do not have access to any expired slices.</i></p>\n";
  }
}

print "</div>\n";
