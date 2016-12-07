# PagSeguro PHP

![PagSeguro](pagseguro.jpg)

A mini biblioteca PagSeguro PHP (não-oficial) permite, de forma bem simplificada, realizar transações, fazer assinaturas, fazer consultas e sincronizar notificações perdidas.

Exemplos:

## Fazer uma compra

    <?php
    $pagseguro = new PagSeguroTransacao();

    $pagseguro->email = PAGSEGURO_EMAIL;
    $pagseguro->token = PAGSEGURO_TOKEN;

    $pagseguro->notificacaoUrl = 'https://meusite.com.br/notificar.php';
    $pagseguro->redirectUrl = 'https://meusite.com.br/?pagseguro';


    try {
        // Algo que identifique a compra. Máx 200 caracteres
        // Pode ser o nº do pedido, id do usuário, etc
        $pagseguro->setId('pedido_45');

        // 1 único produto
        $produto = new Produto();
        $produto->setId(123);
        $produto->setPreco(19.99);
        $produto->setDescricao('Livro de matemática');
        $produto->setQuantidade(10);

        $pagseguro->setProduto($produto);

        $url = $pagseguro->pagar();

        echo $url;
    }
    catch (PagSeguroException $e) {
        echo 'ERRO: ' . $e;
    }


## Fazer uma assinatura

    <?php
    $pagseguro = new PagSeguroAssinatura();

    $pagseguro->email = PAGSEGURO_EMAIL;
    $pagseguro->token = PAGSEGURO_TOKEN;

    $pagseguro->redirectUrl = 'https://meusite.com.br/?pagseguro';


    try {
        // Algum identificador único para a assinatura
        // Pode ser um login ou id do usuário
        // Máx 200 caracteres
        $pagseguro->setId('usuario:paulo');


        $preco = 9.9; // R$ 9,90
        $periodo = 12; // 12 meses (Obs.: não pode ser maior que 24 meses)

        $assinatura = new Assinatura();

        // Preço da assinatura. Informar um valor inteiro
        $assinatura->setPreco($preco);

        // Nome da assinatura. Máx 100 caracteres
        $assinatura->setNome('Revista mensal XPTO');

        // Descrição da assinatura. Máx 255 caracteres (opcional)
        $assinatura->setDescricao('Revista XPTO: tudo sobre programação');

        // auto ou manual (auto recomendado)
        $assinatura->setCobranca('auto');

        // Período: weekly, monthly, bimonthly, trimonthly, semiannually, yearly
        $assinatura->setPeriodo('monthly');

        // Fim da vigência da assinatura (timestamp)
        // -1day para não contar 1 mês a mais
        $assinatura->setDataFinal(strtotime('+' . $periodo . ' months -1day'));

        // Valor máximo que pode ser cobrado durante a vigência da assinatura
        $assinatura->setValorMaximo($preco * $periodo);

        $pagseguro->setAssinatura($assinatura);

        $url = $pagseguro->assinar();
        echo $url;
    }
    catch (PagSeguroException $e) {
        echo 'ERRO: ' . $e;
    }


## Consultar uma transação no PagSeguro

    <?php
    $pagseguro = new PagSeguroConsulta();

    $pagseguro->email = PAGSEGURO_EMAIL;
    $pagseguro->token = PAGSEGURO_TOKEN;

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


## Receber notificação do PagSeguro via POST
    <?php
    $pagseguro = new PagSeguroNotificacao();

    $pagseguro->email = PAGSEGURO_EMAIL;
    $pagseguro->token = PAGSEGURO_TOKEN;

    $pagseguro->callback = function($xml, $notificationType, $notificationCode,
                                     $manual) {

        // Salvar no banco de dados:
        // $xml->code
        // $xml->type
        // $xml->status
        // $xml->sender->email
        // (...)

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



----------------

# FAQ PagSeguro

## Qual é o login e senha para a sandbox?
Use o mesmo e-mail e senha da conta no PagSeguro

## Onde consigo o token da sandbox?
Veja em https://sandbox.pagseguro.uol.com.br/vendedor/configuracoes.html

## Onde consigo o e-mail e senha do comprador de testes?
Veja em https://sandbox.pagseguro.uol.com.br/comprador-de-testes.html

## Qual o CPF e telefone do comprador de testes?
Em CPF, informe 12345678909. Em celular, pode ser 11 91111111.

## Como faço para testar as notificações do PagSeguro em sandbox?
Primeiro faça uma compra usando a biblioteca do PagSeguro, em localhost. Depois entre em https://sandbox.pagseguro.uol.com.br/transacoes.html, clique em uma transação e depois em "Reenviar notificação local". É importante usar HTTPS em localhost e preencher a URL de notificação (`$pagseguro->notificacaoUrl`)

## Por que não recebo as notificações do PagSeguro em produção, usando Cloudflare?
Infelizmente o PagSeguro ainda não é compatível com o certificado de segurança usado por sites que usam Cloudflare. Simplesmente não use HTTPS na URL de notificação. Nesse caso não há riscos de segurança, porque apenas o código de notificação é enviado.

## O Cloudflare está bloqueando as requisições do PagSeguro. O que fazer?
Se estiver usando Cloudflare no site, é recomendável adicionar os seguintes IPs na whitelist do firewall, para que as notificações cheguem sem problemas:

- 186.234.16.8
- 186.234.16.9
- 186.234.48.8
- 186.234.48.9
- 186.234.144.17
- 186.234.144.18
- 200.221.19.4
- 200.221.19.20
- 200.147.112.136
- 200.147.112.137

