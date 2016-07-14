<?php
class PagSeguroTransacao extends PagSeguro {

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


    private function param_produtos($produtos) {
        $param = array();

        // Se for somente 1 produto, coloca dentro de 1 array...
        if (isset($produtos['preco'])) {
            $produtos = array($produtos);
        }

        // Produtos: id, preco, descricao, qtde, peso
        $i = 1;
        foreach ($produtos as $produto) {

            if (isset($produto['id'])) {
                $param['itemId' . $i] = $produto['id'];
            }

            if (isset($produto['preco'])) {
                $param['itemAmount' . $i] = number_format($produto['preco'], 2);
            }

            if (isset($produto['descricao'])) {
                $param['itemDescription' . $i] = $produto['descricao'];
            }

            if (isset($produto['qtde'])) {
                $param['itemQuantity' . $i] = $produto['qtde'];
            }

            if (isset($produto['peso'])) {
                $param['itemWeight' . $i] = $produto['peso'];
            }

            $i++;
        }

        return $param;
    }


    public function pagar($id, $produtos, $pessoa = null, $endereco = null) {

        $param = array(
            'currency' => 'BRL',
            'reference' => $id,
            'redirectURL' => $this->redirect_url,
            'notificationURL' => $this->notificacao_url
        );

        $param = array_merge($param,
            $this->param_produtos($produtos),
            $this->param_pessoa($pessoa),
            $this->param_endereco($endereco)
        );

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
