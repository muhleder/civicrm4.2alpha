<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2009.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


/**
 * This class acts like a psuedo-BAO for transient import job tables
 */
 
class CRM_Import_ImportJob {
    
    protected $_tableName;
    protected $_primaryKeyName;
    protected $_statusFieldName;
    
    protected $_doGeocodeAddress;
    protected $_invalidRowCount;
    protected $_conflictRowCount;
    protected $_onDuplicate;
    protected $_dedupe;
    protected $_newGroupName;
    protected $_newGroupDesc;
    protected $_groups;
    protected $_allGroups;
    protected $_newTagName;
    protected $_newTagDesc;
    protected $_tag;
    protected $_allTags;
    
    protected $_mapper;
    protected $_mapperKeys;
    protected $_mapperLocTypes;
    protected $_mapperPhoneTypes;
    protected $_mapperImProviders;
    protected $_mapperWebsiteTypes;
    protected $_mapperRelated;
    protected $_mapperRelatedContactType;
    protected $_mapperRelatedContactDetails;
    protected $_mapperRelatedContactLocType;
    protected $_mapperRelatedContactPhoneType;
    protected $_mapperRelatedContactImProvider;
    protected $_mapperRelatedContactWebsiteType;
    protected $_mapFields;
    
    protected $_parser;
    
    public function __construct( $tableName = null, $createSql = null, $createTable = false ) {
        $dao = new CRM_Core_DAO();
        $db = $dao->getDatabaseConnection();
        
        if ( $createTable ) {
            if ( !$createSql ) {
                CRM_Core_Error::fatal('Either an existing table name or an SQL query to build one are required');
            }
            
            // FIXME: we should regen this table's name if it exists rather than drop it
            if ( !$tableName ) {
                $tableName = 'civicrm_import_job_' . md5(uniqid(rand(), true));  
            }
            $db->query("DROP TABLE IF EXISTS $tableName");
            $db->query("CREATE TABLE $tableName ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci $createSql");
        }
        
        if ( !$tableName ) {
            CRM_Core_Error::fatal( 'Import Table is required.' );
        }
        
        $this->_tableName = $tableName;
        
        //initialize the properties.
        $properties = array( 'mapperKeys', 
                             'mapperRelated', 
                             'mapperLocTypes', 
                             'mapperPhoneTypes', 
                             'mapperImProviders', 
                             'mapperWebsiteTypes', 
                             'mapperRelatedContactType', 
                             'mapperRelatedContactDetails', 
                             'mapperRelatedContactLocType', 
                             'mapperRelatedContactPhoneType', 
                             'mapperRelatedContactImProvider',
                             'mapperRelatedContactWebsiteType' );
        foreach ( $properties as $property ) $this->{"_$property"} = array( ); 
        
    }
    
    public function getTableName() {
        return $this->_tableName;
    }
    
    public function isComplete( $dropIfComplete = true ) {
        if (!$this->_statusFieldName) {
            CRM_Core_Error::fatal("Could not get name of the import status field");
        }
        $query = "SELECT * FROM $this->_tableName
                  WHERE  $this->_statusFieldName = 'NEW' LIMIT 1";
        $result = CRM_Core_DAO::executeQuery( $query );
        if ($result->fetch()) {
            return false;
        }
        if ( $dropIfComplete ) {
            $query = "DROP TABLE $this->_tableName";
            CRM_Core_DAO::executeQuery( $query );
        }
        return true;
    }
    
    public function setJobParams( &$params )
    {       
        foreach ( $params as $param => $value ) {
            eval( "\$this->_$param = \$value;");
        }
    }
    
    public function runImport(&$form, $timeout = 55) {
        $mapper         = $this->_mapper;
        $mapperFields   = array( );
        $phoneTypes     = CRM_Core_PseudoConstant::phoneType();
        $imProviders    = CRM_Core_PseudoConstant::IMProvider();
        $websiteTypes   = CRM_Core_PseudoConstant::websiteType();
        $locationTypes  = CRM_Core_PseudoConstant::locationType();
        
        //initialize mapper perperty value.
        $mapperPeroperties = array( 'mapperRelated'                  => 'mapperRelatedVal', 
                                    'mapperLocTypes'                 => 'mapperLocTypesVal', 
                                    'mapperPhoneTypes'               => 'mapperPhoneTypesVal', 
                                    'mapperImProviders'              => 'mapperImProvidersVal', 
                                    'mapperWebsiteTypes'             => 'mapperWebsiteTypesVal', 
                                    'mapperRelatedContactType'       => 'mapperRelatedContactTypeVal', 
                                    'mapperRelatedContactDetails'    => 'mapperRelatedContactDetailsVal', 
                                    'mapperRelatedContactLocType'    => 'mapperRelatedContactLocTypeVal', 
                                    'mapperRelatedContactPhoneType'  => 'mapperRelatedContactPhoneTypeVal', 
                                    'mapperRelatedContactImProvider' => 'mapperRelatedContactImProviderVal',
                                    'mapperRelatedContactWebsiteType'=> 'mapperRelatedContactWebsiteTypeVal' );
        
        foreach ( $mapper as $key => $value ) {
            //set respective mapper value to null.
            foreach ( array_values( $mapperPeroperties ) as $perpertyVal ) $$perpertyVal = null;  
            
            $header   = array( );
            $fldName  = CRM_Utils_Array::value( 0, $mapper[$key] );
            $selOne   = CRM_Utils_Array::value( 1, $mapper[$key] );
            $selTwo   = CRM_Utils_Array::value( 2, $mapper[$key] );
            $selThree = CRM_Utils_Array::value( 3, $mapper[$key] );
            $this->_mapperKeys[$key] = $fldName;
            
            //need to differentiate non location elements.
            if ( $selOne && is_numeric( $selOne ) ) {
                if ( $fldName == 'url' ) {
                    $header[] = $websiteTypes[$selOne]; 
                    $mapperWebsiteTypesVal = $selOne;
                } else {
                    $header[] = $locationTypes[$selOne];
                    $mapperLocTypesVal = $selOne;
                    if ( $selTwo && is_numeric( $selTwo ) ) {
                        if ( $fldName == 'phone' ) {
                            $header[] = $phoneTypes[$selTwo];
                            $mapperPhoneTypesVal = $selTwo;
                        } else if ( $fldName == 'im' ) {
                            $header[] = $imProviders[$selTwo];
                            $mapperImProvidersVal = $selTwo;
                        }
                    }
                }
            }
            
            $fldNameParts = explode( '_', $fldName, 3 );
            $id           = $fldNameParts[0];
            $first        = isset($fldNameParts[1])? $fldNameParts[1] : null;
            $second       = isset($fldNameParts[2])? $fldNameParts[2] : null;
            if ( ($first == 'a' && $second == 'b') ||
                 ($first == 'b' && $second == 'a') ) {
                
                $header[] = ucwords(str_replace("_", " ", $selOne ) );
                
                $relationType = new CRM_Contact_DAO_RelationshipType();
                $relationType->id = $id;
                $relationType->find(true);
                $mapperRelatedContactTypeVal = $relationType->{"contact_type_$second"};
                
                $mapperRelatedVal = $fldName;
                if ( $selOne ) {
                    $mapperRelatedContactDetailsVal = $selOne;
                    if ( $selTwo ) {
                        if ( $selOne == 'url' ) {
                            $header[] = $websiteTypes[$selTwo];
                            $mapperRelatedContactWebsiteTypeVal = $selTwo;
                        } else {
                            $header[] = $locationTypes[$selTwo];
                            $mapperRelatedContactLocTypeVal = $selTwo;
                            if ( $selThree ) {
                                if ( $selOne == 'phone' ) {
                                    $header[] = $phoneTypes[$selThree];
                                    $mapperRelatedContactPhoneTypeVal = $selThree;
                                } else if ( $selOne == 'im' ) {
                                    $header[] = $imProviders[$selThree];
                                    $mapperRelatedContactImProviderVal = $selThree;
                                }
                            }
                        }
                    }
                }
            }
            $mapperFields[] = implode(' - ', $header);
            
            //set the respective mapper param array values.
            foreach ( $mapperPeroperties as $mapperProKey => $mapperProVal ) {
                $this->{"_$mapperProKey"}[$key] = $$mapperProVal;
            }
        }
        
        $this->_parser = new CRM_Import_Parser_Contact( 
            $this->_mapperKeys, 
            $this->_mapperLocTypes,
            $this->_mapperPhoneTypes,
            $this->_mapperImProviders,
            $this->_mapperRelated, 
            $this->_mapperRelatedContactType,
            $this->_mapperRelatedContactDetails,
            $this->_mapperRelatedContactLocType, 
            $this->_mapperRelatedContactPhoneType, 
            $this->_mapperRelatedContactImProvider,
            $this->_mapperWebsiteTypes,
            $this->_mapperRelatedContactWebsiteType );
        
        $this->_parser->run( $this->_tableName, $mapperFields,
                             CRM_Import_Parser::MODE_IMPORT,
                             $this->_contactType,
                             $this->_primaryKeyName,
                             $this->_statusFieldName,
                             $this->_onDuplicate,
                             $this->_statusID,
                             $this->_totalRowCount,
                             $this->_doGeocodeAddress,
                             CRM_Import_Parser::DEFAULT_TIMEOUT, 
                             $this->_contactSubType,
                             $this->_dedupe
                             );
                      
        $contactIds = $this->_parser->getImportedContacts( );
        
        //get the related contactIds. CRM-2926
        $relatedContactIds = $this->_parser->getRelatedImportedContacts( );
        if ( $relatedContactIds ) { 
            $contactIds = array_merge( $contactIds, $relatedContactIds );
            if ( $form ) {
                $form->set('relatedCount', count($relatedContactIds) );
            }
        }
        
        if ( $this->_newGroupName || count($this->_groups) ) {
            $groupAdditions = $this->_addImportedContactsToNewGroup($contactIds,
                                                                    $this->_newGroupName,
                                                                    $this->_newGroupDesc);
            if ($form) $form->set('groupAdditions', $groupAdditions);
        }
        
        if ( $this->_newTagName || count($this->_tag) ) {
            $tagAdditions = $this->_tagImportedContactsWithNewTag($contactIds,
                                                                  $this->_newTagName,
                                                                  $this->_newTagDesc);
            if ($form) $form->set('tagAdditions', $tagAdditions);
        }
    }
    
    public function setFormVariables( $form ) {
        $this->_parser->set( $form, CRM_Import_Parser::MODE_IMPORT );
    }
    
    private function _addImportedContactsToNewGroup( $contactIds,
                                                     $newGroupName, $newGroupDesc ) {
        
        $newGroupId = null;
        
        if ($newGroupName) {
            /* Create a new group */
            $gParams = array(
                             'title'         => $newGroupName,
                             'description'   => $newGroupDesc,
                             'is_active'     => true,
                             );
            $group = CRM_Contact_BAO_Group::create($gParams);
            $this->_groups[] = $newGroupId = $group->id;
        }
        
        if (is_array($this->_groups)) {
            $groupAdditions = array();
            foreach ($this->_groups as $groupId) {
                $addCount = CRM_Contact_BAO_GroupContact::addContactsToGroup($contactIds, $groupId);
                $totalCount = $addCount[1];
                if ($groupId == $newGroupId) {
                    $name = $newGroupName;
                    $new = true;
                } else {
                    $name = $this->_allGroups[$groupId];
                    $new = false;
                }
                $groupAdditions[] = array(
                                          'url'      => CRM_Utils_System::url( 'civicrm/group/search',
                                                                               'reset=1&force=1&context=smog&gid=' . $groupId ),
                                          'name'     => $name,
                                          'added'    => $totalCount,
                                          'notAdded' => $addCount[2],
                                          'new'      => $new
                                          );
            }
            return $groupAdditions;
        }
        return false;
    }
    
    private function _tagImportedContactsWithNewTag( $contactIds,
        $newTagName, $newTagDesc ) {
        
        $newTagId = null;
        if ($newTagName) {
            /* Create a new Tag */
            $tagParams = array(
                               'name'          => $newTagName,
                               'title'         => $newTagName,
                               'description'   => $newTagDesc,
                               'is_selectable' => true,
                               'used_for'      => 'civicrm_contact' 
                               );
            $id = array();
            $addedTag = CRM_Core_BAO_Tag::add($tagParams,$id);
            $this->_tag[$addedTag->id] = 1;
        }
        //add Tag to Import   

        if ( is_array($this->_tag) ) {
            $tagAdditions = array();
            foreach ($this->_tag as $tagId =>$val) {
                $addTagCount = CRM_Core_BAO_EntityTag::addEntitiesToTag( $contactIds, $tagId );
                $totalTagCount = $addTagCount[1];
                if ( isset( $addedTag ) && $tagId == $addedTag->id ) {
                    $tagName = $newTagName;
                    $new = true;
                } else {
                    $tagName = $this->_allTags[$tagId];
                    $new = false;
                }
                $tagAdditions[] = array(
                                        'url'      => CRM_Utils_System::url( 'civicrm/contact/search',
                                                                             'reset=1&force=1&context=smog&id=' . $tagId ),
                                        'name'     => $tagName,
                                        'added'    => $totalTagCount,
                                        'notAdded' => $addTagCount[2],
                                        'new'      => $new
                                        );
            }
            return $tagAdditions;
        }
        return false;
    }
    
    public static function getIncompleteImportTables() {
        $dao = new CRM_Core_DAO();
        $database = $dao->database();
        $query = "SELECT   TABLE_NAME FROM INFORMATION_SCHEMA
                  WHERE    TABLE_SCHEMA = ? AND
                           TABLE_NAME LIKE 'civicrm_import_job_%'
                  ORDER BY TABLE_NAME";
        $result = CRM_Core_DAO::executeQuery($query, array($database));
        $incompleteImportTables = array();
        while ($importTable = $result->fetch()) {
            if (!$this->isComplete($importTable)) {
                $incompleteImportTables[] = $importTable;
            }
        }
        return $incompleteImportTables;
    }
}
