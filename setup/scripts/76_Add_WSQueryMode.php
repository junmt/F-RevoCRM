<?php
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
include_once('modules/PickList/DependentPickListUtils.php');
include_once('modules/ModTracker/ModTracker.php');
include_once('include/utils/CommonUtils.php');
include_once('includes/Loader.php');

require_once('setup/utils/FRFieldSetting.php');
require_once('setup/utils/FRFilterSetting.php');
require_once('includes/runtime/BaseModel.php');
require_once('modules/Settings/Vtiger/models/Module.php');
require_once('modules/Settings/MenuEditor/models/Module.php');
require_once('modules/Vtiger/models/MenuStructure.php');
require_once('modules/Vtiger/models/Module.php');

global $adb, $log;

$result = $adb->pquery('SELECT operationid FROM vtiger_ws_operation WHERE name=?', array('query'));
if($adb->num_rows($result) > 0) {
    $operationid = $adb->query_result($result, 0, 'operationid');
}

if(empty($operationid)) {
    echo 'queryオペレーションのIDが見つかりませんでした';
    return ;
}

$result = $adb->pquery('SELECT * FROM vtiger_ws_operation_parameters WHERE operationid=? and name = ?', array($operationid, 'mode'));
if($adb->num_rows($result) == 0) {
    $adb->pquery('INSERT INTO vtiger_ws_operation_parameters (operationid, name, type, sequence) VALUES (?, ?, ?, ?)', array($operationid, 'mode', 'String', 2));
}

echo "実行が完了しました。<br>";
