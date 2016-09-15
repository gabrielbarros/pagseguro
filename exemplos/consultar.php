<?php
require 'autoload.php';

use PagSeguro\PagSeguroConsulta;
use PagSeguro\PagSeguroNotificacao;
use PagSeguro\PagSeguroException;

$sandbox = true;
$pagseguro = new PagSeguroConsulta($sandbox);

$pagseguro->email = PAGSEGURO_EMAIL;
$pagseguro->token = PAGSEGURO_TOKEN;
$pagseguro->userAgent = 'Meu Site (+https://meusite.com.br)'; // opcional


try {
    $xml = $pagseguro->consultar(
        PagSeguroNotificacao::TRANSACAO,
        array('code' => 'E35D4859-6011-42DA-9CA6-56137A1E3318')
    );

    header('Content-Type: text/plain');
    print_r($xml);
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}
