<?php
class Sobregiro {

        private $_cuentaExistID;
        private $_state;
        private $_percent;


        function __construct($_cuentaID, $_state, $_percent ) {
            $this->_cuentaExistID = $_cuentaID;
            $this->_state = $_state;
            $this->_percent = $_percent;

        }

        public function getCuentaExistID(){
            return $this->_cuentaExistID;
        }
        public function getState(){
            return $this->_state;
        }
        public function getPercent(){
            return $this->_percent;
        }

        public function setCuentaExistID($cuentaExistID){
            $this->_number=$cuentaExistID;
        }
        public function setState($state){
            $this->_balance=$state;
        }
        public function setPercent($percent){
            $this->_type=$percent;
        }


    }
