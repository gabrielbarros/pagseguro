<?php
namespace PagSeguro;

class PagSeguroTransacao extends PagSeguro {

    private $id;
    private $comprador;
    private $endereco;
    private $produtos = array();

    // Status das notificações de transações
    const PENDENTE = 1;
    const EM_ANALISE = 2;
    const PAGA = 3;
    const DISPONIVEL = 4;
    const EM_DISPUTA = 5;
    const DEVOLVIDA = 6;
    const CANCELADA = 7;

    // Tipos de transações
    const COMPRA = 1;
    const ASSINATURA = 11;


    public function setId($id) {
        $this->id = $id;
    }

    public function setComprador($comprador) {
        $this->comprador = $comprador;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function setProduto($produto) {
        $this->produtos[0] = $produto;
    }

    public function setProdutos($produtos) {
        for ($i = 0, $len = count($produtos); $i < $len; $i++) {
            $this->produtos[$i] = $produtos[$i];
            $this->produtos[$i]->setNumero($i + 1);
        }
    }

    public function pagar() {

        $param = array(
            'currency' => 'BRL',
            'reference' => $this->id,
            'redirectURL' => $this->redirectUrl,
            'notificationURL' => $this->notificacaoUrl
        );

        if (isset($this->comprador)) {
            $param = array_merge($param, $this->comprador->toParam());
        }

        if (isset($this->endereco)) {
            $param = array_merge($param, $this->endereco->toParam());
        }

        foreach ($this->produtos as $produto) {
            $param = array_merge($param, $produto->toParam());
        }

        $xml = $this->request('POST', '/v2/checkout', $param);

        /*
        <checkout>
            <code>8CF1BE7DCECEF0F005A6DFA0A8243412</code>
            <date>2010-12-02T10:11:28.000-02:00</date>
        </checkout>
        */

        if (!isset($xml->code)) {
            $this->erro('Está faltando o campo "code" no XML');
        }

        $url = $this->url . '/v2/checkout/payment.html?code=' . $xml->code;

        return $this->go($url);
    }
}
