<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
include_once 'vtlib/Vtiger/Field.php';

/**
 * Vtiger Field Model Class
 */
class Accounts_Field_Model extends Vtiger_Field_Model {

	/**
	 * Function to check whether the current field is editable
	 * @return <Boolean> - true/false
	 */
	public function isEditable() {
		if($this->get('name') == 'cf_891') {
			return false;
		}
		if(!$this->isEditEnabled()
				|| !$this->isViewable()
				|| !in_array(((int)$this->get('displaytype')), array(1,5))
				|| $this->isReadOnly() == true
				|| $this->get('uitype') ==  4) {

			return false;
		}
		return true;
	}

}
