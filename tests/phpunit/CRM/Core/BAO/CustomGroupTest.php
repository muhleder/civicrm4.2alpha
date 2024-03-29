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
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
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


require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';
require_once 'CiviTest/Custom.php';

class CRM_Core_BAO_CustomGroupTest extends CiviUnitTestCase 
{
    function get_info( ) 
    {
        return array(
                     'name'        => 'CustomGroup BAOs',
                     'description' => 'Test all Core_BAO_CustomGroup methods.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    function setUp( ) 
    {
        parent::setUp();
    }
    
    /**
     * Function to test getTree()
     */
    function testGetTree()
    {
        $params      = array( );
        $contactId   = Contact::createIndividual();
        $customGrouptitle = 'My Custom Group';
        $groupParams = array(
                             'title'      => $customGrouptitle,
                             'name'       => 'my_custom_group',
                             'style'      => 'Tab',
                             'extends'    => 'Individual',
                             'is_active'  => 1,
                             'version'    => 3
                             );
        
        $customGroup = Custom::createGroup( $groupParams );
        
        $customGroupId = $customGroup->id;
        
        $fields      = array (
                              'groupId'  =>  $customGroupId,
                              'dataType' => 'String',
                              'htmlType' => 'Text'
                              );
        
        $customField = Custom::createField( $params, $fields );
        $formParams = NULL;
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $getTree = CRM_Core_BAO_CustomGroup::getTree('Individual', $formParams, $customGroupId );
        
        $dbCustomGroupTitle = $this->assertDBNotNull( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'title', 'id',
                                                      'Database check for custom group record.' );
        
        Custom::deleteField( $customField );        
        Custom::deleteGroup( $customGroup );
        Contact::delete( $contactId );
        $customGroup->free();
    }
    
    
    /**
     * Function to test retrieve() with Empty Params
     */
    function testRetrieveEmptyParams( )
    {     
        $params = array( ); 
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroup = CRM_Core_BAO_CustomGroup::retrieve( $params, $dafaults );
        $this->assertNull( $customGroup , 'Check that no custom Group is retreived'  );
    }
    
    /**
     * Function to test retrieve() with Inalid Params
     */
    function testRetrieveInvalidParams( )
    { 
        $params = array( 'id' => 99 ); 
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroup = CRM_Core_BAO_CustomGroup::retrieve( $params, $dafaults );
        $this->assertNull( $customGroup , 'Check that no custom Group is retreived'  );      
    }
    
    /**
     * Function to test retrieve()
     */
    function testRetrieve()
    {
        $customGrouptitle = 'My Custom Group';
        $groupParams = array(
                             'title'            => $customGrouptitle,
                             'name'             => 'My_Custom_Group',
                             'style'            => 'Tab',
                             'extends'          => 'Individual',
                             'help_pre'         => 'Custom Group Help Pre',
                             'help_post'        => 'Custom Group Help Post',
                             'is_active'        => 1,
                             'collapse_display' => 1,
                             'weight'           => 2,
                             'version'          => 3
                             );
        
        $customGroup = Custom::createGroup( $groupParams );
        $customGroupId = $customGroup->id;
        
        $params = array( 'id' => $customGroupId );
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroup = CRM_Core_BAO_CustomGroup::retrieve( $params, $dafaults );
        $dbCustomGroupTitle = $this->assertDBNotNull( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'title', 'id',
                                                      'Database check for custom group record.' );
        
        $this->assertEquals( $customGrouptitle, $dbCustomGroupTitle );
        //check retieve values
        unset( $groupParams['version'] );
        $this->assertAttributesEquals( $groupParams, $dafaults ); 
        
        //cleanup DB by deleting customGroup
        Custom::deleteGroup( $customGroup );
    }
    
    /**
     * Function to test setIsActive()
     */
    function testSetIsActive()
    {
        $customGrouptitle = 'My Custom Group';
        $groupParams = array(
                             'title'      => $customGrouptitle,
                             'name'       => 'my_custom_group',
                             'style'      => 'Tab',
                             'extends'    => 'Individual',
                             'is_active'  => 0,
                             'version'    => 3
                             );
        
        $customGroup = Custom::createGroup( $groupParams );
        $customGroupId = $customGroup->id;
        
        require_once 'CRM/Core/BAO/CustomGroup.php';
        //update is_active
        $result = CRM_Core_BAO_CustomGroup::setIsActive( $customGroupId, true );
        
        //check for object update
        $this->assertEquals( true, $result );
        //check for is_active
        $this->assertDBCompareValue( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'is_active', 'id', 1, 
                                     'Database check for custom group is_active field.' );
        //cleanup DB by deleting customGroup
        Custom::deleteGroup( $customGroup );
    }
    
    /**
     * Function to test getGroupDetail() with Empty Params
     */
    function testGetGroupDetailEmptyParams( )
    {   
        $customGroupId = array( ); 
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroup = CRM_Core_BAO_CustomGroup::getGroupDetail( $customGroupId );
        $this->assertEquals( empty( $customGroup ) , 'Check that no custom Group  details is retreived'  );
    }
    
    /**
     * Function to test getGroupDetail() with Inalid Params
     */
    function testGetGroupDetailInvalidParams( )
    { 
        $customGroupId =  99; 
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroup = CRM_Core_BAO_CustomGroup::getGroupDetail( $customGroupId );
        $this->assertEquals( empty( $customGroup ) ,  'Check that no custom Group  details is retreived'  );
    }
    
    /**
     * Function to test getGroupDetail()
     */
    function testGetGroupDetail()
    {
        $customGrouptitle = 'My Custom Group';
        $groupParams = array(
                             'title'            => $customGrouptitle,
                             'name'             => 'My_Custom_Group',
                             'extends'          => 'Individual',
                             'help_pre'         => 'Custom Group Help Pre',
                             'help_post'        => 'Custom Group Help Post',
                             'is_active'        => 1,
                             'collapse_display' => 1,
                             'version'          => 3
                             );
        
        $customGroup = Custom::createGroup( $groupParams );
        $customGroupId = $customGroup->id;
        
        $fieldParams = array(
                             'custom_group_id' => $customGroupId,
                             'label'           => 'Test Custom Field',
                             'html_type'       => 'Text',
                             'data_type'       => 'String',
                             'is_required'     => 1,
                             'is_searchable'   => 0,
                             'is_active'       => 1,
                             'version'         => 3
                             );
        
        $customField = Custom::createField( $fieldParams );
        $customFieldId = $customField->id;
        
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $groupTree = CRM_Core_BAO_CustomGroup::getGroupDetail( $customGroupId );
        $dbCustomGroupTitle = $this->assertDBNotNull( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'title', 'id',
                                                      'Database check for custom group record.' );
        //check retieve values of custom group
        unset( $groupParams['is_active'] );
        unset( $groupParams['title'] );
        unset( $groupParams['version'] );
        $this->assertAttributesEquals( $groupParams, $groupTree[$customGroupId] ); 
        
        //check retieve values of custom field
        unset( $fieldParams['is_active'] );
        unset( $fieldParams['custom_group_id'] );
        unset( $fieldParams['version'] );
        $this->assertAttributesEquals( $fieldParams, $groupTree[$customGroupId]['fields'][$customFieldId] , " in line " . __LINE__); 
        
        //cleanup DB by deleting customGroup
        Custom::deleteField( $customField ); 
        Custom::deleteGroup( $customGroup );
    }
    
    /**
     * Function to test getTitle() with Invalid Params()
     */
    function testGetTitleWithInvalidParams( )
    {
        $params = 99;
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroupTitle =  CRM_Core_BAO_CustomGroup::getTitle( $params );
        
        $this->assertNull( $customGroupTitle , 'Check that no custom Group Title is retreived'  );
    }
    
    
    /**
     * Function to test getTitle()
     */
    function testGetTitle()
    {
        $customGrouptitle = 'My Custom Group';
        $groupParams = array(
                             'title'      => $customGrouptitle,
                             'name'       => 'my_custom_group',
                             'style'      => 'Tab',
                             'extends'    => 'Individual',
                             'is_active'  => 0,
                             'version'    => 3
                             );
        
        $customGroup = Custom::createGroup( $groupParams );
        $customGroupId = $customGroup->id;
        
        require_once 'CRM/Core/BAO/CustomGroup.php';
        //get the custom group title
        $title = CRM_Core_BAO_CustomGroup::getTitle( $customGroupId );
        
        //check for object update
        $this->assertEquals( $customGrouptitle, $title );
        
        //cleanup DB by deleting customGroup
        Custom::deleteGroup( $customGroup );
    }
    
    /**
     * Function to test deleteGroup()
     */
    function testDeleteGroup( )
    { 
        $customGrouptitle = 'My Custom Group';
        $groupParams = array(
                             'title'      => $customGrouptitle,
                             'name'       => 'my_custom_group',
                             'style'      => 'Tab',
                             'extends'    => 'Individual',
                             'is_active'  => 1,
                             'version'    => 3
                             );
        
        $customGroup = Custom::createGroup( $groupParams );
        
        $customGroupId = $customGroup->id;
        
        //get the custom group title
        $dbCustomGroupTitle = $this->assertDBNotNull( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'title', 'id',
                                                      'Database check for custom group record.' );
        //check for group title
        $this->assertEquals( $customGrouptitle, $dbCustomGroupTitle );
        
        require_once 'CRM/Core/BAO/CustomGroup.php';
        //delete the group
        $isDelete = CRM_Core_BAO_CustomGroup::deleteGroup( $customGroup );
        
        //check for delete
        $this->assertEquals( true, $isDelete );
        
        //check the DB
        $this->assertDBNull( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'title', 'id', 
                             'Database check for custom group record.' );
    }
    
    /**
     * Function to test createTable()
     */
    function testCreateTable( )
    { 
        $customGrouptitle = 'My Custom Group';
        $groupParams = array(
                             'title'      => $customGrouptitle,
                             'name'       => 'my_custom_group',
                             'style'      => 'Tab',
                             'extends'    => 'Individual',
                             'is_active'  => 1,
                             'version'    => 3
                             );
        
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroupBAO = new CRM_Core_BAO_CustomGroup();
        $customGroupBAO->copyValues( $groupParams );
        $customGroup = $customGroupBAO->save();
        $tableName   = 'civicrm_value_test_group_'.$customGroup->id;
        $customGroup->table_name = $tableName;
        $customGroup = $customGroupBAO->save();
        $customTable = CRM_Core_BAO_CustomGroup::createTable( $customGroup );
        $customGroupId = $customGroup->id;
        
        //check db for custom group.
        $dbCustomGroupTitle = $this->assertDBNotNull( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'title', 'id',
                                                      'Database check for custom group record.' );
        //check for custom group table name
        $this->assertDBCompareValue(  'CRM_Core_DAO_CustomGroup', $customGroupId, 'table_name', 'id',
                                      $tableName,  'Database check for custom group table name.' );
        
        //check for group title
        $this->assertEquals( $customGrouptitle, $dbCustomGroupTitle );
        
        //cleanup DB by deleting customGroup
        Custom::deleteGroup( $customGroup );
    }
    
    /**
     * Function to test checkCustomField()
     */
    function testCheckCustomField()
    {
        $customGroupTitle = 'My Custom Group';
        $groupParams = array(
                             'title'            => $customGroupTitle,
                             'name'             => 'my_custom_group',
                             'extends'          => 'Individual',
                             'help_pre'         => 'Custom Group Help Pre',
                             'help_post'        => 'Custom Group Help Post',
                             'is_active'        => 1,
                             'collapse_display' => 1,
                             'version'          => 3
                             );
        
        $customGroup = Custom::createGroup( $groupParams );
        $this->assertNotNull($customGroup->id,'pre-requisite group not created successfully');
        $customGroupId = $customGroup->id;

        $customFieldLabel = 'Test Custom Field';
        $fieldParams = array(
                             'custom_group_id' => $customGroupId,
                             'label'           => $customFieldLabel,
                             'html_type'       => 'Text',
                             'data_type'       => 'String',
                             'is_required'     => 1,
                             'is_searchable'   => 0,
                             'is_active'       => 1,
                             'version'         => 3
                             );
       
        $customField = Custom::createField( $fieldParams );
        $this->assertNotNull($customField->id,'pre-requisite field not created successfully');
        
        $customFieldId = $customField->id;
         
        //check db for custom group
        $dbCustomGroupTitle = $this->assertDBNotNull( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'title', 'id',
                                                      'Database check for custom group record.' );
        $this->assertEquals( $customGroupTitle, $dbCustomGroupTitle );
        
        //check db for custom field
        $dbCustomFieldLabel = $this->assertDBNotNull( 'CRM_Core_DAO_CustomField', $customFieldId, 'label', 'id',
                                                      'Database check for custom field record.' );
        $this->assertEquals( $customFieldLabel, $dbCustomFieldLabel );
        
        //check the custom field type.
        $params = array ( 'Individual' );
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $usedFor = CRM_Core_BAO_CustomGroup::checkCustomField( $customFieldId, $params );
        $this->assertEquals( false, $usedFor );
        
        $params = array( 'Contribution', 'Membership', 'Participant' );
        $usedFor = CRM_Core_BAO_CustomGroup::checkCustomField( $customFieldId, $params );
        $this->assertEquals( true, $usedFor );
        
        //cleanup DB by deleting customGroup
        Custom::deleteField( $customField ); 
        Custom::deleteGroup( $customGroup );
    }
    
    /**
     * Function to test getActiveGroups() with Invalid Params()
     */
    function testGetActiveGroupsWithInvalidParams( )
    {   
        $contactId = Contact::createIndividual( );      
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $activeGroups =  CRM_Core_BAO_CustomGroup::getActiveGroups('ABC', 'civicrm/contact/view/cd', $contactId );
        $this->assertEquals( empty($activeGroups) ,true, 'Check that Emprt params are retreived');
    }
    
    function testGetActiveGroups()
    {
        $contactId = Contact::createIndividual( );
        $customGrouptitle = 'Test Custom Group';
        $groupParams = array(
                             'title'      => $customGrouptitle,
                             'name'       => 'test_custom_group',
                             'style'      => 'Tab',
                             'extends'    => 'Individual',
                             'weight'     => 10,
                             'is_active'  => 1,
                             'version'    => 3
                             );

        
        $customGroup = Custom::createGroup( $groupParams );
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $activeGroup = CRM_Core_BAO_CustomGroup::getActiveGroups('Individual', 'civicrm/contact/view/cd', $contactId );
        foreach ( $activeGroup as $key => $value ) {
            if ( $value['id'] == $customGroup->id ) {
                $this->assertEquals( $value['path'] ,'civicrm/contact/view/cd' );
                $this->assertEquals( $value['title'] , $customGrouptitle );
                $query = 'reset=1&gid='.$customGroup->id.'&cid='.$contactId;
                $this->assertEquals( $value['query'] , $query );
            } 
        } 
        
        Custom::deleteGroup( $customGroup );
        Contact::delete( $contactId );

    }
    
    /**
     * Function to test create()
     */
    function testCreate( )
    {        
        $params = array( 'title'            => 'Test_Group_1',
                         'name'             => 'test_group_1',
                         'extends'          => array( 0 => 'Individual', 1 => array()),
                         'weight'           => 4,
                         'collapse_display' => 1,
                         'style'            => 'Inline',
                         'help_pre'         => 'This is Pre Help For Test Group 1',
                         'help_post'        => 'This is Post Help For Test Group 1',
                         'is_active'        => 1,
                         'version'          => 3
                         );
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroup =  CRM_Core_BAO_CustomGroup::create( $params );
        
        $dbCustomGroupTitle = $this->assertDBNotNull( 'CRM_Core_DAO_CustomGroup', $customGroup->id, 'title', 'id',
                                                      'Database check for custom group record.' );
        $this->assertEquals( $params['title'], $dbCustomGroupTitle );
        Custom::deleteGroup( $customGroup );
    } 
    
    
    /**
     * Function to test isGroupEmpty()
     */
    function testIsGroupEmpty( )
    {
        $customGrouptitle = 'Test Custom Group';
        $groupParams = array(
                             'title'      => $customGrouptitle,
                             'name'       => 'test_custom_group',
                             'style'      => 'Tab',
                             'extends'    => 'Individual',
                             'weight'     => 10,
                             'is_active'  => 1,
                             'version'    => 3
                             );
        
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroup = Custom::createGroup( $groupParams );
        $customGroupId = $customGroup->id;
        $isEmptyGroup =  CRM_Core_BAO_CustomGroup::isGroupEmpty( $customGroupId );
        
        $this->assertEquals( $isEmptyGroup , true,  'Check that custom Group is Empty.' );
        Custom::deleteGroup( $customGroup );
    }  
    
    /**
     * Function to test getGroupTitles() with Invalid Params()
     */
    function testgetGroupTitlesWithInvalidParams( )
    {
        $params = array ( 99 );
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $groupTitles =  CRM_Core_BAO_CustomGroup::getGroupTitles( $params );
        $this->assertEquals( empty($groupTitles) , 'Check that no titles are recieved'  );
    }
    
    /**
     * Function to test getGroupTitles()
     */
    function testgetGroupTitles( )
    {
        $customGrouptitle = 'Test Custom Group';
        $groupParams = array(
                             'title'      => $customGrouptitle,
                             'name'       => 'test_custom_group',
                             'style'      => 'Tab',
                             'extends'    => 'Individual',
                             'weight'     => 10,
                             'is_active'  => 1,
                             'version'    => 3
                             );
        
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $customGroup = Custom::createGroup( $groupParams );
         
        $customGroupId = $customGroup->id;
        
        $customFieldLabel = 'Test Custom Field';
        $fieldParams = array(
                             'custom_group_id' => $customGroupId,
                             'label'           => $customFieldLabel,
                             'html_type'       => 'Text',
                             'data_type'       => 'String',
                             'is_required'     => 1,
                             'is_searchable'   => 0,
                             'is_active'       => 1,
                             'version'         => 3
                             );
        
        $customField = Custom::createField( $fieldParams );
        $customFieldId = $customField->id;
        
        $params = array( $customFieldId );
        
        $groupTitles =  CRM_Core_BAO_CustomGroup::getGroupTitles( $params );
        
        $this->assertEquals( $groupTitles[$customFieldId]['groupTitle'] ,'Test Custom Group' ,  'Check Group Title.' );
        Custom::deleteGroup( $customGroup );
    }
}
