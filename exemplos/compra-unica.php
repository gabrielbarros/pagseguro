<?php
require 'autoload.php';

use PagSeguro\PagSeguroTransacao;
use PagSeguro\PagSeguroException;
use PagSeguro\Produto;

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
    $pagseguro->setId('pedido_45');

    // Não pedir endereço
    $pagseguro->setIgnorarEndereco(true);

    // 1 único produto
    $produto = new Produto();
    $produto->setId(123); // Máx 100 caracteres
    $produto->setPreco(19.99);
    $produto->setDescricao('Livro de matemática'); // Máx 100 caracteres
    $produto->setQuantidade(10);

    $pagseguro->setProduto($produto);

    $url = $pagseguro->pagar();

    echo $url;
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}
