<?php 
/*
*@package  Tranzak_Payment_Gateway
*/

namespace Tranzak_PG\Base;

class Deactivation{
  static function deactivate(){
    flush_rewrite_rules();
  }
}