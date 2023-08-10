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

$params = array(
    'vtiger.entity.aftersave',
    'modules/Test/TestSaveHanlder.php',
    'TestSaveHandler',
    '',
    1,
    '[]'
);

$db->pquery("INSERT INTO vtiger_eventhandlers(eventhandler_id, event_name, handler_path, handler_class, cond, is_active, dependent_on)
            SELECT max(eventhandler_id) + 1, ?, ?, ?, ?, ?, ? FROM vtiger_eventhandlers limit 1", $params);
