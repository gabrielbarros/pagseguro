<?php
class PagSeguroNotificacao extends PagSeguro {

    // Notificação de assinatura
    const ASSINATURA = 'preApproval';

    // Notificação de transação
    const TRANSACAO = 'transaction';

    // Função para salvar o resultado da notificacao no banco de dados
    // function($xml, $notification_type, $notification_code, $manual) {}
    public $callback;


    // Método chamado pelo PagSeguro
    public function notificar($post) {
        // $post: notificationCode, notificationType=preApproval|transaction

        // Permitir que a sandbox envie dados via ajax para o localhost
        if ($this->sandbox) {
            header('Access-Control-Allow-Origin: ' . $this->url);
        }

        // Checar campos
        if (!isset($post['notificationCode'], $post['notificationType'])) {
            $msg = 'Faltando algum dos campos: ';
            $msg .= 'notificationCode, notificationType';
            $this->erro($msg);
        }

        $notification_code = $post['notificationCode'];
        $notification_type = $post['notificationType'];

        // Notificação de assinatura ou transação?
        if (!in_array($notification_type,
            array(self::ASSINATURA, self::TRANSACAO))) {

            $this->erro('Campo "notificationType" inválido');
        }


        // Consultar no PagSeguro informações sobre a notificação recebida
        $xml = $this->consultar($notification_type, array(
            'notificationCode' => $notification_code
        ));

        $salvou = $this->salvar($xml, $notification_type, $notification_code);
        return $salvou;
    }


    public function salvar($xml, $notification_type, $notification_code,
        $manual = false) {

        // Salvar notificação em um banco de dados, p. ex.
        if (is_callable($this->callback)) {
            $salvou = ($this->callback)(
                $xml,
                $notification_type, $notification_code, $manual
            );

            return $salvou;
        }

        $msg = 'Informe um callback válido para determinar o que fazer ';
        $msg .= 'com a notificação recebida';
        $this->erro($msg);
    }


    public function consultar($notification_type, $arr) {
        // $arr['notificationCode'] (código de notificação) OU
        // $arr['code'] (código de transação/assinatura)

        // Consultar transação
        if ($notification_type === self::TRANSACAO) {
            $url = '/v2/transactions/';
        }

        // Consultar assinatura
        elseif ($notification_type === self::ASSINATURA) {
            $url = '/v2/pre-approvals/';
        }

        else {
            $this->erro('$notification_type inválido');
        }


        // Consultando usando o código de notificação
        // ou o código de transação/assinatura?
        if (isset($arr['notificationCode'])) {
            $url .= 'notifications/' . $arr['notificationCode'];
        }
        elseif (isset($arr['code'])) {
            $url .= $arr['code'];
        }
        else {
            $msg = 'É necessário informar o campo "notificationCode" ';
            $msg .= 'ou o campo "code"';
            $this->erro($msg);
        }

        return $this->request('GET', $url);

        /*
        <!-- Assinatura exemplo -->
        <preApproval>
            <name>Algum produto</name>
            <code>1904EC0DC4D4A4E6642D3FB5A7D96105</code>
            <date>2015-08-20T18:25:16.000-03:00</date>
            <tracker>1BCD5B</tracker>
            <status>ACTIVE</status>
            <reference>uid:12</reference>
            <lastEventDate>2015-08-20T18:26:56.000-03:00</lastEventDate>
            <charge>AUTO</charge>
            <sender>
                <name>Comprador de Testes</name>
                <email>c12345@sandbox.pagseguro.com.br</email>
                <phone>
                    <areaCode>21</areaCode>
                    <number>99999999</number>
                </phone>
                <address>
                    <street>RUA TAL</street>
                    <number>123</number>
                    <complement/>
                    <district>Bairro X</district>
                    <city>Cidade Y</city>
                    <state>RJ</state>
                    <country>BRA</country>
                    <postalCode>20000000</postalCode>
                </address>
            </sender>
        </preApproval>


        <!-- Transação exemplo -->
        <transaction>
            <date>2015-08-25T02:58:53.000-03:00</date>
            <code>E35D4859-6011-42DA-9CA6-56137A1E3318</code>
            <reference>uid:12</reference>
            <type>11</type>
            <status>3</status>
            <lastEventDate>2015-08-25T03:03:33.000-03:00</lastEventDate>
            <paymentMethod>
                <type>1</type>
                <code>101</code>
            </paymentMethod>
            <grossAmount>10.00</grossAmount>
            <discountAmount>0.00</discountAmount>
            <feeAmount>0.80</feeAmount>
            <netAmount>9.20</netAmount>
            <escrowEndDate>2015-09-24T03:03:33.000-03:00</escrowEndDate>
            <installmentCount>1</installmentCount>
            <itemCount>1</itemCount>
            <items>
                <item>
                <id>001</id>
                <description>Algum produto</description>
                <quantity>1</quantity>
                <amount>10.00</amount>
                </item>
            </items>
            <sender>
                <name>Comprador de Testes</name>
                <email>c12345@sandbox.pagseguro.com.br</email>
                <phone>
                <areaCode>21</areaCode>
                <number>99999999</number>
                </phone>
            </sender>
            <shipping>
                <address>
                    <street>RUA TAL</street>
                    <number>123</number>
                    <complement/>
                    <district>Bairro X</district>
                    <city>Cidade Y</city>
                    <state>RJ</state>
                    <country>BRA</country>
                    <postalCode>20000000</postalCode>
                </address>
                <type>3</type>
                <cost>0.00</cost>
            </shipping>
        </transaction>
        */
    }


    public function sincronizar($notification_type, $tempo = 24, $pag = 1) {
        $qtde_sincronizada = 0;

        $initial_date = date('Y-m-d\TH:i', strtotime("-{$tempo} hours"));
        // $final_date = date('Y-m-d\TH:i', strtotime('now'));
        $final_date = '';

        $max_results = 30;

        // Consultar transação
        if ($notification_type === self::TRANSACAO) {
            $url = '/v2/transactions';
        }

        // Consultar assinatura
        elseif ($notification_type === self::ASSINATURA) {
            $url = '/v2/pre-approvals';
        }

        else {
            $this->erro('$notification_type inválido');
        }

        $xml_node_filho = $notification_type;
        $xml_node_pai = $notification_type . 's';

        $xml = $this->request('GET', $url, array(
            'initialDate' => $initial_date,
            'finalDate' => $final_date,
            'page' => $pag,
            'maxPageResults' => $max_results
        ));

        // Verificar se o XML possui os campos necessários
        if (!isset($xml->totalPages, $xml->resultsInThisPage)) {
            $this->erro('Faltam alguns campos no XML #1');
        }

        $total_pages = (int) $xml->totalPages;
        $results = (int) $xml->resultsInThisPage;

        if (!$results) {
            // Nenhum resultado
            return 0;
        }

        // Mais uma validação no XML
        if (!isset($xml->{$xml_node_pai},
            $xml->{$xml_node_pai}->{$xml_node_filho})) {

            $this->erro('Faltam alguns campos no XML #2');
        }

        // Status que interessam para salvar no banco de dados
        $status_arr = array(
            (string) PagSeguroTransacao::PAGA,
            PagSeguroAssinatura::APROVADA,
            PagSeguroAssinatura::CANCELADA,
            PagSeguroAssinatura::CANCELADA_PELO_VENDEDOR,
            PagSeguroAssinatura::CANCELADA_PELO_USUARIO,
            PagSeguroAssinatura::EXPIRADA
        );

        foreach ($xml->{$xml_node_pai}->{$xml_node_filho} as $value) {
            // $value->code = código da transação/assinatura
            // $value->status = status da transação/assinatura

            if (in_array((string) $value->status, $status_arr, true)) {

                $xml_consulta = $this->consultar($notification_type, array(
                    'code' => $value->code
                ));

                // Notificações manuais serão inseridas no banco com o
                // código "manual_xxxxxx". Isso acontece quando o PagSeguro não
                // conseguir se comunicar com o site, então é necessário fazer
                // uma solicitação manual para sincronizar possíveis
                // notificações perdidas o mais rápido possível

                $notification_code = 'manual_' . $this->random_string(32);

                $salvou = $this->salvar($xml_consulta, $notification_type,
                    $notification_code, true);

                if ($salvou) {
                    $qtde_sincronizada++;
                }
            }
        }

        // Se houver outra página de resultados, continuar analisando
        // em busca de notificações perdidas...
        if ($pag < $total_pages) {
            $pag++;
            $qtde_sincronizada += $this->sincronizar(
                                      $notification_type, $tempo, $pag
                                  );
        }

        return $qtde_sincronizada;
    }
}
