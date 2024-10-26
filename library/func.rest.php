<?php

/**
 * Call REST API function
 */
function callRestAPI($method, $url, $header = false, $data = null, $check_ssl = true)
{
    $curl = curl_init();

    $curlOptions = [
        CURLOPT_SSL_VERIFYPEER => $check_ssl,
        CURLOPT_HEADER => false,
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60
    ];

    if ($header) {
        $curlOptions[CURLOPT_HTTPHEADER] = $header;
    }

    switch ($method) {
        case "POST":
            $curlOptions[CURLOPT_POST] = true;
            if ($data) {
                $curlOptions[CURLOPT_POSTFIELDS] = $data;
            }
            break;
        case "PUT":
            $curlOptions[CURLOPT_PUT] = true;
            break;
        default:
            if ($data) {
                $url .= '?' . http_build_query($data);
                $curlOptions[CURLOPT_URL] = $url;
            }
    }

    curl_setopt_array($curl, $curlOptions);

    $result = curl_exec($curl);

    if ($result === false) {
        $result = fallbackFileGetContents($method, $url, $header, $data);
    }

    curl_close($curl);
    return $result;
}

/**
 * Fallback function using file_get_contents
 */
function fallbackFileGetContents($method, $url, $header, $data)
{
    $opts = [
        'http' => [
            'method' => $method,
            'header' => $header ? implode("\r\n", $header) : '',
            'content' => $data,
            'timeout' => 60
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        throw new Exception("Connection Failure");
    }

    return $result;
}
