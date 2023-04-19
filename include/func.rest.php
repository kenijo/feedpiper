<?php

/**
 * URL parsing functions
 */

function CallRestAPI($method, $url, $header = false, $data = null, $check_ssl = true)
{
  $curl = curl_init();

  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $check_ssl);
  curl_setopt($curl, CURLOPT_HEADER, false);

  if ($header) {
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
  }

  switch ($method) {
    case "POST":
      curl_setopt($curl, CURLOPT_POST, true);

      if ($data)
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      break;
    case "PUT":
      curl_setopt($curl, CURLOPT_PUT, true);
      break;
    default:
      if ($data)
        $url = sprintf("%s?%s", $url, http_build_query($data));
  }

  // Optional Authentication:
  //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  //curl_setopt($curl, CURLOPT_USERPWD, "username:password");

  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  // EXECUTE:
  $result = curl_exec($curl);
  if (!$result) {
    // If curl fails, try with file_get_contents()
    $opts = array('http' =>
    array(
      'method'  => $method,
      'header'  => $header,
      'content' => $data,
      'timeout' => 60
    ));
    $context  = stream_context_create($opts);
    $result =  file_get_contents($url, false, $context);
    if (!$result) {
      die("Connection Failure");
    }
  }

  curl_close($curl);
  return $result;
}
