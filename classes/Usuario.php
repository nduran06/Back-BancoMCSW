<?php

    class Usuario {

        public $_id;
        public $_doc;
        public $_name;
        public $_user;
        public $_pass;
        public $_type;


        function __construct($_doc, $_name, $_user, $_pass, $_type) {
            $this->_doc = $_doc;
            $this->_name = $_name;
            $this->_user = $_user;
            $this->_pass = $_pass;
            $this->_type = $_type;
        }

        function __construct0($_id, $_doc, $_name, $_user, $_pass, $_type) {
            $this->_id = $_id;
            $this->_doc = $_doc;
            $this->_name = $_name;
            $this->_user = $_user;
            $this->_pass = $_pass;
            $this->_type = $_type;
        }

        /**
         * @return mixed
         */
        public function getId()
        {
            return $this->_id;
        }

        /**
         * @param mixed $id
         */
        public function setId($id)
        {
            $this->_id = $id;
        }

        /**
         * @return mixed
         */
        public function getDoc()
        {
            return $this->_doc;
        }

        /**
         * @param mixed $doc
         */
        public function setDoc($_doc)
        {
            $this->_doc = $_doc;
        }

        public function getUser(){
            return $this->_user;
        }

        public function setUser($user){
            $this->_user=$user;
        }

        public function getPass(){
            return $this->_pass;
        }

        public function setPass($pass){
            $this->_pass=$pass;
        }

        /**
         * @return mixed
         */
        public function getName()
        {
            return $this->_name;
        }

        public function setName($name){
            $this->_name=$name;
        }

        /**
         * @return mixed
         */
        public function getType()
        {
            return $this->_type;
        }

        /**
         * @param mixed $type
         */
        public function setType($type)
        {
            $this->_type = $type;
        }

    }
