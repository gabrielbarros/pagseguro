<?php
class HttpRequest {

    public $charset = 'UTF-8';
    public $userAgent = 'curl';
    public $followLocation = true;
    public $connectTimeout = 30;
    public $timeout = 30;
    public $autoReferer = true;
    public $maxRedirs = 10;
    public $postContentType;

    private $url;
    private $urlInfo;
    private $getParam = array();
    private $postParam = array();
    private $upload = false;
    private $cookies;
    private $headers = array();
    private $options = array();
    private $username;
    private $password;

    // Retorno
    public $error;
    public $errorMsg;
    public $responseText;
    public $responseHeaders;
    public $status;


    const UA_IE10 = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)';
    const UA_IE9 = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)';
    const UA_IE8 = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)';
    const UA_IE7 = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)';
    const UA_FIREFOX = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0';
    const UA_CHROME = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36';
    const UA_OPERA = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.149 Safari/537.36 OPR/20.0.1387.77';
    const UA_SAFARI = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2';
    const UA_ANDROID = 'Mozilla/5.0 (Linux; U; Android 4.0.2; en-us; Galaxy Nexus Build/ICL53F) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
    const UA_IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_2 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A4449d Safari/9537.53';
    const UA_IPAD = 'Mozilla/5.0 (iPad; CPU OS 7_0_2 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A501 Safari/9537.53';
    const UA_GOOGLEBOT = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';


    public function setCookies($cookie) {
        if (is_array($cookie)) {
            $this->cookies = http_build_query($cookie, '', '; ', PHP_QUERY_RFC3986);
        }

        else {
            $this->cookies = $cookie;
        }
    }

    public function setHeaders($headers) {
        foreach ($headers as $key => $value) {
            $this->headers[] = $key . ': ' . $value;
        }
    }

    public function setOptions($options) {
        $this->options = $options;
    }

    public function setAuthentication($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    public function get($url, $param = array()) {
        return $this->request('GET', $url, $param);
    }

    public function post($url, $param = array(), $param2 = array()) {
        return $this->request('POST', $url, $param, $param2);
    }

    public function head($url, $param = array()) {
        return $this->request('HEAD', $url, $param);
    }

    public function request($method, $url, $param = array(), $param2 = array()) {

        $this->url = $url;

        // Obter informações da URL: host, path, scheme
        $this->urlInfo = parse_url($url);

        // CURL options
        $options = array();

        // Validar URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL');
        }

        $this->getParam = $param;

        if ($method === 'POST') {
            $this->getParam = $param2;

            $options[CURLOPT_POST] = true;

            // Formulário
            if (is_array($param)) {

                // Mesclar com possíveis variáveis vindas do uploadFile()
                $this->postParam = array_merge($this->postParam, $param);

                if ($this->upload) {
                    $options[CURLOPT_POSTFIELDS] = $this->postParam;
                }
                else {
                    $options[CURLOPT_POSTFIELDS] = http_build_query($this->postParam);
                    $this->postContentType = 'application/x-www-form-urlencoded; charset=' . $this->charset;
                }
            }

            // JSON, XML, etc...
            else {
                $options[CURLOPT_POSTFIELDS] = $param;
            }

            if (isset($this->postContentType)) {
                $this->setHeaders(array(
                    'Content-Type' => $this->postContentType
                ));
            }
        }

        elseif ($method === 'HEAD') {
            $options[CURLOPT_NOBODY] = true;
            $this->followLocation = false;
        }

        elseif ($method !== 'GET') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        $options[CURLOPT_URL] = $this->getFullUrl();
        $options[CURLOPT_HEADER] = true;
        $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        $options[CURLOPT_CONNECTTIMEOUT] = $this->connectTimeout;
        $options[CURLOPT_TIMEOUT] = $this->timeout;
        $options[CURLOPT_MAXREDIRS] = $this->maxRedirs;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_ENCODING] = ''; // Accept: */*
        $options[CURLOPT_AUTOREFERER] = $this->autoReferer;
        $options[CURLOPT_FOLLOWLOCATION] = $this->followLocation;
        $options[CURLOPT_USERAGENT] = $this->userAgent;
        $options[CURLOPT_HTTPHEADER] = $this->headers;


        // Especificar certificado SSL para acessar páginas de forma segura
        if (strtolower($this->urlInfo['scheme']) === 'https') {
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            $options[CURLOPT_SSL_VERIFYHOST] = 2;
            $options[CURLOPT_CAINFO] = __DIR__ . DIRECTORY_SEPARATOR . 'cacert.pem';
        }
        else {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        // Usuário e senha para acessar a página
        if (isset($this->username, $this->password)) {
            $options[CURLOPT_USERPWD] = $this->username . ':' . $this->password;
        }

        // Especificar cookie para acessar a página?
        if (isset($this->cookies)) {
            //$options[CURLOPT_COOKIEJAR] = 'cookie.txt';
            //$options[CURLOPT_COOKIEFILE] = 'cookie.txt';
            //$options[CURLOPT_COOKIESESSION] = true;
            $options[CURLOPT_COOKIE] = $this->cookies;
        }


        // Custom CURL options
        foreach ($this->options as $key => $value) {
            $options[$key] = $value;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $output = curl_exec($curl);

        // Se ocorreu um erro ao tentar solicitar a URL...
        if (curl_errno($curl))  {
            $this->error = true;
            $this->errorMsg = curl_error($curl);
        }

        else {
            $this->error = false;
            $curlInfo = curl_getinfo($curl);

            if ($method === 'HEAD') {
                $this->responseText = $output;
                $responseHeaders = $output;
            }
            else {
                $headerSize = $curlInfo['header_size'];
                $this->responseText = substr($output, $headerSize);
                $responseHeaders = substr($output, 0, $headerSize);
            }

            $this->responseHeaders = $this->headersToArray($responseHeaders);
            $this->status = $curlInfo['http_code'];
        }
    }

    private function getFullUrl() {
        $url = $this->url;

        if (count($this->getParam)) {
            $buildQuery = http_build_query($this->getParam);

            if (isset($this->urlInfo['query'])) {
                $url .= '&' . ltrim($buildQuery, '&');
            }
            else {
                $url .= '?' . ltrim($buildQuery, '?');
            }
        }

        return $url;
    }

    private function headersToArray($headers) {

        $lines = explode("\r\n", trim($headers));
        $arrHeaders = array();

        foreach ($lines as $value) {
            $parts = explode(':', $value, 2);

            if (count($parts) > 1) {
                $arrHeaders[strtolower($parts[0])] = $parts[1];
            }
        }

        return $arrHeaders;
    }

    // Especificar um arquivo para upload
    public function uploadFile($key, $path, $mimetype) {

        $filename = basename($path);

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $file = new CURLFile($path, $mimetype, $filename);
        }

        else {
            $file = '@' . $path . ";type={$mimetype};filename={$filename}";
        }

        $this->upload = true;
        $this->postParam[$key] = $file;
    }
}
