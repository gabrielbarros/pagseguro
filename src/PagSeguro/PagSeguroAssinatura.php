<?php
namespace PagSeguro;

class PagSeguroAssinatura extends PagSeguro {

    private $id;
    private $comprador;
    private $endereco;
    private $assinatura;

    // Status das notificações de assinatura
    const APROVADA = 'ACTIVE';
    const PENDENTE = 'PENDING';
    const CANCELADA = 'CANCELLED';
    const CANCELADA_PELO_VENDEDOR = 'CANCELLED_BY_RECEIVER';
    const CANCELADA_PELO_USUARIO = 'CANCELLED_BY_SENDER';
    const EXPIRADA = 'EXPIRED';
    const SUSPENSA = 'SUSPENDED'; // ?


    public function setId($id) {
        $this->id = $id;
    }

    public function setComprador($comprador) {
        $this->comprador = $comprador;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function setAssinatura($assinatura) {
        $this->assinatura = $assinatura;
    }


    public function assinar() {

        $param = array(
            'reference' => $this->id,
            'redirectURL' => $this->redirectUrl
        );

        if (isset($this->comprador)) {
            $param = array_merge($param, $this->comprador->toParam());
        }

        if (isset($this->endereco)) {
            $param = array_merge($param, $this->endereco->toParam());
        }

        if (isset($this->assinatura)) {
            $param = array_merge($param, $this->assinatura->toParam());
        }

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
