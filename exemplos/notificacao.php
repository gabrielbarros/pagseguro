<?php
require 'autoload.php';

use PagSeguro\PagSeguroConsulta;
use PagSeguro\PagSeguroNotificacao;
use PagSeguro\PagSeguroException;

// IMPORTANTE! Use https em localhost para testar na sandbox

$sandbox = true;
$pagseguro = new PagSeguroNotificacao($sandbox);

$pagseguro->email = PAGSEGURO_EMAIL;
$pagseguro->token = PAGSEGURO_TOKEN;
$pagseguro->userAgent = 'Meu Site (+https://meusite.com.br)'; // opcional

$pagseguro->callback = function($xml, $notificationType, $notificationCode,
                                 $manual) {

    /*
    $xml: o xml da consulta (ver abaixo)

    $notificationType:
        preApproval (assinatura) ou transaction (transação)

    $notificationCode:
        código da notificação

    $manual:
        true para notificações manuais (via sincronizar), false para automáticas


    Notificação de transação (http://bit.ly/29TOtxu)
    ----------------------------------------------------------------------------
    $xml->code        | código da transação
    $xml->reference   | o valor de $compra_id em compra-unica.php

    $xml->type        | 1: compra normal
                      | 11: assinatura

    $xml->status      | 1: aguardando pgto
                      | 2: em análise
                      | 3: paga
                      | 4: disponível
                      | 5: em disputa
                      | 6: devolvida
                      | 7: cancelada

    $xml->paymentMethod->type | 1: cartão de crédito
                              | 2: boleto
                              | 3: débito online
                              | 4: saldo PagSeguro
                              | 7: depósito em conta

    $xml->paymentMethod->code | 101: Visa
                              | 102: MasterCard
                              | 103: American Express
                              | (...)

    $xml->grossAmount | valor bruto da transação
    $xml->feeAmount   | taxa do PagSeguro
    $xml->feeAmount   | valor bruto menos taxas

    $xml->sender->name
    $xml->sender->email
    (...)

    $xml->shipping->address->street
    $xml->shipping->address->number
    $xml->shipping->address->complement
    (...)


    Notificação de assinatura
    ----------------------------------------------------------------------------
    $xml->code      | código identificador da assinatura
    $xml->tracker   | código identificador público

    $xml->status    | PENDING: iniciado o fluxo de pagamento
                    | ACTIVE: a transação que originou a assinatura foi paga
                    | CANCELLED: cancelada por falta de pagamento
                    | CANCELLED_BY_RECEIVER: cancelada pelo vendedor
                    | CANCELLED_BY_SENDER: cancelada pelo comprador
                    | EXPIRED: expirou por causa da data limite ou valor máximo

    $xml->reference | o valor de $assinatura_id em exemplo-assinatura.php

    $xml->charge    | auto: gerenciada automaticamente pelo PagSeguro
                    | manual: gerenciada pelo vendedor

    $xml->sender->name
    $xml->sender->email
    $xml->sender->address->street
    $xml->sender->address->number
    (...)


    ----------------------------------------------------------------------------
    Recomendado salvar no banco de dados:
    * -- id única da notificação
    * `notificacao_id` INT UNSIGNED NOT NULL AUTO_INCREMENT
    *
    * -- tipo da notificação: preApproval (assinatura), transaction (transação)
    * `notification_type` VARCHAR(20) NOT NULL,
    *
    * -- código da notificação
    * `notification_code` CHAR(39) NOT NULL UNIQUE KEY,
    *
    * -- código da assinatura do usuário (32) ou da transação (36)
    * `code` VARCHAR(36) NOT NULL,
    *
    * -- código público da assinatura
    * `tracker` VARCHAR(6),
    *
    * -- status da notificação
    * `status` VARCHAR(40),
    *
    * -- valor do pagamento, caso seja uma notificação de transação
    * `valor` VARCHAR(7),
    *
    * -- data da gravação da notificação
    * `notificacao_data` DATETIME NOT NULL,
    *
    * -- email do usuário no PagSeguro
    * `email_pagseguro` VARCHAR(60) NOT NULL
    */

    // retornar true se salvar no banco de dados
    // retornar false se for notificação duplicada ou inválida
    return true;
};


try {
    $salvou = $pagseguro->receberNotificacao($_POST);

    if ($salvou) {
        echo 'Notificação salva com sucesso';
    }
    else {
        echo 'Notificação inválida';
    }
}
catch (PagSeguroException $e) {

    // Importante não retornar HTTP 200 se der algum erro, para que
    // o PagSeguro volte a enviar a notificação mais tarde
    header('HTTP/1.1 422 Unprocessable Entity');

    echo 'ERRO: ' . $e;
}
