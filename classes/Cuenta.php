<?php
    class Account {

        private $_number;
        private $_balance;
        private $_type;
        private $_state;
        private $_userID;
        private $_bancoID;

        function __construct($_number, $_balance, $_type, $_state, $_userID, $_bancoID ) {
            $this->_number = $_number;
            $this->_balance = $_balance;
            $this->_type = $_type;
            $this->_state = $_state;
            $this->_state = $_userID;
            $this->_state = $_bancoID;
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
        public function getUser(){
            return $this->_userID;
        }
        public function getBanco(){
            return $this->_bancoID;
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
        public function setType($userID){
            $this->_type=$userID;
        }
        public function setState($bancoID){
            $this->_state=$bancoID;
        }
        
    }

