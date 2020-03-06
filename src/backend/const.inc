<?php
  $TABLE_KEYTREE = 'skilleval_keytree';
  $TABLE_DATA = 'skilleval_values';
  
  define('KEY_MESSAGE', 'message');
  define('KEY_ERROR', 'error');
  define('KEY_SUCCESS', 'success');
  define('KEY_VALUE', 'value');
  define('KEY_TEXT', 'text');
  define('KEY_COMMENT', 'comment');
  define('KEY_CHILDREN', 'children');
  define('KEY_CREATE_TS', 'created');
  define('KEY_LASTEDIT_TS', 'edited');
  define('KEY_LASTEDIT_USER', 'user');

  define('FIELD_CREATE_TS', 'create_timestamp');
  define('FIELD_LASTEDIT_TS', 'lastedit_timestamp');
  define('FIELD_LASTEDIT_USER', 'lastedit_user');

  define('VAL_DB_FAIL', 'Database connection failed.');
  define('VAL_DB_ERROR', 'Database error.');
  define('VAL_OPTIONS_OK', 'OK');
  define('VAL_INVALID_ACCESS', 'Invalid access.');
  define('VAL_INVALID_ACTION', 'Invalid action.');
  define('VAL_ID_EMPTY', 'Invalid ID.');
  define('VAL_KEY_EMPTY', 'Data key is empty.');
  define('VAL_KEY_INVALID', 'Data key is invalid.');

  // messages users are expected to see
  define('VAL_NO_READ_PERMISSION', 'You have to be logged in to use this feature.');
  define('VAL_NO_WRITE_PERMISSION', 'You do not have access privileges to perform this action.');
  define('VAL_TEXT_EMPTY', 'Text may not be empty.');
  define('VAL_CHILDREN_EXIST', 'Element has child elements and cannot be deleted.');

  define('VAL_CONFIG_SAVED', 'Configuration saved.');
  define('VAL_DATA_SAVED', 'Data saved.');
  define('VAL_DATA_CHANGED', 'Change saved.');
  define('VAL_DATA_DELETED', 'Entry deleted.');
  define('VAL_COMMENT_SAVED', 'Comment saved.');
?>
