<?php
namespace PagSeguro;

class Assinatura {
    private $preco;
    private $nome;
    private $descricao;
    private $cobranca;
    private $periodo;
    private $dataFinal;
    private $valorMaximo;

    public function __construct() {

    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setCobranca($cobranca) {
        $this->cobranca = $cobranca;
    }

    public function setPeriodo($periodo) {
        $this->periodo = $periodo;
    }

    public function setDataFinal($dataFinal) {
        $this->dataFinal = $dataFinal;
    }

    public function setValorMaximo($valorMaximo) {
        $this->valorMaximo = $valorMaximo;
    }


    public function toParam() {
        $param = array();

        if (isset($this->preco)) {
            $param['preApprovalAmountPerPayment'] = number_format(
                $this->preco, 2);
        }

        if (isset($this->nome)) {
            $param['preApprovalName'] = $this->nome;
        }

        if (isset($this->descricao)) {
            $param['preApprovalDetails'] = $this->descricao;
        }

        if (isset($this->cobranca)) {
            $param['preApprovalCharge'] = $this->cobranca;
        }

        if (isset($this->periodo)) {
            $param['preApprovalPeriod'] = $this->periodo;
        }

        if (isset($this->dataFinal)) {
            $param['preApprovalFinalDate'] = date('c', $this->dataFinal);
        }

        if (isset($this->valorMaximo)) {
            $param['preApprovalMaxTotalAmount'] = number_format(
                $this->valorMaximo, 2);
        }

        return $param;
    }
}
