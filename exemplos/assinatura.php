<?php
require 'autoload.php';

use PagSeguro\PagSeguroAssinatura;
use PagSeguro\PagSeguroException;

$sandbox = true;
$pagseguro = new PagSeguroAssinatura($sandbox);

// autoRedirect=true para redirecionar automaticamente
// caso contrário, o método assinar retorna a URL do PagSeguro
// $pagseguro->autoRedirect = true;

$pagseguro->email = PAGSEGURO_EMAIL;
$pagseguro->token = PAGSEGURO_TOKEN;
$pagseguro->userAgent = 'Meu Site (+https://meusite.com.br)'; // opcional

// notificacaoUrl não funciona com assinaturas! Informe a URL no PagSeguro:
// https://pagseguro.uol.com.br/preferencias/integracoes.jhtml
// $pagseguro->notificacaoUrl = 'https://meusite.com.br/notificar.php';

$pagseguro->redirectUrl = 'https://meusite.com.br/?pagseguro';


try {
    // Algum identificador único para a assinatura
    // Pode ser um login ou id do usuário
    // Máx 200 caracteres
    $assinaturaId = 'usuario:paulo';

    $preco = 9.9; // R$ 9,90
    $periodo = 12; // 12 meses (Obs.: não pode ser maior que 24 meses)

    $assinatura = array(
        // Preço da assinatura. Informar um valor inteiro
        'preco' => $preco,

        // Nome da assinatura. Máx 100 caracteres
        'nome' => 'Revista mensal XPTO',

        // Descrição da assinatura. Máx 255 caracteres (opcional)
        'descricao' => 'Revista XPTO: tudo sobre programação',

        // auto ou manual (auto recomendado)
        'cobranca' => 'auto',

        // Período: weekly, monthly, bimonthly, trimonthly, semiannually, yearly
        'periodo' => 'monthly',

        // Fim da vigência da assinatura (timestamp)
        // -1day para não contar 1 mês a mais
        'data_final' => strtotime('+' . $periodo . ' months -1day'),

        // Valor máximo que pode ser cobrado durante a vigência da assinatura
        'valor_maximo' => $preco * $periodo
    );

    $url = $pagseguro->assinar($assinaturaId, $assinatura);
    echo $url;
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}


