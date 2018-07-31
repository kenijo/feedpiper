<?php

// This code is an example on how to do a REST API call

// Parameters
// ?category=xxx&sortBy=xxx&pageSize=xxx&pageNumber=xxx&sortOrder=xxx

// Category
//    Select a specific category
//      Azure
//      Debuggers
//      Extension Packs
//      Formatters
//      Keymaps
//      Language Packs
//      Linters
//      Other
//      Programming Languages
//      SCM Providers
//      Snippets
//      Themes
if (isset($_GET['category']))
{
  $categorySelection = $_GET['category'];

  $categoryArray = array(
    "Azure",
    "Debuggers",
    "Extension Packs",
    "Formatters",
    "Keymaps",
    "Language Packs",
    "Linters",
    "Other",
    "Programming Languages",
    "SCM Providers",
    "Snippets",
    "Themes"
  );

  if($key = array_search(strtolower($categorySelection), array_map('strtolower', $categoryArray)))
  {
    $categoryValue = '
    ,
    {
      "filterType": 5,
      "value": "' . $categoryArray[$key] . '"
    }
    ';
  }
  else
  {
    // Defaults to all extensions
    $categoryValue = '';
  }
}

// sortBy
//    Sort extensions by
//      Downloads (4)
//      Name (2)
//      PublishedDate (10)
//      Publisher (3)
//      Rating (12)
//      Trending (8)
//      UpdatedDate (1)
if (isset($_GET['sortBy']))
{
  $sortBySelection = $_GET['sortBy'];

  $sortByArray = array(
    1 => "UpdatedDate",
    2 => "Name",
    3 => "Publisher",
    4 => "Downloads",
    8 => "Trending",
    10 => "PublishedDate",
    12 => "Rating"
  );

  if($key = array_search(strtolower($sortBySelection), array_map('strtolower', $sortByArray)))
  {
    $sortByValue = $sortByArray[$key];
  }
  else
  {
    // Defaults to sorting by PublishedDate
    $sortByValue = 10;
  }
}

// pageSize
//    Number of documents to retrieve
if (isset($_GET['pageSize']))
{
  $pageSize = $_GET['pageSize']);
}
else
{
  $pageSize = 50;
}

// pageNumber
//    Page number to retrieve
//    If pageSize=1 and pageNumber=2 then the result is extension 2.
//    It is equivalent of retrieving the second extension of pagesize=2 and pageNumber=1
$pageNumber = 1;
if (isset($_GET['pageNumber']))
{
  $pageNumber = $_GET['pageNumber'];
}
else
{
  $pageNumber = 1;
}

// sortOrder
//    Sort extensions Descending (0) or Ascending (1)
if (isset($_GET['sortOrder']))
{
  $sortOrder = $_GET['sortOrder'];
}
else
{
  $sortOrder = 0;
}

// assetTypes
//    Retrieves axtra details on the extensions

$data_json = '
{
    "assetTypes": [
        "Microsoft.VisualStudio.Services.Icons.Default",
        "Microsoft.VisualStudio.Services.Icons.Branding",
        "Microsoft.VisualStudio.Services.Icons.Small"
    ],
    "filters": [
        {
            "criteria": [
                {
                    "filterType": 8,
                    "value": "Microsoft.VisualStudio.Code"
                },
                {
                    "filterType": 10,
                    "value": "target:\"Microsoft.VisualStudio.Code\""
                },
                {
                    "filterType": 12,
                    "value": "37888"
                }' . $categoryValue . '
            ],
            "pageSize": ' . $pageSize . ',
            "pageNumber": ' . $pageNumber . ',
            "sortBy": ' . $sortByValue . ',
            "sortOrder": ' . $sortOrder . ',
        }
    ],
    "flags": 870
}
';

$method = "POST";

$url="https://marketplace.visualstudio.com/_apis/public/gallery/extensionquery";

$header_array = array(
    'Content-Type: application/json',
    'Accept: api-version=3.0-preview',
    'Content-Length: ' . strlen($data_json) ,
  );

function CallAPI($method, $url, $header = false, $data = false)
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    switch ($method)
    {
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
    if(!$result)
    {
      die("Connection Failure");
    }
    curl_close($curl);
    return $result;
}

$output_json = CallAPI($method, $url, $header_array, $data_json);
$output_array = json_decode($output_json, true);
var_dump($output_array);
