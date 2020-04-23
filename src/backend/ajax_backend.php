<?php
require 'const.inc';
require 'functions.inc';

$user_id = intval(1);
$permission = getPermissions();
$ret = array();
if (isValidAccess($ret) && hasReadPermission($permission, $ret)) {
  $ret = array('wp' => $permission->write, 'rp' => $permission->read);
  $db = db_connect();
  $input = json_decode(file_get_contents('php://input'), true);
  $action = strval($input['action']) ?: '';
  $parent = intval($input['parent']) ?: 0;
  $id = intval($input['id']) ?: 0;
  $text = strval($input[KEY_TEXT]) ?: '';
  $children = is_array($input[KEY_CHILDREN]) ? array_filter($input[KEY_CHILDREN], "intval") : [0];
  $key = strval($input['key']) ?: '';
  $value = strval($input['value']) ?: 0;
  $ids = strval($input['ids']) ?: '0';
  $idarray = $input['idarray'] ?: [];
  switch ($action) {

    case "status":
      break;

    case "load":
      $result = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `parent` = '$parent' ORDER BY `id`");
      $ret['items'] = array();
      if ($result->num_rows) {
        while ($row = $result->fetch_array()) {
          $children = array();
          $result2 = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `parent` = '{$row['id']}' ORDER BY `id`");
          if ($result2->num_rows) {
            while ($childrow = $result2->fetch_array()) {
              array_push($children, array(
                KEY_VALUE => intval($childrow['id']), KEY_TEXT => $childrow[KEY_TEXT],
                KEY_CREATE_TS => $childrow[FIELD_CREATE_TS], KEY_LASTEDIT_TS => $childrow[FIELD_LASTEDIT_TS], KEY_LASTEDIT_USER => intval($childrow[FIELD_LASTEDIT_USER]), KEY_CHILDREN => []
              ));
            }
          }
          array_push($ret['items'], array(
            KEY_VALUE => intval($row['id']), KEY_TEXT => $row[KEY_TEXT],
            KEY_CREATE_TS => $row[FIELD_CREATE_TS], KEY_LASTEDIT_TS => $row[FIELD_LASTEDIT_TS], KEY_LASTEDIT_USER => intval($row[FIELD_LASTEDIT_USER]), KEY_CHILDREN => $children
          ));
        }
      }
      break;

    case "add":
      if (!hasWritePermission($permission, $ret)) {break;}
      if (!isValidText($text, $ret)) {break;}
      $result = $db->query("INSERT INTO `$TABLE_KEYTREE` (`parent`, `text`, `lastedit_user`) VALUES ($parent, '{$db->real_escape_string($text)}', $user_id)");
      if (!$db->insert_id) {
        errorMessage($ret, VAL_DB_ERROR);
        break;
      }
      $result = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `id` = '$db->insert_id'");
      if ($result->num_rows && $row = $result->fetch_array()) {
        $ret['item'] = array(
          KEY_VALUE => intval($row['id']), KEY_TEXT => $row[KEY_TEXT],
          KEY_CREATE_TS => $row[FIELD_CREATE_TS], KEY_LASTEDIT_TS => null, KEY_LASTEDIT_USER => intval($row[FIELD_LASTEDIT_USER]), KEY_CHILDREN => array()
        );
      }
      break;

    case "del":
      if (!hasWritePermission($permission, $ret)) {break;}
      if (!isValidID($id, $ret)) {break;}
      $result = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `parent` = $id");
      if ($result->num_rows) {
        errorMessage($ret, VAL_CHILDREN_EXIST);
        break;
      }
      $result = $db->query("DELETE FROM `$TABLE_KEYTREE` WHERE `id` = $id");
      if ($result) {
        success($ret, VAL_DATA_DELETED);
      } else {
        errorMessage($ret, VAL_DB_ERROR);
      }
      break;

    case "ren":
      if (!hasWritePermission($permission, $ret)) {break;}
      if (!isValidID($id, $ret)) {break;}
      if (!isValidText($text, $ret)) {break;}
      $result = $db->query("UPDATE `$TABLE_KEYTREE` SET `text` = '{$db->real_escape_string($text)}', `lastedit_timestamp` = NOW() WHERE `id` = $id");
      if ($result) {
        success($ret, VAL_DATA_CHANGED);
      } else {
        errorMessage($ret, VAL_DB_ERROR);
      }
      break;

    case "loadval":
      $ret['values'] = array();
      $result = $db->query("SELECT * FROM `$TABLE_DATA` WHERE `id` = $id");
      if ($result) {
        while ($row = $result->fetch_array()) {
          $ret['values'][$row['key']] = $row['value'] == "0" ? false : $row['value'];
        }
      }
      break;

    case "saveval":
      if (!hasWritePermission($permission, $ret)) {break;}
      $result = $db->query("SELECT * FROM `$TABLE_DATA` WHERE `id` = $id AND `key` = '$key'");
      if ($result->num_rows) {
        $result = $db->query("UPDATE `$TABLE_DATA` SET `value` = '{$db->real_escape_string($value)}', `lastedit_timestamp` = NOW(), `lastedit_user` = $user_id WHERE `id` = $id AND `key` = '$key'");
        if (!$result) {
          errorMessage($ret, VAL_DB_ERROR);
          break;
        }
      } else {
        $result = $db->query("INSERT INTO `$TABLE_DATA` (`id`, `key`, `value`, `lastedit_user`) VALUES ($id, '$key', '{$db->real_escape_string($value)}', $user_id)");
        if (!$result) {
          errorMessage($ret, VAL_DB_ERROR);
          break;
        }
      }
      success($ret, VAL_CONFIG_SAVED);
      break;

      case "prefetch":
        $ret['items'] = array();
        $ret['target'] = array();
        $rowkeys = array();
        $colkeys = array();
        $result = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `parent` = '1' ORDER BY `id`");
        if ($result->num_rows) {
          while ($row = $result->fetch_array()) {
            $children = array();
            $result2 = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `parent` = '{$row['id']}' ORDER BY `id`");
            if ($result2->num_rows) {
              while ($childrow = $result2->fetch_array()) {
                array_push($rowkeys, $row['id'].'.'.$childrow['id']);
              }
            } else {
              array_push($rowkeys, $row['id'].'.0');
            }
          }
        }
        $result = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `parent` = '2' ORDER BY `id`");
        if ($result->num_rows) {
          while ($row = $result->fetch_array()) {
            $children = array();
            $result2 = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `parent` = '{$row['id']}' ORDER BY `id`");
            if ($result2->num_rows) {
              while ($childrow = $result2->fetch_array()) {
                array_push($colkeys, $row['id'].'.'.$childrow['id']);
              }
            } else {
              array_push($colkeys, $row['id'].'.0');
            }
          }
        }
        foreach ($rowkeys as $rkey) {
          foreach ($colkeys as $ckey) {
            $blockkey = $rkey.'.'.$ckey;
            $result = $db->query("SELECT `value` FROM `$TABLE_DATA` WHERE `key` = '$blockkey' AND `id` = 0");
            if ($row = $result->fetch_assoc()) {
              $ret['target'][$blockkey] = $row['value'];
            } else {
              continue;
            }
            $ret['items'][$blockkey] = array();
            // SELECT `d`.`id`, `c`.`text`, `v`.`value` FROM `skilleval_values` AS `d`
            //   LEFT JOIN `skilleval_keytree` AS `c` ON (`d`.`id` = `c`.`id`)
            //   LEFT JOIN `skilleval_values` AS `v` ON (`d`.`id` = `v`.`id` AND `v`.`key` = 'points')
            //   WHERE `d`.`key` = '4.17.20.24' AND `d`.`value` > 0 AND `d`.`id` > 0 AND `v`.`value` > 0
            $result = $db->query("SELECT `d`.`id`, `c`.`text`, `v`.`value` FROM `$TABLE_DATA` AS `d`
                                    LEFT JOIN `$TABLE_KEYTREE` AS `c` ON (`d`.`id` = `c`.`id`)
                                    LEFT JOIN `$TABLE_DATA` AS `v` ON (`d`.`id` = `v`.`id` AND `v`.`key` = 'points')
                                    WHERE `d`.`key` = '$blockkey' AND `d`.`value` > 0 AND `d`.`id` > 0 AND `v`.`value` > 0");
            while ($row = $result->fetch_assoc()) {
              array_push($ret['items'][$blockkey], $row);
            }
          }
        }
        break;

    // case "com":
    //   if (!hasWritePermission($permission, $ret)) {break;}
    //   if (!isValidID($id, $ret)) {break;}
    //   $result = $db->query("UPDATE `$TABLE_KEYTREE` SET `comment` = '{$db->real_escape_string($text)}', `lastedit_timestamp` = NOW() WHERE `id` = $id");
    //   if ($result) {
    //     success($ret, VAL_COMMENT_SAVED);
    //   } else {
    //     errorMessage($ret, VAL_DB_ERROR);
    //   }
    //   break;
    //
    // case "chload":
    //   if (!isValidID($id, $ret)) {break;}
    //   $ret['mappings'] = array();
    //   $result = $db->query("SELECT `m`.*, `s`.`parent` AS `stype`, `t`.`parent` AS `ttype`, `t`.`id` AS `tid`
    //   	FROM `$TABLE_MAP` AS `m`
    //   	LEFT JOIN `$TABLE_KEYTREE` AS `s` ON (`m`.`source` = `s`.`id`)
    //     LEFT JOIN `$TABLE_KEYTREE` AS `t` ON (`m`.`target` = `t`.`id`)
    //     WHERE `m`.`source` = $id");
    //   if ($result) {
    //     while ($row = $result->fetch_array()) {
    //       array_push($ret['mappings'], array(
    //         'stype' => intval($row['stype']), 'ttype' => intval($row['ttype']), 'tid' => $row['tid'],
    //         KEY_CREATE_TS => $row[FIELD_CREATE_TS], KEY_LASTEDIT_TS => $row[FIELD_LASTEDIT_TS], KEY_LASTEDIT_USER => intval($row[FIELD_LASTEDIT_USER])
    //       ));
    //     }
    //   } else {
    //     errorMessage($ret, VAL_DB_ERROR);
    //   }
    //   break;
    //
    // case "cload":
    //   $ret['options'] = array();
    //   $result = $db->query("SELECT `t`.*, `t`.`parent` AS `ttype`, `t`.`id` AS `tid`
    //   	FROM `$TABLE_MAP` AS `m`
    //     LEFT JOIN `$TABLE_KEYTREE` AS `t` ON (`m`.`target` = `t`.`id`)
    //     WHERE `m`.`source` IN ($ids)");
    //   if ($result) {
    //     while ($row = $result->fetch_array()) {
    //       if (!is_array($ret['options'][$row['ttype']])) {
    //         $ret['options'][$row['ttype']] = array();
    //       }
    //       foreach ($ret['options'][$row['ttype']] as $option) {
    //           if ($option['value'] == $row['tid']) {
    //             continue 2;
    //           }
    //       }
    //       $children = array();
    //       $result2 = $db->query("SELECT * FROM `$TABLE_KEYTREE` WHERE `parent` = '{$row['tid']}' ORDER BY  `text`");
    //       if ($result2->num_rows) {
    //         while ($childrow = $result2->fetch_array()) {
    //           array_push($children, array(KEY_VALUE => intval($childrow['id']), KEY_TEXT => $childrow[KEY_TEXT]));
    //         }
    //       }
    //       array_push($ret['options'][$row['ttype']], array(
    //           KEY_VALUE => intval($row['tid']), KEY_TEXT => $row[KEY_TEXT], KEY_COMMENT => '',
    //           KEY_CREATE_TS => $row[FIELD_CREATE_TS], KEY_LASTEDIT_TS => null, KEY_LASTEDIT_USER => intval($row[FIELD_LASTEDIT_USER]), KEY_CHILDREN => $children
    //       ));
    //     }
    //   } else {
    //     errorMessage($ret, VAL_DB_ERROR);
    //   }
    //   break;
    //
    // case "dload":
    //   if (!isValidID($id, $ret)) {break;}
    //   $ret['values'] = array();
    //   $result = $db->query("SELECT * FROM `$TABLE_DATA` WHERE `id` LIKE '$id.%'");
    //   while ($row = $result->fetch_array()) {
    //     $ret['values'][$row['id']] = floatval($row['data']);
    //   }
    //   break;
    //
    // case "dsave":
    //   if (!hasWritePermission($permission, $ret)) {break;}
    //   if (!isValidKey($key, $ret)) {break;}
    //   $result = $db->query("SELECT * FROM `$TABLE_DATA` WHERE `id` = '$key'");
    //   if ($result->num_rows) {
    //     $result = $db->query("UPDATE `$TABLE_DATA` SET `data` = $value, `lastedit_timestamp` = NOW(), `lastedit_user` = $user_id WHERE `id` = '$key'");
    //   } else {
    //     $result = $db->query("INSERT INTO `$TABLE_DATA` (`id`, `data`, `lastedit_user`) VALUES ('$key', $value, $user_id)");
    //   }
    //   if (!$result) {
    //     errorMessage($ret, VAL_DB_ERROR);
    //   }
    //   if (!$ret['error']) {
    //     success($ret, VAL_DATA_SAVED);
    //   }
    //   break;
    //
    default:
      invalidAction($ret);
  }
}
// CORS headers for dev purposes
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Headers: content-type, x-requested-with');
header('Access-Control-Allow-Credentials: true');

header('Content-Type: application/json');
echo json_encode($ret);
?>
