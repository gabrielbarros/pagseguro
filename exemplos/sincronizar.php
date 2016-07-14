<?php
require '../HttpRequest.class.php';
require 'token.php';
require '../PagSeguro.php';
require '../PagSeguroAssinatura.php';
require '../PagSeguroTransacao.php';
require '../PagSeguroNotificacao.php';

// IMPORTANTE! Use https em localhost para testar na sandbox

$sandbox = true;
$pagseguro = new PagSeguroNotificacao($sandbox);

$pagseguro->email = PAGSEGURO_EMAIL;
$pagseguro->token = PAGSEGURO_TOKEN;
$pagseguro->user_agent = 'Meu Site (+https://meusite.com.br)'; // opcional

$pagseguro->callback = function($xml) {
    // salvar no banco de dados...

    // retornar true se salvar no banco de dados
    // retornar false se for notificação duplicada ou inválida
    return true;
};


try {
    $numero = 0;

    // Sincronizar notificações de assinaturas
    $numero += $pagseguro->sincronizar(PagSeguroNotificacao::ASSINATURA);

    // Sincronizar notificações de transações
    $numero += $pagseguro->sincronizar(PagSeguroNotificacao::TRANSACAO);

    echo "Número de notificações sincronizadas: {$numero}";
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}


