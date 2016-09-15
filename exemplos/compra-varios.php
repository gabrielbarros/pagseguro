<?php
require 'autoload.php';

use PagSeguro\PagSeguroTransacao;
use PagSeguro\PagSeguroException;

$sandbox = true;
$pagseguro = new PagSeguroTransacao($sandbox);

// autoRedirect=true para redirecionar automaticamente
// caso contrário, o método pagar retorna a URL do PagSeguro
// $pagseguro->autoRedirect = true;

$pagseguro->email = PAGSEGURO_EMAIL;
$pagseguro->token = PAGSEGURO_TOKEN;
$pagseguro->userAgent = 'Meu Site (+https://meusite.com.br)'; // opcional

$pagseguro->notificacaoUrl = 'https://meusite.com.br/notificar.php';
$pagseguro->redirectUrl = 'https://meusite.com.br/?pagseguro';


try {
    // Algo que identifique a compra. Máx 200 caracteres
    // Pode ser o nº do pedido, id do usuário, etc
    $compraId = 'pedido_8891';

    // Vários produtos
    $produtos = array(

        array(
            'id' => 123,
            'preco' => 19.99,
            'descricao' => 'Livro de matemática',
            'qtde' => 1
        ),

        array(
            'id' => 557,
            'preco' => 9.99,
            'descricao' => 'Livro de português',
            'qtde' => 2
        ),

        array(
            'id' => 9908,
            'preco' => 15.99,
            'descricao' => 'Livro de química',
            'qtde' => 4
        )
    );

    $url = $pagseguro->pagar($compraId, $produtos);

    echo $url;
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}


