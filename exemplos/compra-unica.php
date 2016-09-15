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
    $compraId = 'pedido_123';

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

    $url = $pagseguro->pagar($compraId, $produto);

    echo $url;
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}


