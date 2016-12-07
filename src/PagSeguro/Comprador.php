<?php
namespace PagSeguro;

class Comprador {
    private $nome;
    private $cpf;
    private $ddd;
    private $telefone;
    private $email;

    public function __construct() {

    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setCpf($cpf) {
        $this->cpf = $cpf;
    }

    public function setDdd($ddd) {
        $this->ddd = $ddd;
    }

    public function setTelefone($telefone) {
        $this->telefone = $telefone;
    }

    public function setEmail($email) {
        $this->email = $email;
    }


    public function toParam() {
        $param = array();

        if (isset($this->nome)) {
            $param['senderName'] = $this->nome;
        }

        if (isset($this->cpf)) {
            $param['senderCPF'] = $this->cpf;
        }

        if (isset($this->ddd)) {
            $param['senderAreaCode'] = $this->ddd;
        }

        if (isset($this->telefone)) {
            $param['senderPhone'] = $this->telefone;
        }

        if (isset($this->email)) {
            $param['senderEmail'] = $this->email;
        }

        return $param;
    }
}
