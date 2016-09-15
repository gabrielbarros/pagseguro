<?php
namespace PagSeguro;

class PagSeguroConsulta extends PagSeguro {

    public function consultar($notificationType, $arr) {
        // $arr['notificationCode'] (código de notificação) OU
        // $arr['code'] (código de transação/assinatura)

        // Consultar transação
        if ($notificationType === PagSeguroNotificacao::TRANSACAO) {
            $url = '/v2/transactions/';
        }

        // Consultar assinatura
        elseif ($notificationType === PagSeguroNotificacao::ASSINATURA) {
            $url = '/v2/pre-approvals/';
        }

        else {
            $this->erro('$notificationType inválido');
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
}
