<?php
class HttpRequest {

    public $charset = 'UTF-8';
    public $user_agent = 'cURL';
    public $follow_location = true;
    public $timeout = 30;
    public $connect_timeout = 30;
    public $auto_referer = true;
    public $max_redirs = 10;
    public $post_content_type;
    public $debug = false;

    private $upload = false;
    private $cookies;
    private $headers = array();
    private $custom_headers = array();
    private $custom_options = array();
    private $username;
    private $password;

    private $preserve_upload = false;
    private $preserve_cookies = false;
    private $preserve_headers = false;
    private $preserve_options = false;
    private $preserve_authentication = false;

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


    private function get_params($arr, $separator = '&') {
        $str = '';

        foreach ($arr as $key => $value) {
            $str .= $key . '=' . rawurlencode($value) . $separator;
        }

        return rtrim($str, $separator);
    }


    public function setCookies($cookie, $preserve = false) {
        if (is_array($cookie)) {
            $this->cookies = $this->get_params($cookie, '; ');
        }

        else {
            $this->cookies = $cookie;
        }

        $this->preserve_cookies = $preserve;
    }

    public function setHeaders($headers) {
        foreach ($headers as $key => $value) {
            $this->headers[] = $key . ': ' . $value;
        }
    }

    public function setCustomHeaders($headers, $preserve = false) {

        foreach ($headers as $key => $value) {
            $this->custom_headers[] = $key . ': ' . $value;
        }

        $this->preserve_headers = $preserve;
    }

    public function setCustomOptions($options, $preserve = false) {
        $this->custom_options = $options;
        $this->preserve_options = $preserve;
    }

    public function setAuthentication($username, $password, $preserve = false) {
        $this->username = $username;
        $this->password = $password;
        $this->preserve_authentication = $preserve;
    }

    public function setUpload($upload, $preserve = false) {
        $this->upload = $upload;
        $this->preserve_upload = $preserve;
    }

    public function __toString() {
        return print_r(array_map(function($elem) {
            if ($elem === true) {
                return 'true';
            }
            if ($elem === false) {
                return 'false';
            }
            return $elem;
        }, get_object_vars($this)), true);
    }


    public function get($url, $data = array()) {
        return $this->request('GET', $url, $data);
    }

    public function post($url, $data = array(), $data2 = array()) {
        return $this->request('POST', $url, $data, $data2);
    }

    public function request($method, $url, $data = array(), $data2 = array()) {
        $options = array();

        // Validar URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL');
        }

        // Obter informações da URL: host, path, scheme
        $url_info = parse_url($url);


        if ($method === 'GET') {
            if (count($data)) $url .= '?' . http_build_query($data);
        }

        elseif ($method === 'POST') {
            if (count($data2)) $url .= '?' . http_build_query($data2);

            $options[CURLOPT_POST] = true;

            // Upload
            if ($this->upload) {
                $options[CURLOPT_POSTFIELDS] = $data;
                // $this->post_content_type = 'multipart/form-data';
            }

            // Formulário
            elseif (is_array($data)) {
                $options[CURLOPT_POSTFIELDS] = $this->get_params($data);
                $this->post_content_type = 'application/x-www-form-urlencoded; charset=' . $this->charset;
            }

            // JSON, XML, etc...
            else {
                $options[CURLOPT_POSTFIELDS] = $data;
            }

            if (isset($this->post_content_type)) {
                $this->setCustomHeaders(array(
                    'Content-Type' => $this->post_content_type
                ));
            }
        }

        elseif ($method === 'HEAD') {
            $options[CURLOPT_HEADER] = true;
            $options[CURLOPT_NOBODY] = true;
            $this->follow_location = false;
        }

        else {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        // Custom options
        foreach ($this->custom_options as $key => $value) {
            $options[$key] = $value;
        }

        if (!isset($url_info['path'])) $url_info['path'] = '/';

        // Headers obrigatórios
        $this->setHeaders(array(
            'Host' => $url_info['host'],
            'Method' => $method,
            'Path' => $url_info['path'],
            'Scheme' => $url_info['scheme'],
            'Version' => 'HTTP/1.1',
            'Accept-Language' => 'pt-BR,pt;q=0.8,en-US;q=0.6,en;q=0.4',
            'Cache-Control' => 'max-age=0',
            'Connection' => 'keep-alive'
        ));

        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        $options[CURLOPT_CONNECTTIMEOUT] = $this->connect_timeout;
        $options[CURLOPT_TIMEOUT] = $this->timeout;
        $options[CURLOPT_MAXREDIRS] = $this->max_redirs;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_ENCODING] = '';
        $options[CURLOPT_AUTOREFERER] = $this->auto_referer;
        $options[CURLOPT_FOLLOWLOCATION] = $this->follow_location;
        $options[CURLOPT_USERAGENT] = $this->user_agent;
        $options[CURLOPT_HTTPHEADER] = array_merge($this->headers, $this->custom_headers);

        // Especificar certificado SSL para acessar páginas de forma segura?
        if (strtolower($url_info['scheme']) === 'https') {
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
            $options[CURLOPT_COOKIEJAR] = 'cookie.txt';
            $options[CURLOPT_COOKIEFILE] = 'cookie.txt';
            $options[CURLOPT_COOKIESESSION] = true;
            $options[CURLOPT_COOKIE] = $this->cookies;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $output = curl_exec($curl);

        // Se ocorreu um erro ao tentar solicitar a URL...
        if (curl_errno($curl))  {
            $arr = array(
                'error' => true,
                'error_info' => curl_error($curl),
                'content' => '',
                'info' => array()
            );
        }

        // Se conseguiu acessar a URL especificada...
        else {
            $arr = array(
                'error' => false,
                'error_info' => '',
                'content' => $output,
                'info' => curl_getinfo($curl)
            );

            curl_close($curl);
        }

        if (!$this->preserve_upload)   $this->upload = false;
        if (!$this->preserve_cookies)  unset($this->cookies);
        if (!$this->preserve_headers)  $this->custom_headers = array();
        if (!$this->preserve_options)  $this->preserve_options = array();
        if (!$this->preserve_authentication) unset($this->username, $this->password);

        if (!$this->debug) {
            return $arr;
        }

        header('Content-Type: text/plain');
        print_r($arr);
    }

    // Especificar um arquivo para upload
    public function upload_file($path, $mimetype, $filename) {

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            return new CURLFile($path, $mimetype, $filename);
        }

        return '@' . $path . ";type={$mimetype};filename={$filename}";
    }

    // Descobrir para onde uma URL redireciona
    public function extract_url($url) {
        $info = $this->request('HEAD', $url);

        if ($info['error']) {
            return false;
        }

        return $info['info']['redirect_url'];
    }

    // Obter o código HTTP que o servidor envia de volta, dada uma URL
    public function http_code($url) {
        $info = $this->request('HEAD', $url);

        if ($info['error']) {
            return false;
        }

        return $info['info']['http_code'];
    }

    // Descobrir o tamanho de um arquivo
    public function file_size($url) {
        $info = $this->request('HEAD', $url);

        if ($info['error']) {
            return false;
        }

        return $info['info']['download_content_length'];
    }

    // Obter cabeçalhos HTTP (array)
    public function headers($url) {
        $headers = $this->request('HEAD', $url);

        if ($headers['error']) {
            return false;
        }

        return trim($headers['content']);
    }

    // Salvar página
    public function save($url, $path) {
        $page = $this->get($url);

        if ($page['error']) {
            return false;
        }

        $content = $page['content'];
        file_put_contents($path, $content);
        //return $page;
    }
}
