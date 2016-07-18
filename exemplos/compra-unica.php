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
    $compra_id = 'pedido_123';

    // 1 único produto
    $produto = array(

        // Identificação do produto. Máx 100 caracteres
        'id' => 123,

        // Preço. Valor inteiro
        'preco' => 19.99,

        // Descrição do produto. Máx 100 caracteres
        'descricao' => 'Livro de matemática',

        // Quantidade
        'qtde' => 10
    );

    $url = $pagseguro->pagar($compra_id, $produto);

    echo $url;
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}


