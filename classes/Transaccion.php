<?php

    class Transaccion {

        private $id;
        private $origen;
        private $destino;
        private $saldo;
        private $estado;
        private $fecha;

        function __construct($id, $origen, $destino, $saldo, $estado, $fecha) {
            $this->id = $id;
            $this->origen = $origen;
            $this->destino = $destino;
            $this->saldo = $saldo;
            $this->estado = $estado;
            $this->fecha = $fecha;
        }

        /**
         * @return mixed
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @param mixed $id
         */
        public function setId($id)
        {
            $this->id = $id;
        }

        /**
         * @return mixed
         */
        public function getOrigen()
        {
            return $this->origen;
        }

        /**
         * @param mixed $origen
         */
        public function setOrigen($origen)
        {
            $this->origen = $origen;
        }

        /**
         * @return mixed
         */
        public function getDestino()
        {
            return $this->destino;
        }

        /**
         * @param mixed $destino
         */
        public function setDestino($destino)
        {
            $this->destino = $destino;
        }

        /**
         * @return mixed
         */
        public function getSaldo()
        {
            return $this->saldo;
        }

        /**
         * @param mixed $saldo
         */
        public function setSaldo($saldo)
        {
            $this->saldo = $saldo;
        }

        /**
         * @return mixed
         */
        public function getEstado()
        {
            return $this->estado;
        }

        /**
         * @param mixed $estado
         */
        public function setEstado($estado)
        {
            $this->estado = $estado;
        }

        /**
         * @return mixed
         */
        public function getFecha()
        {
            return $this->fecha;
        }

        /**
         * @param mixed $fecha
         */
        public function setFecha($fecha)
        {
            $this->fecha = $fecha;
        }


    }