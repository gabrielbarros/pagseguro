<?php
require 'autoload.php';

use PagSeguro\PagSeguroTransacao;
use PagSeguro\PagSeguroException;
use PagSeguro\Produto;
use PagSeguro\Comprador;
use PagSeguro\Endereco;

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
    $pagseguro->setId('pedido_47');

    $produtos = array(

        // new Produto($id, $preco, $descricao, $quantidade),

        new Produto(123, 19.99, 'Livro de matemática', 1),
        new Produto(557, 9.99, 'Livro de português', 2),
        new Produto(9908, 15.99, 'Livro de química', 4)
    );

    $pagseguro->setProdutos($produtos);

    $comprador = new Comprador();
    $comprador->setNome('João da Silva');
    $comprador->setCpf('12345678909');
    $comprador->setDdd('11');
    $comprador->setTelefone('999991111');
    $comprador->setEmail('joaodasilva@sandbox.pagseguro.com.br');
    $pagseguro->setComprador($comprador);

    $endereco = new Endereco();
    $endereco->setLogradouro('Praça do Patriarca');
    $endereco->setNumero('9999');
    $endereco->setComplemento('');
    $endereco->setBairro('Sé');
    $endereco->setCep('01002-010');
    $endereco->setCidade('São Paulo');
    $endereco->setEstado('SP');
    $pagseguro->setEndereco($endereco);

    $url = $pagseguro->pagar();

    echo $url;
}
catch (PagSeguroException $e) {
    echo 'ERRO: ' . $e;
}
