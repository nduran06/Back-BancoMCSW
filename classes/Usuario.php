<?php

    class Users {

        public $_id;
        public $_name;
        public $_user;
        public $_pass;

        function __construct($_id, $_name, $_user, $_pass) {
            $this->_id = $_id;
            $this->_name = $_name;
            $this->_user = $_user;
            $this->_pass = $_pass;
        }

        public function getDoc(){
            return $this->_id;
        }
        public function getName(){
            return $this->_name;
        }
        public function getUser(){
            return $this->_user;
        }
        public function getPass(){
            return $this->_pass;
        }

        public function setDoc($id){
            $this->_id=$id;
        }
        public function setName($name){
            $this->_name=$name;
        }
        public function setUser($user){
            $this->_user=$user;
        }
        public function setPass($pass){
            $this->_pass=$pass;
        }

    }
