<?php

    /**
     *
     * Función para encriptar una contraseña
     * @param $passwd Contraseña que se desea encriptar
     * @return La contraseña
     *
     */
    function criptPass($passwd) {

        $randomData = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)), '+', '.'), 0, 22);
        return crypt($passwd, '$2y$12$'.$randomData);
    }

    /**
     * Función para validar una contraseña
     * @param $passwd Contraseña normal
     * @param $hashPasswd Contraseña encriptada
     * @return bool
     *
     */
    function validPass($passwd, $hashPasswd) {

        return boolval($hashPasswd == crypt($passwd, $hashPasswd));
    }


// Refs: https://gist.github.com/dzuelke/972386