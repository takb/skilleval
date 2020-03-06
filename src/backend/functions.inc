<?php
  function db_connect() {
    require 'conf.inc';
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die(VAL_DB_FAIL);
    $db->query('SET CHARACTER SET utf8');
    $db->query('SET NAMES utf8');
    return $db;
  }

  function getPermissions(){
    $obj = new stdClass;
    $obj->read = true;
    $obj->write = true;
    return $obj;
  }

  function invalidAction(&$data){
    $data[KEY_ERROR] = true;
    $data[KEY_MESSAGE] = VAL_INVALID_ACTION;
  }

  function isValidAccess(&$data){
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      $data[KEY_MESSAGE] = VAL_OPTIONS_OK;
      return false;
    }
    $valid = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    if (!$valid) {
      $data[KEY_ERROR] = true;
      $data[KEY_MESSAGE] = VAL_INVALID_ACCESS;
    }
    return $valid;
  }

  function isValidID($id, &$data){
    if (!$id) {
      $data[KEY_ERROR] = true;
      $data[KEY_MESSAGE] = VAL_ID_EMPTY;
    }
    return $id;
  }

  function isValidText($text, &$data){
    if (!$text) {
      $data[KEY_ERROR] = true;
      $data[KEY_MESSAGE] = VAL_TEXT_EMPTY;
    }
    return $text;
  }

  function isValidKey($key, &$data){
    if (!$key) {
      $data[KEY_ERROR] = true;
      $data[KEY_MESSAGE] = VAL_KEY_EMPTY;
      return false;
    }
    if (!preg_match('/\d+\.\d+\.\d+\.\d+/', $key)) {
      $data[KEY_ERROR] = true;
      $data[KEY_MESSAGE] = VAL_KEY_INVALID;
      return false;
    }
    return true;
  }

  function hasReadPermission($permission, &$data){
    if (!$permission->read) {
      $data[KEY_ERROR] = true;
      $data[KEY_MESSAGE] = VAL_NO_READ_PERMISSION;
    }
    return $permission->read;
  }

  function hasWritePermission($permission, &$data){
    if (!$permission->write) {
      $data[KEY_ERROR] = true;
      $data[KEY_MESSAGE] = VAL_NO_WRITE_PERMISSION;
    }
    return $permission->write;
  }

  function errorMessage(&$data, $message){
    $data[KEY_ERROR] = true;
    $data[KEY_MESSAGE] = $message;
  }

  function success(&$data, $message){
    $data[KEY_SUCCESS] = true;
    $data[KEY_MESSAGE] = $message;
  }
?>
