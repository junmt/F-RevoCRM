<?php
class TestSaveHandler extends VTEventHandler {
    function handleEvent($name, $entityData) {
        if($name != 'vtiger.entity.aftersave') {
            return ;
        }

        if($entityData->getModuleName() != 'Test') {
            return ;
        }

        global $adb;
        $accountid = $entityData->get('accountid');
    
        $sql = "SELECT
                    sum(cf.cf_893) as sum
                FROM
                    vtiger_test t
                    INNER JOIN vtiger_testcf cf ON cf.testid = t.testid
                    INNER JOIN vtiger_crmentity c ON c.crmid = t.testid
                WHERE
                    c.deleted = 0
                    AND t.accountid = ?";
        $params = array($accountid);
    
        $result = $adb->pquery($sql, $params);
        if($adb->num_rows($result) > 0) {
            $sum = $adb->query_result($result, 0, "sum");
        }
    
        $account = Vtiger_Record_Model::getInstanceById($accountid);
        $account->set("cf_891", $sum);
        $account->set('mode', 'edit');
        $account->save();
        // $cf_893 = $entityData->get('cf_893');
    }
}
