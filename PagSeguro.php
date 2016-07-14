<?php
abstract class PagSeguro {
    public $debug = false;
    public $auto_redirect = false;

    public $user_agent = 'Mini PagSeguro';

    public $email;
    public $token;
    public $notificacao_url;
    public $redirect_url;

    protected $url;
    protected $api_url;
    protected $sandbox;

    public function __construct($sandbox = false) {
        $this->sandbox = $sandbox;

        if ($sandbox) {
            $this->url = 'https://sandbox.pagseguro.uol.com.br';
            $this->api_url = 'https://ws.sandbox.pagseguro.uol.com.br';
        }
        else {
            $this->url = 'https://pagseguro.uol.com.br';
            $this->api_url = 'https://ws.pagseguro.uol.com.br';
        }
    }

    protected function erro($msg) {
        throw new PagSeguroException($msg);
    }

    protected function random_string($length) {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length / 2));
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length / 2));
        }

        $chars = '0123456789abcdef';
        $str = '';

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, 15)];
        }

        return $str;
    }

    protected function go($url) {
        if ($this->auto_redirect) {
            header('Location: ' . $url);
            exit;
        }

        return $url;
    }

    protected function request($method, $path, $param = array()) {
        set_time_limit(0);

        $http = new HttpRequest();
        $http->user_agent = $this->user_agent;

        $default_param = array(
            'email' => $this->email,
            'token' => $this->token
        );

        $url = $this->api_url . $path;

        $response = $http->request(
            $method, $url,
            array_merge($default_param, $param)
        );

        if ($response['error']) {
            $this->erro('Não foi possível se comunicar com o PagSeguro');
        }

        // No método POST, os dados vêm em UTF-8;
        // No método GET, os dados vêm em ISO-8859-1
        if ($method === 'GET') {
            $response['content'] = utf8_encode($response['content']);
        }

        // Erros?
        // 401: E-mail/token inválido
        // 404: Notificação/assinatura/transação não encontrada
        $http_code = $response['info']['http_code'];
        if ($http_code !== 200) {
            $msg = 'Ocorreu um erro com o código HTTP ' . $http_code;

            // O normal é retornar um txt ou xml, mas às vezes o PagSeguro
            // retorna uma página html gigante quando está em manutenção
            if (strlen($response['content']) < 500) {
                $msg .= "\n\n" . $response['content'] . "\n\n";
            }

            $this->erro($msg);
        }

        // Suprimir erros caso o XML seja inválido
        libxml_use_internal_errors(true);

        // Obter XML
        $xml = simplexml_load_string($response['content']);

        if ($xml === false) {
            $this->erro('XML inválido');
        }

        return $xml;
    }


    protected function param_pessoa($pessoa) {
        $param = array();

        // Pessoa (OPCIONAL!): nome, ddd, telefone, e-mail
        if (isset($pessoa['nome'])) {
            $param['senderName'] = $pessoa['nome'];
        }

        if (isset($pessoa['ddd'])) {
            $param['senderAreaCode'] = $pessoa['ddd'];
        }

        if (isset($pessoa['telefone'])) {
            $param['senderPhone'] = $pessoa['telefone'];
        }

        if (isset($pessoa['email'])) {
            $param['senderEmail'] = $pessoa['email'];
        }

        return $param;
    }


    protected function param_endereco($endereco) {
        $param = array();

        // Endereço (OPCIONAL!): rua, numero, complemento, bairro,
        // cep, cidade, estado, pais

        if (isset($endereco['rua'])) {
            $param['shippingAddressStreet'] = $endereco['rua'];
        }

        if (isset($endereco['numero'])) {
            $param['shippingAddressNumber'] = $endereco['numero'];
        }

        if (isset($endereco['complemento'])) {
            $param['shippingAddressComplement'] = $endereco['complemento'];
        }

        if (isset($endereco['bairro'])) {
            $param['shippingAddressDistrict'] = $endereco['bairro'];
        }

        if (isset($endereco['cep'])) {
            $param['shippingAddressPostalCode'] = $endereco['cep'];
        }

        if (isset($endereco['cidade'])) {
            $param['shippingAddressCity'] = $endereco['cidade'];
        }

        if (isset($endereco['estado'])) {
            $param['shippingAddressState'] = $endereco['estado'];
        }

        if (isset($endereco['pais'])) {
            $param['shippingAddressCountry'] = $endereco['pais'];
        }

        return $param;
    }
}

class PagSeguroException extends Exception {}
