<?php
namespace PagSeguro;

class Produto {
    private $numero = 1;
    private $id;
    private $preco;
    private $descricao;
    private $quantidade;
    private $peso;

    public function __construct($id = null, $preco = null,
        $descricao = null, $quantidade = null, $peso = null) {

        $this->id = $id;
        $this->preco = $preco;
        $this->descricao = $descricao;
        $this->quantidade = $quantidade;
        $this->peso = $peso;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setQuantidade($quantidade) {
        $this->quantidade = $quantidade;
    }

    public function setPeso($peso) {
        $this->peso = $peso;
    }


    public function toParam() {
        $param = array();

        if (isset($this->id)) {
            $param['itemId' . $this->numero] = $this->id;
        }

        if (isset($this->preco)) {
            $param['itemAmount' . $this->numero] = $this->preco;
        }

        if (isset($this->descricao)) {
            $param['itemDescription' . $this->numero] = $this->descricao;
        }

        if (isset($this->quantidade)) {
            $param['itemQuantity' . $this->numero] = $this->quantidade;
        }

        if (isset($this->peso)) {
            $param['itemWeight' . $this->numero] = $this->peso;
        }

        return $param;
    }
}
