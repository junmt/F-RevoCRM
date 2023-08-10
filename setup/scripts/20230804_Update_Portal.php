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


global $log;

$db = PearDatabase::getInstance();

$result = $db->pquery("SELECT * FROM vtiger_customerportal_tabs WHERE tabid = ?", array(2));
if($db->num_rows($result) == 0) {
    $db->pquery("INSERT INTO vtiger_customerportal_tabs(tabid, visible, sequence, createrecord, editrecord) values (?, 1, 15, 0, 0)", array(2));
}

// 案件に対する書き込み権限を有効にする
$db->pquery("UPDATE vtiger_customerportal_tabs SET editrecord = 1 WHERE tabid = ?", array(2));

// 案件の項目に対する書き込み権限を有効にする
$db->pquery("UPDATE vtiger_customerportal_fields SET fieldinfo = replace(fieldinfo, '0', '1') WHERE tabid = ?", array(2));

