<?php
require '../HttpRequest.class.php';
require 'token.php';
require '../PagSeguro.php';
require '../PagSeguroTransacao.php';

$sandbox = true;
$pagseguro = new PagSeguroTransacao($sandbox);

// auto_redirect=true para redirecionar automaticamente
// caso contrário, o método pagar retorna a URL do PagSeguro
//$pagseguro->auto_redirect = true;

$pagseguro->email = PAGSEGURO_EMAIL;
$pagseguro->token = PAGSEGURO_TOKEN;
$pagseguro->user_agent = 'Meu Site (+https://meusite.com.br)'; // opcional

$pagseguro->notificacao_url = 'https://meusite.com.br/notificar.php';
$pagseguro->redirect_url = 'https://meusite.com.br/?pagseguro';


try {
    // Algo que identifique a compra. Máx 200 caracteres
    // Pode ser o nº do pedido, id do usuário, etc
    $compra_id = 'pedido_8891';

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

    $url = $pagseguro->pagar($compra_id, $produtos);

    echo $url;
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}


