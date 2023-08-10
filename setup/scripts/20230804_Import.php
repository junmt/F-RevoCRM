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

require_once('includes/runtime/Globals.php');

global $adb, $log, $current_user, $VTIGER_BULK_SAVE_MODE;

// ログインしているユーザー作成
$current_user = new Users();
$current_user->id = 1;

$user = CRMEntity::getInstance('Users');
$user->retrieveCurrentUserInfoFromFile(1);
vglobal('current_user', $user);

$VTIGER_BULK_SAVE_MODE = false;

$adb->startTransaction();

//CSVを読み込み、Vtiger_Record_Modelを作成する
function createRecordModel($csv) {
    $recordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
    $recordModel->set('accountname', $csv[0]);
    $recordModel->set('assigned_user_id', 1);
    return $recordModel;
}

//CSVファイルを１行ずつ読み込む
$fp = fopen('setup/scripts/Accounts.csv', 'r');
$cnt = 0;
while (($csv = fgetcsv($fp)) !== FALSE) {
    //CSVを読み込み、Vtiger_Record_Modelを作成する
    $recordModel = createRecordModel($csv);
    //Vtiger_Record_Modelを保存する
    $recordModel->save();
    $cnt++;
    if($cnt % 1000 == 0) {
        echo $cnt.PHP_EOL;
        $adb->completeTransaction();
        $adb->startTransaction();
    }
}

$adb->completeTransaction();

