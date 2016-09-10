# PagSeguro PHP

![PagSeguro](https://raw.githubusercontent.com/gabrielsbarros/pagseguro/master/pagseguro.jpg)

A mini biblioteca PagSeguro PHP (não-oficial) permite, de forma bem simplificada, realizar transações, fazer assinaturas, fazer consultas e sincronizar notificações perdidas.

Exemplos:

## Fazer uma compra

    <?php
    $pagseguro = new PagSeguroTransacao();

    $pagseguro->email = 'meuemail@exemplo.com.br';
    $pagseguro->token = '0123456789ABCDEF0123456789ABCDEF';

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

## Fazer uma assinatura

    <?php
    $pagseguro = new PagSeguroAssinatura();

    $pagseguro->email = 'meuemail@exemplo.com.br';
    $pagseguro->token = '0123456789ABCDEF0123456789ABCDEF';

    $pagseguro->redirect_url = 'https://meusite.com.br/?pagseguro';

    try {
        // Algum identificador único para a assinatura
        // Pode ser um login ou id do usuário
        // Máx 200 caracteres
        $assinatura_id = 'usuario:paulo';

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

        $url = $pagseguro->assinar($assinatura_id, $assinatura);
        echo $url;
    }
    catch (PagSeguroException $e) {
        echo 'ERRO: ' . $e;
    }

## Consultar uma transação no PagSeguro

    <?php
    $pagseguro = new PagSeguroNotificacao();

    $pagseguro->email = 'meuemail@exemplo.com.br';
    $pagseguro->token = '0123456789ABCDEF0123456789ABCDEF';

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

    $pagseguro->email = 'meuemail@exemplo.com.br';
    $pagseguro->token = '0123456789ABCDEF0123456789ABCDEF';

    $pagseguro->callback = function($xml, $notification_type, $notification_code,
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
        $salvou = $pagseguro->notificar($_POST);

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

## CloudFlare

Se estiver usando CloudFlare no site, é recomendável adicionar os seguintes IPs na whitelist do firewall, para que as notificações cheguem sem problemas:

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

