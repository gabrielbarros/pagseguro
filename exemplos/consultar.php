<?php
require '../HttpRequest.class.php';
require 'token.php';
require '../PagSeguro.php';
require '../PagSeguroNotificacao.php';

$sandbox = true;
$pagseguro = new PagSeguroNotificacao($sandbox);

$pagseguro->email = PAGSEGURO_EMAIL;
$pagseguro->token = PAGSEGURO_TOKEN;
$pagseguro->user_agent = 'Meu Site (+https://meusite.com.br)'; // opcional


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
