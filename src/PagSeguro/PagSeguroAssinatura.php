<?php
namespace PagSeguro;

class PagSeguroAssinatura extends PagSeguro {

    // Status das notificações de assinatura
    const APROVADA = 'ACTIVE';
    const PENDENTE = 'PENDING';
    const CANCELADA = 'CANCELLED';
    const CANCELADA_PELO_VENDEDOR = 'CANCELLED_BY_RECEIVER';
    const CANCELADA_PELO_USUARIO = 'CANCELLED_BY_SENDER';
    const EXPIRADA = 'EXPIRED';
    const SUSPENSA = 'SUSPENDED'; // ?


    private function paramAssinatura($assinatura) {
        $param = array();

        // Assinatura: preco, nome, descricao, cobranca, periodo,
        // data_final, valor_maximo
        if (isset($assinatura['preco'])) {
            $param['preApprovalAmountPerPayment'] = number_format(
                $assinatura['preco'], 2);
        }

        if (isset($assinatura['nome'])) {
            $param['preApprovalName'] = $assinatura['nome'];
        }

        if (isset($assinatura['descricao'])) {
            $param['preApprovalDetails'] = $assinatura['descricao'];
        }

        if (isset($assinatura['cobranca'])) {
            $param['preApprovalCharge'] = $assinatura['cobranca'];
        }

        if (isset($assinatura['periodo'])) {
            $param['preApprovalPeriod'] = $assinatura['periodo'];
        }

        if (isset($assinatura['data_final'])) {
            $param['preApprovalFinalDate'] = date('c',
                $assinatura['data_final']);
        }

        if (isset($assinatura['valor_maximo'])) {
            $param['preApprovalMaxTotalAmount'] = number_format(
                $assinatura['valor_maximo'], 2);
        }

        return $param;
    }


    public function assinar($id, $assinatura, $pessoa = null,
                              $endereco = null) {

        $param = array(
            'reference' => $id,
            'redirectURL' => $this->redirectUrl
        );

        $param = array_merge($param,
            $this->paramAssinatura($assinatura),
            $this->paramPessoa($pessoa),
            $this->paramEndereco($endereco)
        );


        $xml = $this->request('POST', '/v2/pre-approvals/request', $param);

        /*
        <preApprovalRequest>
            <code>EA0D70578A8AE68FF4868FB8A500B3D0</code>
            <date>2015-08-14T23:15:27.000-03:00</date>
        </preApprovalRequest>
        */

        if (!isset($xml->code)) {
            $this->erro('Está faltando o campo "code" no XML');
        }

        $url = $this->url . '/v2/pre-approvals/request.html?code=' . $xml->code;

        return $this->go($url);
    }


    public function cancelar($codigoAssinatura) {
        // $codigoAssinatura = preApprovalCode

        $xml = $this->request('GET', '/v2/pre-approvals/cancel/' .
            $codigoAssinatura);

        if (isset($xml->error)) {
            $this->erro('Não foi possível cancelar a assinatura');
        }

        // A assinatura foi cancelada com sucesso
        return isset($xml->status) && strtolower($xml->status) === 'ok';
    }
}
