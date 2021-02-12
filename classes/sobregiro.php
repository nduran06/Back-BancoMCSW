<?php

    class Sobregiro {

        private $_cuentaExistID;
        private $_state;
        private $_percent;
        private $_saldo;
        private $_fecha;

        function __construct($_cuentaID, $_state, $_percent, $saldo, $fecha) {
            $this->_cuentaExistID = $_cuentaID;
            $this->_state = $_state;
            $this->_percent = $_percent;
            $this->_saldo = $saldo;
            $this->_fecha = $fecha;
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

        /**
         * @return mixed
         */
        public function getSaldo()
        {
            return $this->_saldo;
        }

        /**
         * @param mixed $saldo
         */
        public function setSaldo($saldo)
        {
            $this->_saldo = $saldo;
        }

        /**
         * @return mixed
         */
        public function getFecha()
        {
            return $this->_fecha;
        }

        /**
         * @param mixed $fecha
         */
        public function setFecha($fecha)
        {
            $this->_fecha = $fecha;
        }

        public function toArray()
        {
            return  array(
                "cuenta"=>$this->_cuentaExistID,
                "estado"=>$this->_state,
                "saldo"=>$this->_saldo,
                "porcentaje"=>$this->_percent,
                "fecha"=>$this->_fecha
            );
        }
        
    }
