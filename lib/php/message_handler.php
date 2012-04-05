<?php
/* Top level message handler for a PHP top-level process
 * to handle smime encrypted/signed message files as JSON files
 *
 * Take the message as a file, decrypt, validate, decode as JSON, 
 * run the signified function with arguments
 * take the result and JSON encode into a document, encrypt and sign and return response
 */

require_once("smime.php");

function handle_message($prefix)
{
  //  error_log($prefix . ": starting");
  $request_method = strtolower($_SERVER['REQUEST_METHOD']);
  switch($request_method)
    {
    case 'put':
      $putdata = fopen("php://input", "r");
      $data = '';
      //      error_log($prefix . " starting to read...");
      while ($putchunk = fread($putdata, 1024))
	{
	  //	  error_log("Read chunk: $putchunk");
	  $data .= $putchunk;
	}
      fclose($putdata);
      break;
    case 'post':
      if (array_key_exists('file', $_FILES)) {
	$errorcode = $_FILES['file']['error'];
	if ($errorcode != 0) {
	  // An error occurred with the upload.
	  if ($errorcode == UPLOAD_ERR_NO_FILE) {
	    $error = "No file was uploaded.";
	  } else {
	    $error = "Unknown upload error (code = $errorcode).";
	  }
	  //	  error_log($prefix . ": $error");
	} else {
	  $msg_file = $_FILES["file"]["tmp_name"];
	}
      }
      break;
    }
   
  //  error_log($prefix . ": finished switch");
  //  error_log("Data = " . $data);

  // Now process the data
  $data = smime_decrypt($data);
  $msg = smime_validate($data);
  // XXX Error check smime_validate result here
   
  $funcargs = parse_message($msg);
  $result = call_user_func($funcargs[0], $funcargs[1]);
  //  error_log("RESULT = " . $result);
  $output = encode_result($result);
  //   error_log("RESULT(enc) = " . $output);
  //   error_log("RESULT(dec) = " . decode_result($output));
  $output = smime_sign_message($output);
  $output = smime_encrypt($output);
  //  error_log("BEFORE PRINT:" . $output);
  print $output;
}

?>
