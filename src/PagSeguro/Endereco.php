<?php
namespace PagSeguro;

class Endereco {
    private $logradouro;
    private $numero;
    private $complemento;
    private $bairro;
    private $cep;
    private $cidade;
    private $estado;

    public function __construct() {

    }

    public function setLogradouro($logradouro) {
        $this->logradouro = $logradouro;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setComplemento($complemento) {
        $this->complemento = $complemento;
    }

    public function setBairro($bairro) {
        $this->bairro = $bairro;
    }

    public function setCep($cep) {
        $this->cep = $cep;
    }

    public function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }


    public function toParam() {
        $param = array();

        $param['shippingType'] = '1';
        // $param['shippingCost'] = '0.00';

        if (isset($this->logradouro)) {
            $param['shippingAddressStreet'] = $this->logradouro;
        }

        if (isset($this->numero)) {
            $param['shippingAddressNumber'] = $this->numero;
        }

        if (isset($this->complemento)) {
            $param['shippingAddressComplement'] = $this->complemento;
        }

        if (isset($this->bairro)) {
            $param['shippingAddressDistrict'] = $this->bairro;
        }

        if (isset($this->cep)) {
            $param['shippingAddressPostalCode'] = $this->cep;
        }

        if (isset($this->cidade)) {
            $param['shippingAddressCity'] = $this->cidade;
        }

        if (isset($this->estado)) {
            $param['shippingAddressState'] = $this->estado;
        }

        $param['shippingAddressCountry'] = 'BRA';

        return $param;
    }
}
