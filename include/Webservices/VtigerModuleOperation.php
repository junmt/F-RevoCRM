<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

 class VtigerModuleOperation extends WebserviceEntityOperation {
	protected $tabId;
	protected $isEntity = true;
	protected $partialDescribeFields = null;
	private $queryTotalRows = 0;

	public function __construct($webserviceObject,$user,$adb,$log)
	{
		parent::__construct($webserviceObject,$user,$adb,$log);
		$this->meta = $this->getMetaInstance();
		$this->tabId = $this->meta->getTabId();
	}
	protected function getMetaInstance(){
		if(empty(WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id])){
			WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id]  = new VtigerCRMObjectMeta($this->webserviceObject,$this->user);
		}
		return WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id];
	}

	public function create($elementType,$element){
		$crmObject = new VtigerCRMObject($elementType, false);

		$element = DataTransform::sanitizeForInsert($element,$this->meta);

		$error = $crmObject->create($element);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		
		$id = $crmObject->getObjectId();

		// Bulk Save Mode
		if(CRMEntity::isBulkSaveMode()) {
			// Avoiding complete read, as during bulk save mode, $result['id'] is enough
			return array('id' => vtws_getId($this->meta->getEntityId(), $id) );
		}

		$error = $crmObject->read($id);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
				vtws_getWebserviceTranslatedString('LBL_'.
						WebServiceErrorCode::$DATABASEQUERYERROR));
		}

		return DataTransform::filterAndSanitize($crmObject->getFields(),$this->meta);
	}

	public function retrieve($id){
		
		$ids = vtws_getIdComponents($id);
		$elemid = $ids[1];
		
		$crmObject = new VtigerCRMObject($this->tabId, true);
		$error = $crmObject->read($elemid);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		
		return DataTransform::filterAndSanitize($crmObject->getFields(),$this->meta);
	}
	
    public function relatedIds($id, $relatedModule, $relatedLabel, $relatedHandler=null) {
		$ids = vtws_getIdComponents($id);
        $sourceModule = $this->webserviceObject->getEntityName();		
        global $currentModule;
        $currentModule = $sourceModule;
		$sourceRecordModel = Vtiger_Record_Model::getInstanceById($ids[1], $sourceModule);
		$targetModel       = Vtiger_RelationListView_Model::getInstance($sourceRecordModel, $relatedModule, $relatedLabel);
        $sql = $targetModel->getRelationQuery();

        $relatedWebserviceObject = VtigerWebserviceObject::fromName($adb,$relatedModule);
        $relatedModuleWSId = $relatedWebserviceObject->getEntityId();

		// Rewrite query to pull only crmid transformed as webservice id.
        $sqlFromPart = substr($sql, stripos($sql, ' FROM ')+6);        
        $sql = sprintf("SELECT DISTINCT concat('%sx',vtiger_crmentity.crmid) as wsid FROM %s", $relatedModuleWSId, $sqlFromPart);
                
        $rs = $this->pearDB->pquery($sql, array());
        $relatedIds = array();
		while ($row = $this->pearDB->fetch_array($rs)) {
            $relatedIds[] = $row['wsid'];
		}
		return $relatedIds;
    }

	public function update($element){
		$ids = vtws_getIdComponents($element["id"]);
		$element = DataTransform::sanitizeForInsert($element,$this->meta);
		
		$crmObject = new VtigerCRMObject($this->tabId, true);
		$crmObject->setObjectId($ids[1]);
		$error = $crmObject->update($element);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		
		$id = $crmObject->getObjectId();
		
		$error = $crmObject->read($id);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
				vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		
		return DataTransform::filterAndSanitize($crmObject->getFields(),$this->meta);
	}

	public function revise($element){
		$ids = vtws_getIdComponents($element["id"]);
		$element = DataTransform::sanitizeForInsert($element,$this->meta);

		$crmObject = new VtigerCRMObject($this->tabId, true);
		$crmObject->setObjectId($ids[1]);
		$error = $crmObject->revise($element);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}

		$id = $crmObject->getObjectId();

		$error = $crmObject->read($id);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}

		return DataTransform::filterAndSanitize($crmObject->getFields(),$this->meta);
	}

	public function delete($id){
		$ids = vtws_getIdComponents($id);
		$elemid = $ids[1];
		
		$crmObject = new VtigerCRMObject($this->tabId, true);
		
		$error = $crmObject->delete($elemid);
		if(!$error){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		return array("status"=>"successful");
	}

	public function wsVTQL2SQL($q, &$meta, &$queryRelatedModules, $mode = 'new') {
		require_once 'include/Webservices/VTQL_EnhancedParser.php';

		$q = str_replace(array("\n", "\t", "\r"), ' ', $q);
		$parser = new VTQL_EnhancedParser($this->user, $q);

		if($mode == 'old') {
			$oldParser = new Parser($this->user, $q);
			$error = $oldParser->parse();
			if ($error) {
				return $oldParser->getError();
			}
			$mysql_query = $oldParser->getSql();
			$meta = $oldParser->getObjectMetaData();
		} elseif ($parser->conditionQuery($q)) {  // extended workflow condition syntax
			$moduleRegex = '/[fF][rR][Oo][Mm]\s+([^\s;]+)/';
			preg_match($moduleRegex, $q, $m);
			$fromModule = trim($m[1]);
			$handler = vtws_getModuleHandlerFromName($fromModule, $this->user);
			$meta = $handler->getMeta();
			list($mysql_query, $queryRelatedModules) = $parser->conditionGetQuery($q, $fromModule, $this->user);
		} else {  // FQN extended syntax
			list($mysql_query,$queryRelatedModules) = $parser->getQuery($q, $this->user);
			$moduleRegex = "/[fF][rR][Oo][Mm]\s+([^\s;]+)/";
			preg_match($moduleRegex, $q, $m);
			$fromModule = trim($m[1]);
			$handler = vtws_getModuleHandlerFromName($fromModule, $this->user);
			$meta = $handler->getMeta();
		}

		return $mysql_query;
	}

	public function query($q) {
		$mysql_query = $this->wsVTQL2SQL($q, $meta, $queryRelatedModules);
		return $this->querySQLResults($mysql_query, $q, $meta, $queryRelatedModules);
	}

	public function querySQLResults($mysql_query, $q, $meta, $queryRelatedModules, $addimagefields = true, $keycase = ADODB_ASSOC_CASE_LOWER) {
		global $site_URL, $adb, $default_charset, $currentModule;
		$holdCM = $currentModule;
		$currentModule = $meta->getEntityName();
		if (strpos($mysql_query, 'vtiger_inventoryproductrel')) {
			$invlines = true;
			$pdoWebserviceObject = VtigerWebserviceObject::fromName($adb, 'Products');
			$pdowsid = $pdoWebserviceObject->getEntityId();
			$srvWebserviceObject = VtigerWebserviceObject::fromName($adb, 'Services');
			$srvwsid = $srvWebserviceObject->getEntityId();
		} else {
			$invlines = false;
		}
		$this->pearDB->startTransaction();
		$result = $this->pearDB->pquery($mysql_query, array());
		$error = $this->pearDB->hasFailedTransaction();
		$this->pearDB->completeTransaction();

		if ($error) {
			$currentModule = $holdCM;
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, vtws_getWebserviceTranslatedString('LBL_'.WebServiceErrorCode::$DATABASEQUERYERROR));
		}
		if ($addimagefields) {
			$imageFields = $meta->getImageFields();
		}
		$isDocModule = ($meta->getEntityName()=='Documents');
		$parser = new VTQL_EnhancedParser($this->user, $q);
		$isRelatedQuery = $parser->isFQNQuery($q);
		$noofrows = $this->pearDB->num_rows($result);
		$output = array();
		$streamraw = (isset($_REQUEST['format']) && strtolower($_REQUEST['format'])=='streamraw');
		$streaming = (isset($_REQUEST['format']) && (strtolower($_REQUEST['format'])=='stream' || $streamraw));
		$stream = '';
		$fieldColumnMapping = $meta->getFieldColumnMapping();
		for ($i=0; $i<$noofrows; $i++) {
			$row = $this->pearDB->fetchByAssoc($result, $i, true, $keycase);
			$rowcrmid = (isset($row[$fieldColumnMapping['id']]) ? $row[$fieldColumnMapping['id']] : (isset($row['crmid']) ? $row['crmid'] : (isset($row['id']) ? $row['id'] : '')));
			if (!$meta->hasPermission(EntityMeta::$RETRIEVE, $rowcrmid)) {
				continue;
			}
			if ($streamraw) {
				$newrow = $row;
			} else {
				$newrow = DataTransform::sanitizeDataWithColumn($row, $meta);
				if ($isRelatedQuery) {
					if ($invlines) {
						$newrow = $row;
						if (!empty($newrow['id'])) {
							$newrow['id'] = vtws_getWebserviceEntityId(getSalesEntityType($newrow['id']), $newrow['id']);
						}
						$newrow['linetype'] = '';
						if (!empty($newrow['productid'])) {
							$newrow['linetype'] = getSalesEntityType($newrow['productid']);
							$newrow['productid'] = ($newrow['linetype'] == 'Products' ? $pdowsid : $srvwsid) . 'x' . $newrow['productid'];
						}
						if (!empty($newrow['serviceid'])) {
							$newrow['linetype'] = 'Services';
							$newrow['serviceid'] = $srvwsid . 'x' . $newrow['serviceid'];
						}
					} else {
						$relflds = array_diff_key($row, $newrow);
						foreach ($queryRelatedModules as $relmod => $relmeta) {
							$lrm = strtolower($relmod);
							$newrflds = array();
							foreach ($relflds as $fldname => $fldvalue) {
								$fldmod = substr($fldname, 0, strlen($relmod));
								if (isset($row[$fldname]) && $fldmod==$lrm) {
									$newkey = substr($fldname, strlen($lrm));
									$newrflds[$newkey] = $fldvalue;
								}
							}
							$relrow = DataTransform::sanitizeDataWithColumn($newrflds, $relmeta);
							$newrelrow = array();
							foreach ($relrow as $key => $value) {
								$newrelrow[$lrm.$key] = $value;
							}
							$newrow = array_merge($newrow, $newrelrow);
						}
					}
				}
				if ($isDocModule) {
					$relatt=$adb->pquery('SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid=?', array($rowcrmid));
					if ($relatt && $adb->num_rows($relatt)==1) {
						$fileid = $adb->query_result($relatt, 0, 0);
						$attrs=$adb->pquery('SELECT * FROM vtiger_attachments WHERE attachmentsid=?', array($fileid));
						if ($attrs && $adb->num_rows($attrs) == 1) {
							$name = @$adb->query_result($attrs, 0, 'name');
							$filepath = @$adb->query_result($attrs, 0, 'path');
							$name = html_entity_decode($name, ENT_QUOTES, $default_charset);
							// $newrow['_downloadurl'] = $site_URL.'/'.$filepath.$fileid.'_'.$name;
							$newrow['filename'] = $name;
						}
					}
				}
			}
			if ($streaming) {
				$stream .= json_encode($newrow)."\n";
				if (($i % 500)==0) {
					echo $stream;
					flush();
					$stream = '';
				}
			} else {
				$output[] = $newrow;
			}
		}
		if ($stream!='') {
			echo $stream;
			flush();
			$stream = '';
		}

		$query = preg_replace("/[\n\r\s]+/", ' ', $mysql_query);
		if (strripos($query, ' ORDER BY ') > 0) {
			$query = substr($query, 0, strripos($query, ' ORDER BY '));
		}
		if (strripos($query, ' LIMIT ') > 0) {
			$query = substr($query, 0, strripos($query, ' LIMIT '));
		}
		$mysql_query = "SELECT count(*) AS cnt".substr($query, stripos($query, ' FROM '), strlen($query));

		$result = $this->pearDB->pquery($mysql_query, array());
		if ($result) {
			if ($result->fields) {
				$this->queryTotalRows = $result->fields['cnt'];
			} else {
				$this->queryTotalRows = 0;
			}
		} else {
			$this->queryTotalRows = 0;
		}
		$currentModule = $holdCM;
		return $output;
	}

	public function getQueryTotalRows() {
		return $this->queryTotalRows;
	}

	public function describe($elementType){
		$app_strings = VTWS_PreserveGlobal::getGlobal('app_strings');
		$current_user = vtws_preserveGlobal('current_user',$this->user);;
		
		$label = (isset($app_strings[$elementType]))? $app_strings[$elementType]:$elementType;
		$createable = (strcasecmp(isPermitted($elementType,EntityMeta::$CREATE),'yes')===0)? true:false;
		$updateable = (strcasecmp(isPermitted($elementType,EntityMeta::$UPDATE),'yes')===0)? true:false;
		$deleteable = $this->meta->hasDeleteAccess();
		$retrieveable = $this->meta->hasReadAccess();
		$fields = $this->getModuleFields();
		return array(	'label'			=> $label,
						'name'			=> $elementType,
						'createable'	=> $createable,
						'updateable'	=> $updateable,
						'deleteable'	=> $deleteable,
						'retrieveable'	=> $retrieveable,
						'fields'		=> $fields,
						'idPrefix'		=> $this->meta->getEntityId(),
						'isEntity'		=> $this->isEntity,
						'allowDuplicates'=>  $this->meta->isDuplicatesAllowed(),
						'labelFields'	=> $this->meta->getNameFields());
	}
	
	public function describePartial($elementType, $fields=null) {
		$this->partialDescribeFields = $fields;
		$result = $this->describe($elementType);
		$this->partialDescribeFields = null;
		return $result;
	}
	
	function getModuleFields(){
		
		$fields = array();
		$moduleFields = $this->meta->getModuleFields();
		foreach ($moduleFields as $fieldName=>$webserviceField) {
			if(((int)$webserviceField->getPresence()) == 1) {
				continue;
			}
			array_push($fields,$this->getDescribeFieldArray($webserviceField));
		}
		array_push($fields,$this->getIdField($this->meta->getObectIndexColumn()));
		
		return $fields;
	}
	
	function getDescribeFieldArray($webserviceField){
		$default_language = VTWS_PreserveGlobal::getGlobal('default_language');
		
		$fieldLabel = getTranslatedString($webserviceField->getFieldLabelKey(), $this->meta->getTabName());
		
		$typeDetails = array();
		if (!is_array($this->partialDescribeFields)) {
			$typeDetails = $this->getFieldTypeDetails($webserviceField);
		} else if (in_array($webserviceField->getFieldName(), $this->partialDescribeFields)) {
			$typeDetails = $this->getFieldTypeDetails($webserviceField);
		}
		
		//set type name, in the type details array.
		$typeDetails['name'] = $webserviceField->getFieldDataType();
		//Reference module List is missing in DescribePartial api response
		if($typeDetails['name'] === "reference") {
			$typeDetails['refersTo'] = $webserviceField->getReferenceList();
		}
		$editable = $this->isEditable($webserviceField);
		
		$describeArray = array(	'name'		=> $webserviceField->getFieldName(),
								'label'		=> $fieldLabel,
								'mandatory'	=> $webserviceField->isMandatory(),
								'type'		=> $typeDetails,
								'isunique'	=> $webserviceField->isUnique(),
								'nullable'	=> $webserviceField->isNullable(),
								'editable'	=> $editable);
		if($webserviceField->hasDefault()){
			$describeArray['default'] = $webserviceField->getDefault();
		}
		return $describeArray;
	}
	
	function getMeta(){
		return $this->meta;
	}
	
	function getField($fieldName){
		$moduleFields = $this->meta->getModuleFields();
		return $this->getDescribeFieldArray($moduleFields[$fieldName]);
	}
    
    /**
     * Function to get the file content
     * @param type $id
     * @return type
     * @throws WebServiceException
     */
    public function file_retrieve($crmid, $elementType, $attachmentId=false){
		$ids = vtws_getIdComponents($crmid);
		$crmid = $ids[1];
        $recordModel = Vtiger_Record_Model::getInstanceById($crmid, $elementType);
        if($attachmentId) {
            $attachmentDetails = $recordModel->getFileDetails($attachmentId);
        } else {
            $attachmentDetails = $recordModel->getFileDetails();
        }
        $fileDetails = array();
        if (!empty ($attachmentDetails)) {
            if(is_array(current(($attachmentDetails)))) {
                foreach ($attachmentDetails as $key => $attachment) {
                    $fileDetails[$key] = vtws_filedetails($attachment);
                }
            } else if(is_array($attachmentDetails)){
                $fileDetails[] = vtws_filedetails($attachmentDetails);
            }
        }
        return $fileDetails;
	}
	
}
?>