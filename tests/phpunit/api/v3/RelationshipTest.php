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


require_once 'api/v3/Relationship.php';
require_once 'api/v3/CustomGroup.php';
require_once 'CiviTest/CiviUnitTestCase.php';
require_once ('api/api.php');

/**
 * Class contains api test cases for "civicrm_relationship"
 *
 */
class api_v3_RelationshipTest extends CiviUnitTestCase 
{
    protected $_apiversion;
    protected $_cId_a;
    protected $_cId_b;
    protected $_relTypeID;
    protected $_ids  = array( );
    protected $_customGroupId = null;
    protected $_customFieldId = null;
    protected $_params;  
    protected $_entity;     
    function get_info( )
    {
        return array(
                     'name'        => 'Relationship Create',
                     'description' => 'Test all Relationship Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    } 
    
    function setUp() 
    {
        parent::setUp();
        $this->_apiversion = 3;      
        $this->_cId_a  = $this->individualCreate(null);
        $this->_cId_b  = $this->organizationCreate(null );
        $this->_entity = 'relationship';
        //Create a relationship type
        $relTypeParams = array(
                               'name_a_b'       => 'Relation 1 for delete',
                               'name_b_a'       => 'Relation 2 for delete',
                               'description'    => 'Testing relationship type',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'is_reserved'    => 1,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->_relTypeID = $this->relationshipTypeCreate($relTypeParams );
        $this->_params =  array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',
                         'is_active'            => 1,
                          'version'							=> $this->_apiversion,
                         );        
    }

    function tearDown() 
    {
        $this->relationshipTypeDelete( $this->_relTypeID );
        $this->contactDelete( $this->_cId_a );
        $this->contactDelete( $this->_cId_b );
    }
    
///////////////// civicrm_relationship_create methods

    /**
     * check with empty array
     */
    function testRelationshipCreateEmpty( )
    {
        $params = array( 'version'  => $this->_apiversion);
        $result =& civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    /**
     * check with No array
     */
    function testRelationshipCreateParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    /**
     * check if required fields are not passed
     */
    function testRelationshipCreateWithoutRequired( )
    {
        $params = array(
                        'start_date' => array('d'=>'10','M'=>'1','Y'=>'2008'),
                        'end_date'   => array('d'=>'10','M'=>'1','Y'=>'2009'),
                        'is_active'  => 1
                        );
        
        $result =& civicrm_api('relationship', 'create', $params);
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    /**
     * check with incorrect required fields
     */
    function testRelationshipCreateWithIncorrectData( )
    {

        $params = array(
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => 'Breaking Relationship'
                        );

        $result =& civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );

        //contact id is not an integer
        $params = array( 'contact_id_a'         => 'invalid',
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                         'is_active'            => 1
                         );
        $result =& civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );

        //contact id does not exists
        $params['contact_id_a'] = 999;
        $result =& civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );

        //invalid date
        $params['contact_id_a'] = $this->_cId_a;
        $params['start_date']   = array('d'=>'1','M'=>'1');
        $result =& civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );
    }
   
    /**
     * check relationship creation with invalid Relationship 
     */
    function testRelationshipCreatInvalidRelationship( )
    {
        // both the contact of type Individual
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_a,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                         'is_active'            => 1
                         );
        
        $result = & civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );
        
        // both the contact of type Organization
        $params = array( 'contact_id_a'         => $this->_cId_b,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                         'is_active'            => 1
                         );
        
        $result = & civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );

    } 
    
    /**
     * check relationship already exists
     */
    function testRelationshipCreateAlreadyExists( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',                         'end_date'             => null,
                         'is_active'            => 1,
                         'version'							=> $this->_apiversion,
                         );
        $relationship = & civicrm_api('relationship','create',$params );
        
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',
                         'is_active'            => 1,
                         'version'							=> $this->_apiversion,
                         );
        $result = & civicrm_api('relationship','create',$params );

        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Relationship already exists' ); 
        
        $params['id'] = $relationship['result']['id'] ; 
        $result = & civicrm_api('relationship','delete',$params );
    } 
    /**
     * check relationship already exists
     */
    function testRelationshipCreateUpdateAlreadyExists( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',
                         'end_date'             => null,
                         'is_active'            => 1,
                         'version'							=> $this->_apiversion,
                         );
        $relationship = civicrm_api('relationship','create',$params );
        
        $params = array( 'id'         => $relationship['id'],
                         'is_active'            => 0,
                         'version'							=> $this->_apiversion,
                         );
        $result = civicrm_api('relationship','create',$params );

        $this->assertAPISuccess($result, 'in line ' . __LINE__);
        $result = civicrm_api('relationship','get',$params );
        $this->assertEquals(0,$result['values'][$result['id']]['is_active'], 'in line ' . __LINE__);
        $params['id'] = $relationship['result']['id'] ; 
        $result = & civicrm_api('relationship','delete',$params );
    } 

    /**
     * check relationship creation
     */
    function testRelationshipCreate( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2010-10-30',
                         'end_date'             => '2010-12-30',
                         'is_active'            => 1,
                         'note'                 => 'note',
                         'version'				=> $this->_apiversion,
                         );
        
        $result = & civicrm_api('relationship','create', $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'in line ' . __LINE__ );
        $this->assertNotNull( $result['id'],'in line ' . __LINE__ );   
        $relationParams = array(
                                'id' => $result['id'],
                                );

        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_Relationship', $result['id'], $relationParams ); 
        $result = &  civicrm_api('relationship','get', array('version' => 3, 'id' => $result['id']));
        $values = $result['values'][$result['id']];
        foreach($params as $key => $value){
          if($key == 'version' || $key == 'note')continue;
          $this->assertEquals($value, $values[$key],$key . " doesn't match " . print_r($values,true) . 'in line' . __LINE__);        
          
        }
        $params['id'] = $result['values']['id'] ; 
        civicrm_api('relationship','delete',$params );
    }
    /**
     * check relationship creation
     */
    function testRelationshipCreateEmptyEndDate( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2010-10-30',
                         'end_date'             => '',
                         'is_active'            => 1,
                         'note'                 => 'note',
                         'version'				=> $this->_apiversion,
                         );
        
        $result = & civicrm_api('relationship','create', $params );

        $this->assertEquals( 0, $result['is_error'], 'in line ' . __LINE__ );
        $this->assertNotNull( $result['id'],'in line ' . __LINE__ );   
        $relationParams = array(
                                'id' => $result['id'],
                                );

        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_Relationship', $result['id'], $relationParams ); 
        $result = &  civicrm_api('relationship','get', array('version' => 3, 'id' => $result['id']));
        $values = $result['values'][$result['id']];
        foreach($params as $key => $value){
          if($key == 'version' || $key == 'note')continue;
          $this->assertEquals($value, $values[$key],$key . " doesn't match " . print_r($values,true) . 'in line' . __LINE__);        
          
        }
        $params['id'] = $result['values']['id'] ; 
        civicrm_api('relationship','delete',$params );
    } 

    
    /**
     * check relationship creation with custom data
     */
    function testRelationshipCreateWithCustomData( )
    {         
        $customGroup = $this->createCustomGroup( );
        $this->_customGroupId = $customGroup['id'];
        $this->_ids  = $this->createCustomField( );     
        //few custom Values for comparing
        $custom_params = array("custom_{$this->_ids[0]}" => 'Hello! this is custom data for relationship',
                               "custom_{$this->_ids[1]}" => 'Y',
                               "custom_{$this->_ids[2]}" => '2009-07-11 00:00:00',
                               "custom_{$this->_ids[3]}" => 'http://example.com',
                               );
        
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',
                         'is_active'            => 1,
                          'version'							=> $this->_apiversion,
                         );
        $params = array_merge( $params, $custom_params );
        $result = & civicrm_api('relationship','create',$params );
        
        $this->assertNotNull( $result['id'] );   
        $relationParams = array(
                                'id' => $result['id'],
                                );
        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_Relationship', $result['id'], $relationParams ); 
        
        $params['id'] = $result['id'] ; 
        $result = & civicrm_api('relationship','delete',$params );
        $this->relationshipTypeDelete( $this->_relTypeID ); 
    }
      /**
     * check with complete array + custom field 
     * Note that the test is written on purpose without any
     * variables specific to participant so it can be replicated into other entities
     * and / or moved to the automated test suite
     */
    function testGetWithCustom()
    {
        $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
        
        $params = $this->_params;
        $params['custom_'.$ids['custom_field_id']]  =  "custom string";
 
        $result = civicrm_api($this->_entity,'create', $params);
        $this->assertEquals($result['id'],$result['values'][$result['id']]['id']);

        $this->assertNotEquals( $result['is_error'],1 ,$result['error_message'] . ' in line ' . __LINE__);
        $getParams = array('version' =>3, 'id' => $result['id']);
        $check = civicrm_api($this->_entity,'get',$getParams);
        $this->documentMe($getParams, $check, __FUNCTION__, __FILE__);
        $this->assertEquals("custom string", $check['values'][$check['id']]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);
   
        $this->customFieldDelete($ids['custom_field_id']);
        $this->customGroupDelete($ids['custom_group_id']);      

    }
    function createCustomGroup( )
    {
        $params = array(
                        'title'            => 'Test Custom Group',
                        'extends'          => array ( 'Relationship' ),
                        'weight'           => 5,
                        'style'            => 'Inline',
                        'is_active'        => 1,
                        'max_multiple'     => 0,
                        'version'							=> $this->_apiversion,
                        );
        $customGroup =& civicrm_api('custom_group','create', $params);
        return null;
    }

    function createCustomField( )
    {
        $ids = array( );
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'label'           => 'Enter text about relationship',
                        'html_type'       => 'Text',
                        'data_type'       => 'String',
                        'default_value'   => 'xyz',
                        'weight'          => 1,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                        'version' => $this->_apiversion,
                         );
        

        $result = civicrm_api('CustomField','create',$params );
  
        $customField = null;
        $ids[] = $customField['result']['customFieldId'];
        
        $optionValue[] = array (
                                'label'     => 'Red',
                                'value'     => 'R',
                                'weight'    => 1,
                                'is_active' => 1
                                );
        $optionValue[] = array (
                                'label'     => 'Yellow',
                                'value'     => 'Y',
                                'weight'    => 2,
                                'is_active' => 1
                                );
        $optionValue[] = array (
                                'label'     => 'Green',
                                'value'     => 'G',
                                'weight'    => 3,
                                'is_active' => 1
                                );
        
        $params = array(
                        'label'           => 'Pick Color',
                        'html_type'       => 'Select',
                        'data_type'       => 'String',
                        'weight'          => 2,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                        'option_values'   => $optionValue,
                        'custom_group_id' => $this->_customGroupId,
                        );
        
        $customField  =& civicrm_api('custom_field','create',  $params );
        
        $ids[] = $customField['result']['customFieldId'];
        
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'name'            => 'test_date',
                        'label'           => 'test_date',
                        'html_type'       => 'Select Date',
                        'data_type'       => 'Date',
                        'default_value'   => '20090711',
                        'weight'          => 3,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField  =& civicrm_api('custom_field','create',  $params );			
        
        $ids[] = $customField['result']['customFieldId'];
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'name'            => 'test_link',
                        'label'           => 'test_link',
                        'html_type'       => 'Link',
                        'data_type'       => 'Link',
                        'default_value'   => 'http://civicrm.org',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField  =& civicrm_api('custom_field','create',  $params );
        $ids[] = $customField['result']['customFieldId'];
        return $ids;
    }

///////////////// civicrm_relationship_delete methods

    /**
     * check with empty array
     */
    function testRelationshipDeleteEmpty( )
    {
        $params = array('version' => $this->_apiversion );
        $result =& civicrm_api('relationship','delete',$params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: id' );
    }
    
    /**
     * check with No array
     */
    
    function testRelationshipDeleteParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_api('relationship','delete',$params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }
    
    /**
     * check if required fields are not passed
     */
    function testRelationshipDeleteWithoutRequired( )
    {
        $params = array(
                         'start_date'           => '2008-12-20',
                         'end_date'           => '2009-12-20',
                        'is_active'  => 1
                        );
        
        $result =& civicrm_api('relationship','delete',$params ); 
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: version, id' );
    }
    
    /**
     * check with incorrect required fields
     */
    function testRelationshipDeleteWithIncorrectData( )
    {
        $params = array(
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => 'Breaking Relationship',
                        'version'							 => $this->_apiversion,
                        );
        
        $result =& civicrm_api('relationship','delete',$params );
        $this->assertEquals( $result['is_error'], 1,'in line ' . __LINE__  );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: id','in line ' . __LINE__ );

        $params['id'] = "Invalid";
        $result =& civicrm_api('relationship','delete',$params );
        $this->assertEquals( $result['is_error'], 1,'in line ' . __LINE__  );
        $this->assertEquals( $result['error_message'], 'Invalid value for relationship ID','in line ' . __LINE__  ); 
    }

    /**
     * check relationship creation
     */
    function testRelationshipDelete( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',
                         'is_active'            => 1,
                         'version'							=> $this->_apiversion,
                         );
        
        $result = & civicrm_api('relationship','create',$params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertNotNull( $result['id'] );

        //Delete relationship
        $params = array();
        $params['id']= $result['id'];
        
        $result = & civicrm_api('relationship','delete',$params );
        $this->relationshipTypeDelete( $this->_relTypeID ); 
    }
    
///////////////// civicrm_relationship_update methods

    /**
     * check with empty array
     */
    function testRelationshipUpdateEmpty( )
    {
        $params = array('version' => 3 );
        $result =& civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Mandatory key(s) missing from params array: contact_id_a, contact_id_b, relationship_type_id', $result['error_message'], 'In line ' . __LINE__ );
    }
    
    /**
     * check with No array
     */
    function testRelationshipUpdateParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_api('relationship','create',$params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Input variable `params` is not an array', $result['error_message'], 'In line ' . __LINE__ );
    }

    /**
     * check if required fields are not passed
     */

    /**
     * check relationship update
     */
    function testRelationshipUpdate( )
    {
        $relParams     = array(
                               'contact_id_a'         => $this->_cId_a,
                               'contact_id_b'         => $this->_cId_b,
                               'relationship_type_id' => $this->_relTypeID,
                               'start_date'           => '20081214',
                               'end_date'             => '20091214',
                               'is_active'            => 1,
                               'version'              => $this->_apiversion,
                               );

        $result = & civicrm_api('relationship','create',$relParams );

        $this->assertNotNull( $result['id'], 'In line ' . __LINE__ );  
        $this->_relationID = $result['id'];

        $params = array(
                        'relationship_id'      => $this->_relationID,
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => $this->_relTypeID,
                        'start_date'           => '20081214',
                        'end_date'             => '20091214',                       'is_active'            => 0,
                        'version'							 => $this->_apiversion,
                        );
        
        $result = & civicrm_api('relationship','create',$params );
        
        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__  );
        $this->assertEquals( $result['error_message'], 'Relationship already exists', 'In line ' . __LINE__  );

        //delete created relationship
        $params = array('id'       => $this->_relationID,
                        'version'  => $this->_apiversion);
        
        $result = & civicrm_api('relationship','delete',$params );
        $this->assertEquals( $result['is_error'], 0 ,'in line ' .__LINE__);
        
        //delete created relationship type        
        $this->relationshipTypeDelete( $this->_relTypeID ); 
    }

    /**
     * check with valid params array.
     */
    function testRelationshipsGet( )
    {
        $relParams = array(
                           'contact_id_a'         => $this->_cId_a,
                           'contact_id_b'         => $this->_cId_b,
                           'relationship_type_id' => $this->_relTypeID,
                           'start_date'           => '2011-01-01',
                           'end_date'             => '2013-01-01',
                           'is_active'            => 1,
                           'version'              => $this->_apiversion,
                           );

        $result = civicrm_api('relationship','create',$relParams );
        
        //get relationship
        $params = array( 'contact_id' => $this->_cId_b ,
                          'version'     => $this->_apiversion);
        $result = civicrm_api('relationship','get',$params );
 
        $this->assertAPISuccess($result, 'in line ' .__LINE__ );
        $this->assertEquals( $result['count'], 1,'in line ' .__LINE__ );
        $params = array( 'contact_id_a' => $this->_cId_a ,
                          'version'     => $this->_apiversion); 
        $result = civicrm_api('relationship','get',$params );     
        $this->assertAPISuccess($result, 'in line ' .__LINE__ );
        $this->assertEquals( $result['count'], 1,'in line ' .__LINE__ );
        // contact_id_a is wrong so should be no matches
        $params = array( 'contact_id_a' => $this->_cId_b ,
                          'version'     => $this->_apiversion);  
        $result = civicrm_api('relationship','get',$params );    
        $this->assertAPISuccess($result, 'in line ' .__LINE__ );
        $this->assertEquals( $result['count'], 0,'in line ' .__LINE__ );
     
        
    }
    /**
     * check with valid params array.
     * (The get function will behave differently without 'contact_id' passed
     */
    function testRelationshipsGetGeneric( )
    {
        $relParams = array(
                           'contact_id_a'         => $this->_cId_a,
                           'contact_id_b'         => $this->_cId_b,
                           'relationship_type_id' => $this->_relTypeID,
                           'start_date'           => '2011-01-01',
                           'end_date'             => '2013-01-01',
                           'is_active'            => 1,
                           'version'              => $this->_apiversion,
                           );

        $result = civicrm_api('relationship','create',$relParams );
        
        //get relationship
        $params = array( 'contact_id_b' => $this->_cId_b ,
                          'version'     => $this->_apiversion);
        $result = civicrm_api('relationship','get',$params );
        $this->assertEquals( $result['is_error'], 0,'in line ' .__LINE__ );
    }
   ///////////////// civicrm_relationship_type_add methods
    
   /**
    * check with invalid relationshipType Id
    */
    function testRelationshipTypeAddInvalidId( )
    {
        $relTypeParams = array(
                               'id'             => 'invalid',
                               'name_a_b'       => 'Relation 1 for delete',
                               'name_b_a'       => 'Relation 2 for delete',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'version'				=>$this->_apiversion,
                               );
        $result =& civicrm_api('relationship_type','create', $relTypeParams );
        $this->assertEquals( $result['is_error'], 1 ,'in line ' .__LINE__);
        $this->assertEquals( $result['error_message'], 'Invalid value for relationship type ID', 'in line ' .__LINE__);
    } 

    ///////////////// civicrm_get_relationships
    
    /**
    * check with invalid data
    */
    function testGetRelationshipInvalidData( )
    {
        $contact_a = array( 'contact_id' => $this->_cId_a );
        $contact_b = array( 'contact_id' => $this->_cId_b );
        
        //no relationship has been created
        $result =& civicrm_api('relationship','get',$contact_a, $contact_b, null , 'asc' );
        $this->assertEquals( $result['is_error'], 1 );
    } 
    
    
    /**
     * check with valid data with contact_b
     */
    function testGetRelationshipWithContactB( )
    {
        $relParams = array(
                           'contact_id_a'         => $this->_cId_a,
                           'contact_id_b'         => $this->_cId_b,
                           'relationship_type_id' => $this->_relTypeID,
                           'start_date'           => '2011-01-01',
                           'end_date'             => '2013-01-01',
                           'is_active'            => 1,
                           'version'			  => $this->_apiversion,
                           );

        $relationship = & civicrm_api('relationship','create',$relParams );

        $contacts = array( 'contact_id' => $this->_cId_a ,
                           'version'	  => $this->_apiversion );

        $result =& civicrm_api('relationship','get',$contacts );
        $this->assertEquals( $result['is_error'], 0 ,'in line ' .__LINE__);
        $this->assertGreaterThan( 0,  $result['count'],'in line ' .__LINE__);
        $params = array('id' => $relationship['id'] ,
                        'version' => $this->_apiversion,);
        $result = & civicrm_api('relationship','delete',$params );
        $this->relationshipTypeDelete( $relTypeID );
    }

    /**
    * check with valid data with relationshipTypes
    */
    function testGetRelationshipWithRelTypes( )
    {
        $relParams = array(
                           'contact_id_a'         => $this->_cId_a,
                           'contact_id_b'         => $this->_cId_b,
                           'relationship_type_id' => $this->_relTypeID,
                           'start_date'           => '2011-01-01',
                           'end_date'             => '2013-01-01',
                           'is_active'            => 1,
                           'version'			  => $this->_apiversion,
                           );

        $relationship = & civicrm_api('relationship','create',$relParams );
        
        $contact_a = array( 'contact_id' => $this->_cId_a,
                            'version'      => $this->_apiversion, );

        $result =& civicrm_api('relationship','get',$contact_a);

        $this->assertEquals( $result['is_error'], 0,'in line ' .__LINE__ );

        $params = array('id' => $relationship['result']['id'],
                        'version'		=>$this->_apiversion,) ;
        $result = & civicrm_api('relationship','delete',$params );
        $this->relationshipTypeDelete( $relTypeID );
    } 

}
 
?> 
