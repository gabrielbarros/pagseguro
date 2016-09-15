<?php
namespace PagSeguro;

use HttpRequest\HttpRequest;

abstract class PagSeguro {
    public $autoRedirect = false;

    public $userAgent = 'Mini PagSeguro';

    public $email;
    public $token;
    public $notificacaoUrl;
    public $redirectUrl;

    protected $url;
    protected $apiUrl;
    protected $sandbox;

    public function __construct($sandbox = false) {
        $this->sandbox = $sandbox;

        if ($sandbox) {
            $this->url = 'https://sandbox.pagseguro.uol.com.br';
            $this->apiUrl = 'https://ws.sandbox.pagseguro.uol.com.br';
        }
        else {
            $this->url = 'https://pagseguro.uol.com.br';
            $this->apiUrl = 'https://ws.pagseguro.uol.com.br';
        }
    }

    protected function erro($msg) {
        throw new PagSeguroException($msg);
    }

    protected function randomString($length) {
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
        if ($this->autoRedirect) {
            header('Location: ' . $url);
            exit;
        }

        return $url;
    }

    protected function request($method, $path, $param = array()) {
        set_time_limit(0);

        $http = new HttpRequest();
        $http->userAgent = $this->userAgent;

        $defaultParam = array(
            'email' => $this->email,
            'token' => $this->token
        );

        $url = $this->apiUrl . $path;
        $param = array_merge($defaultParam, $param);

        if ($method === 'GET') {
            $http->setQuery($param);
            $http->get($url);
        }
        else {
            $http->setBody($param);
            $http->post($url);
        }

        if ($http->error) {
            $this->erro('Não foi possível se comunicar com o PagSeguro');
        }


        // Erros?
        // 401: E-mail/token inválido
        // 404: Notificação/assinatura/transação não encontrada
        $httpCode = $http->status;
        if ($httpCode !== 200) {
            $msg = 'Ocorreu um erro com o código HTTP ' . $httpCode;

            // O normal é retornar um txt ou xml, mas às vezes o PagSeguro
            // retorna uma página html gigante quando está em manutenção
            if (strlen($http->responseText) < 500) {
                $msg .= "\n\n" . $http->responseText . "\n\n";
            }

            $this->erro($msg);
        }

        // Suprimir erros caso o XML seja inválido
        libxml_use_internal_errors(true);

        // Obter XML
        $xml = simplexml_load_string($http->responseText);

        if ($xml === false) {
            $this->erro('XML inválido');
        }

        return $xml;
    }


    protected function paramPessoa($pessoa) {
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


    protected function paramEndereco($endereco) {
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
