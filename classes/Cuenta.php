<?php
    class Account {

        private $_number;
        private $_balance;
        private $_type;
        private $_state;
        private $_idUsuario;
        private $_idBanco;

        function __construct($_number, $_balance, $_type, $_state, $_idUsuario, $_idBanco) {
            $this->_number = $_number;
            $this->_balance = $_balance;
            $this->_type = $_type;
            $this->_state = $_state;
            $this->_idUsuario = $_idUsuario;
            $this->_idBanco = $_idBanco;

        }

        public function getNumber(){
            return $this->_number;
        }
        public function getBalance(){
            return $this->_balance;
        }
        public function getType(){
            return $this->_type;
        }
        public function getState(){
            return $this->_state;
        }

        public function setNumber($number){
            $this->_number=$number;
        }
        public function setBalance($balance){
            $this->_balance=$balance;
        }
        public function setType($type){
            $this->_type=$type;
        }
        public function setState($state){
            $this->_state=$state;
        }

        /**
         * @return mixed
         */
        public function getIdUsuario()
        {
            return $this->_idUsuario;
        }

        /**
         * @param mixed $idUsuario
         */
        public function setIdUsuario($idUsuario)
        {
            $this->_idUsuario = $idUsuario;
        }

        /**
         * @return mixed
         */
        public function getIdBanco()
        {
            return $this->_idBanco;
        }

        /**
         * @param mixed $idBanco
         */
        public function setIdBanco($idBanco)
        {
            $this->_idBanco = $idBanco;
        }

    }

