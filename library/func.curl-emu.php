<?php

/* A quick emulator for common curl function so code based on CURL works on curl free hosting */
if (!function_exists('curl_init')) {
    // The curl option constants
    define('CURLOPT_RETURNTRANSFER', 19913);
    define('CURLOPT_SSL_VERIFYPEER', 64);
    define('CURLOPT_SSL_VERIFYHOST', 81);
    define('CURLOPT_USERAGENT', 10018);
    define('CURLOPT_HEADER', 42);
    define('CURLOPT_CUSTOMREQUEST', 10036);
    define('CURLOPT_POST', 47);
    define('CURLOPT_POSTFIELDS', 10015);
    define('CURLOPT_HTTPHEADER', 10023);
    define('CURLOPT_URL', 10002);
    define('CURLOPT_HTTPGET', 80); // this could be a good idea to handle params as array
    define('CURLOPT_CONNECTTIMEOUT', 78);
    define('CURLOPT_TIMEOUT', 13);
    define('CURLOPT_CAINFO', 10065);
    define('CURLOPT_SSLVERSION', 32);
    define('CURLOPT_FOLLOWLOCATION', 52);
    define('CURLOPT_FORBID_REUSE', 75);
    define('CURLOPT_HTTP_VERSION', 84);
    define('CURLOPT_MAXREDIRS', 68);
    define('CURLOPT_ENCODING', 10102);

    // curl info constants
    define('CURLINFO_HEADER_SIZE', 2097163);
    define('CURLINFO_HTTP_CODE', 2097154);
    define('CURLINFO_HEADER_OUT', 2); // This seems to be an option?
    define('CURLINFO_TOTAL_TIME', 3145731);

    define('CURLE_SSL_CACERT', 60);
    define('CURLE_SSL_PEER_CERTIFICATE', 51);
    define('CURLE_SSL_CACERT_BADFILE', 77);

    define('CURLE_COULDNT_CONNECT', 7);
    define('CURLE_OPERATION_TIMEOUTED', 28);
    define('CURLE_COULDNT_RESOLVE_HOST', 6);

    class CurlEmu
    {
        // Storing the result in here
        private $result;

        // The headers of the result will be stored here
        private $responseHeader;

        // url for request
        private $url;

        // options
        private $options = [];

        public function __construct($url = null)
        {
            $this->url = $url;
        }

        public function setOpt($option, $value)
        {
            $this->options[$option] = $value;
        }

        public function setOptArray($options)
        {
            foreach ($options as $option => $value) {
                $this->setOpt($option, $value);
            }
        }

        public function getInfo($opt = 0)
        {
            if (!$this->result) {
                $this->fetchResult();
            }

            $responseHeaderSize = 0;
            foreach ($this->responseHeader as $header)
                $responseHeaderSize += (strlen($header) + 2); // The one is for each newline

            $httpCode = 200;
            if (!empty($this->responseHeader[0]) && preg_match('#HTTP/\d+\.\d+ (\d+)#', $this->responseHeader[0], $matches)) {
                $httpCode = intval($matches[1]);
            }

            // opt
            if ($opt == CURLINFO_HEADER_SIZE)
                return $responseHeaderSize;

            if ($opt == CURLINFO_HTTP_CODE)
                return $httpCode;

            return [
                "url" => $this->url,
                "content_type" => "",
                "http_code" => $httpCode,
                "header_size" => $responseHeaderSize,
                "request_size" => 0,
                "filetime" => 0,
                "ssl_verify_result" => null,
                "redirect_count" => 0,
                "total_time" => 0,
                "namelookup_time" => 0,
                "connect_time" => 0,
                "pretransfer_time" => 0,
                "size_upload" => 0,
                "size_download" => 0,
                "speed_download" => 0,
                "speed_upload" => 0,
                "download_content_length" => 0,
                "upload_content_length" => 0,
                "starttransfer_time" => 0,
                "redirect_time" => 0,
                "certinfo" => 0,
                "request_header" => 0
            ];
        }

        public function exec()
        {
            $this->fetchResult();

            $fullResult = $this->result;

            if ($this->getValue(CURLOPT_HEADER, false)) {
                $fullResult = implode("\r\n", $this->responseHeader) . "\r\n\r\n" . $this->result;
            }

            return $this->getValue(CURLOPT_RETURNTRANSFER, false) ? $fullResult : print ($fullResult);
        }

        private function fetchResult()
        {
            // Create the context for this request based on the curl parameters

            // Determine the method
            if (!$this->getValue(CURLOPT_CUSTOMREQUEST, false) && $this->getValue(CURLOPT_POST, false)) {
                $method = 'POST';
            } else {
                $method = $this->getValue(CURLOPT_CUSTOMREQUEST, 'GET');
            }

            // Add the post header if type is post and it has not been added
            if ($method == 'POST') {
                if (is_array($this->getValue(CURLOPT_HTTPHEADER))) {
                    $found = false;
                    foreach ($this->getValue(CURLOPT_HTTPHEADER, []) as $header) {
                        if (strtolower($header) == strtolower('Content-type: application/x-www-form-urlencoded')) {
                            $found = true;
                        }
                    }

                    // add post header if not found
                    if (!$found) {
                        $headers = $this->getValue(CURLOPT_HTTPHEADER, []);
                        $headers[] = 'Content-type: application/x-www-form-urlencoded';
                        $this->setOpt(CURLOPT_HTTPHEADER, $headers);
                    }
                }
            }

            // Determine the content which can be an array or a string
            if (is_array($this->getValue(CURLOPT_POSTFIELDS))) {
                $content = http_build_query($this->getValue(CURLOPT_POSTFIELDS, []));
            } else {
                $content = $this->getValue(CURLOPT_POSTFIELDS, "");
            }

            // get timeout
            $timeout = $this->getValue(CURLOPT_TIMEOUT, 60);
            $connectTimeout = $this->getValue(CURLOPT_CONNECTTIMEOUT, 30);

            // take bigger timeout
            if ($connectTimeout > $timeout)
                $timeout = $connectTimeout;

            $headers = $this->getValue(CURLOPT_HTTPHEADER, "");
            if (is_array($headers)) {
                $headers = join("\r\n", $headers);
            }

            // 'http' instead of $parsedUrl['scheme']; https doest work atm
            $options = [
                'http' => [
                    "timeout" => $timeout,
                    "ignore_errors" => true,
                    'method' => $method,
                    'header' => $headers,
                    'content' => $content
                ]
            ];

            $options["http"]["follow_location"] = $this->getValue(CURLOPT_FOLLOWLOCATION, 1);

            // get url from options
            if ($this->getValue(CURLOPT_URL, false))
                $this->url = $this->getValue(CURLOPT_URL);

            // SSL settings when set
            // $parsedUrl = parse_url($this->url);
            // if ($parsedUrl['scheme'] == 'https') {
            //   $context['https']['ssl'] = [
            //     'verify_peer' => $this->getValue(CURLOPT_SSL_VERIFYPEER, false)
            //   ];
            // }

            $context = stream_context_create($options);
            $this->result = @file_get_contents($this->url, false, $context);

            if ($this->result === false) {
                $this->lastError = error_get_last();
            }

            $this->responseHeader = $http_response_header ?? [];
        }

        private function getValue($value, $default = null)
        {
            if (isset($this->options[$value]) && $this->options[$value]) {
                return $this->options[$value];
            }
            return $default;
        }

        public function errNo()
        {
            if (isset($this->lastError)) {
                return $this->lastError['type'] ?? 0;
            }
            return 0;
        }

        public function error()
        {
            if (isset($this->lastError)) {
                return $this->lastError['message'] ?? "";
            }
            return "";
        }

        public function close()
        {
        }
    }

    function curl_init($url = null)
    {
        return new CurlEmu($url);
    }

    function curl_setopt($ch, $option, $value)
    {
        $ch->setOpt($option, $value);
    }

    function curl_exec($ch)
    {
        return $ch->exec();
    }

    function curl_getinfo($ch, $option = 0)
    {
        return $ch->getInfo($option);
    }

    function curl_errno($ch)
    {
        return $ch->errNo();
    }

    function curl_error($ch)
    {
        return $ch->error();
    }

    function curl_close($ch)
    {
        return $ch->close();
    }

    function curl_setopt_array($ch, $options)
    {
        $ch->setOptArray($options);
    }

    function curl_version()
    {
        return [
            'version_number' => '7.0.0',
            'version' => '7.0.0',
            'ssl_version_number' => 0,
            'ssl_version' => 'OpenSSL/0.0.0',
            'libz_version' => '',
            'host' => 'unknown',
            'features' => 0,
            'protocols' => ['http', 'https'],
        ];
    }
}