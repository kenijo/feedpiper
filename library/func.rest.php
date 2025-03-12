<?php

/**
 * Call REST API function
 *
 * This function makes a REST API call using cURL. It supports GET, POST, and PUT methods.
 * If the cURL call fails, it falls back to using file_get_contents.
 *
 * @param string $method The HTTP method to use (GET, POST, PUT).
 * @param string $url The URL to send the request to.
 * @param array|false $header Optional. An array of headers to send with the request.
 * @param array|null $data Optional. Data to send with the request (for POST and PUT methods).
 * @param bool $check_ssl Optional. Whether to check the SSL certificate. Default is true.
 * @return string The response from the API call.
 * @throws Exception If both cURL and file_get_contents fail.
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
 *
 * This function is used as a fallback when the cURL call fails. It uses file_get_contents
 * to make the HTTP request.
 *
 * @param string $method The HTTP method to use (GET, POST, PUT).
 * @param string $url The URL to send the request to.
 * @param array|false $header Optional. An array of headers to send with the request.
 * @param array|null $data Optional. Data to send with the request (for POST and PUT methods).
 * @return string The response from the API call.
 * @throws Exception If the file_get_contents call fails.
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
