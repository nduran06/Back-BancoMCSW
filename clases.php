<?php

class Users {

  public $_doc;
  public $_name;
  public $_user;
  public $_pass;

  public function setDoc($doc){
    $this->_doc=$doc;
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

  public function getDoc(){
    return $this->_doc;
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
}

class Accounts {

  public $_number;
  public $_balance;
  public $_type;
  public $_state;

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


}


 ?>
