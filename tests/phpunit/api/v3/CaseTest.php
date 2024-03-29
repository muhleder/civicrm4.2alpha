<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File for the TestCase class
 *
 *  (PHP 5)
 *  
 *   @author Walt Haas <walt@dharmatech.org> (801) 534-1262
 *   @copyright Copyright CiviCRM LLC (C) 2009
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id: ActivityTest.php 31254 2010-12-15 10:09:29Z eileen $
 *   @package   CiviCRM
 *
 *   This file is part of CiviCRM
 *
 *   CiviCRM is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Affero General Public License
 *   as published by the Free Software Foundation; either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   CiviCRM is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public
 *   License along with this program.  If not, see
 *   <http://www.gnu.org/licenses/>.
 */

/**
 *  Include class definitions
 */
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/Case.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
require_once 'Utils.php';

/**
 *  Test APIv3 civicrm_case_* functions
 *
 *  @package   CiviCRM
 */
class api_v3_CaseTest extends CiviUnitTestCase
{   protected $_params;
    protected $_entity;
    protected $_apiversion;
    protected $followup_activity_type_value;
    protected $case_activity_type_value;
    protected $caseStatusGroup;
    protected $caseTypeGroup;
    protected $optionValues;
    /**
     *  Test setup for every test
     *
     *  Connect to the database, truncate the tables that will be used
     *  and redirect stdin to a temporary file
     */
    public function setUp()
    {
        $this->_apiversion =3;
        $this->_entity = 'case';
      
        parent::setUp();
        // CRM-9404 - set-up is a bit cumbersome but had to put something in place to set up activity types & case types
        //. Using XML was causing breakage as id numbers were changing over time
        // & was really hard to troubleshoot as involved truncating option_value table to mitigate this & not leaving DB in a 
        // state where tests could run afterwards without re-loading.
        $this->caseStatusGroup = civicrm_api('option_group', 'get', array('version' => API_LATEST_VERSION,'name' => 'case_status', 'format.only_id' => 1));
        $this->caseTypeGroup = civicrm_api('option_group', 'get', array('version' => API_LATEST_VERSION,'name' => 'case_type', 'format.only_id' => 1));
        $caseTypes = civicrm_api('option_value', 'Create', array('version' => API_LATEST_VERSION, 
        																						'option_group_id' => $this->caseTypeGroup , 
        																						'name' => 'housing_support',
                    																'label'=>"Housing Support",  
                                                    'sequential' => 1, 
                                                    'description' => 'Help homeless individuals obtain temporary and long-term housing',                                              
                                                     )); 
        
        $this->case_activity_type_value = $caseTypes['values'][0]['value'];
        $this->optionValues[] =$caseTypes['id'];
        $optionValues = array('Medical evaluation' => 'Medical evaluation',
                              'Mental health evaluation'=>"Mental health evaluation",
                              'Secure temporary housing' => 'Secure temporary housing' ,
                              'Long-term housing plan' => 'Long-term housing plan' ,
                              'ADC referral'         => 'ADC referral' ,
                              'Income and benefits stabilization' => 'Income and benefits stabilization' ,       );
        foreach ($optionValues as $name => $label) {
                 $activityTypes = civicrm_api('option_value', 'Create', array('version' => API_LATEST_VERSION, 
        																						'option_group_id' => 2 , 
        																						'name' => $name,
                    																'label'=> $label, 
                                                    'component_id' => 7, 
                                                     )); 
        $this->optionValues[] = $activityTypes['id'];// store for cleanup
        }
        $tablesToTruncate = array( 'civicrm_activity',
                                   'civicrm_contact',
                                   'civicrm_custom_group',
                                   'civicrm_custom_field',
                                   'civicrm_case',
                                   'civicrm_case_contact',
                                   'civicrm_case_activity',
                                   'civicrm_activity_target',
                                   'civicrm_activity_assignment',
                                   'civicrm_relationship',
                                   'civicrm_relationship_type',
                                   );

        $this->quickCleanup( $tablesToTruncate );

        $activityTypes = civicrm_api('option_value', 'get', array('version' => API_LATEST_VERSION, 'option_group_id' => 2, 
        																						'name' => 'Follow Up',
                    																'label' =>  'Follow Up',
                                                    'sequential' => 1 )); 
        $this->followup_activity_type_value = $activityTypes['values'][0]['value'];
        //  Insert a row in civicrm_contact creating contact 17
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/contact_17.xml') );
 
 

   


        //Create relationship types
        $relTypeParams = array(
                               'name_a_b'       => 'Case Coordinator is',
                               'label_a_b'      => 'Case Coordinator is',
                               'name_b_a'       => 'Case Coordinator',
                               'label_b_a'      => 'Case Coordinator',
                               'description'    => 'Case Coordinator',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  

        $relTypeParams = array(
                               'name_a_b'       => 'Homeless Services Coordinator is',
                               'label_a_b'      => 'Homeless Services Coordinator is',
                               'name_b_a'       => 'Homeless Services Coordinator',
                               'label_b_a'      => 'Homeless Services Coordinator',
                               'description'    => 'Homeless Services Coordinator',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  

        $relTypeParams = array(
                               'name_a_b'       => 'Health Services Coordinator is',
                               'label_a_b'      => 'Health Services Coordinator is',
                               'name_b_a'       => 'Health Services Coordinator',
                               'label_b_a'      => 'Health Services Coordinator',
                               'description'    => 'Health Services Coordinator',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  

        $relTypeParams = array(
                               'name_a_b'       => 'Senior Services Coordinator is',
                               'label_a_b'      => 'Senior Services Coordinator is',
                               'name_b_a'       => 'Senior Services Coordinator',
                               'label_b_a'      => 'Senior Services Coordinator',
                               'description'    => 'Senior Services Coordinator',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  

        $relTypeParams = array(
                               'name_a_b'       => 'Benefits Specialist is',
                               'label_a_b'      => 'Benefits Specialist is',
                               'name_b_a'       => 'Benefits Specialist',
                               'label_b_a'      => 'Benefits Specialist',
                               'description'    => 'Benefits Specialist',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  
 
        // enable the default custom templates for the case type xml files
        $this->customDirectories( array( 'template_path' => TRUE ) );

        // case is not enabled by default
        require_once 'CRM/Core/BAO/ConfigSetting.php';
        $enableResult = CRM_Core_BAO_ConfigSetting::enableComponent( 'CiviCase' );
        $this->assertTrue( $enableResult, 'Cannot enable CiviCase in line ' . __LINE__);

        $this->_params = array( 
                'case_type_id' => $this->case_activity_type_value,
                'subject' => 'Test case',
                'contact_id' => 17,
                'version' => $this->_apiversion);

        // create a logged in USER since the code references it for source_contact_id
        $this->createLoggedInUser( );
        $session = CRM_Core_Session::singleton( );
        $this->_loggedInUser = $session->get( 'userID' );
        /// note that activityType options are cached by the FULL set of options you pass in
        // ie. because Activity api includes campaign in it's call cache is not flushed unless
        // included in this call. Also note flush function doesn't work on this property as it sets to null not empty array
        CRM_Core_PseudoConstant::activityType( true,  true, true, 'name', true);
       
    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    function tearDown()
    {
      foreach ($this->optionValues as $id) {
          civicrm_api('option_value','delete',array('version' => API_LATEST_VERSION, 'id' => $id));
      }
        $tablesToTruncate = array( 'civicrm_contact', 
                                   'civicrm_activity',
                                   'civicrm_case',
                                   'civicrm_case_contact',
                                   'civicrm_case_activity',
                                   'civicrm_activity_target',
                                   'civicrm_activity_assignment',
                                   'civicrm_relationship',
                                   'civicrm_relationship_type',
                                   );
        $this->quickCleanup( $tablesToTruncate, true );

        $this->customDirectories( array( 'template_path' => FALSE ) );
    }

    
    /**
     * check with empty array
     */
    function testCaseCreateEmpty( )
    {
        $params = array('version' => $this->_apiversion );
        $result = & civicrm_api('case','create',$params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check if required fields are not passed
     */
    function testCaseCreateWithoutRequired( )
    {
        $params = array(
                        'subject'             => 'this case should fail',
                        'case_type_id' => 1,
                        'version' => $this->_apiversion
                        );
        
        $result = & civicrm_api('case','create',$params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }


    /**
     *  Test civicrm_case_create() with valid parameters
     */
    function testCaseCreate( )
    {
 
        $params = $this->_params;
        $result = civicrm_api('case','create', $params );
        $this->assertAPISuccess($result, 'in line ' . __LINE__);

        $result = civicrm_api('case','get', $params );
// TODO: There's more things we could check
        $this->assertEquals( $result['values'][$result['id']]['id'], 1,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['case_type_id'], $params['case_type_id'],'in line ' . __LINE__);
    }

    /**
     *  Test activity api create for case activities
     */
    function testCaseActivityCreate( )
    {
        // Create a case first
        $params = $this->_params;
        $result = civicrm_api('case','create', $params );
        $this->assertAPISuccess($result, 'in line ' . __LINE__);     
        $params = array( 'case_id' => 1,
                         'activity_type_id' => $this->followup_activity_type_value, // follow up
                         'subject' => 'Test followup',
                         'source_contact_id' => $this->_loggedInUser,
                         'target_contact_id' => $this->_params['contact_id'],
                         'version' => $this->_apiversion,
                       );
        $result = civicrm_api('activity','create', $params );
        $this->assertAPISuccess($result, 'in line ' . __LINE__ . print_r($params, true));
        $this->assertEquals( $result['values'][$result['id']]['activity_type_id'], $params['activity_type_id'],'in line ' . __LINE__);

        // might need this for other tests that piggyback on this one
        $this->_caseActivityId = $result['values'][$result['id']]['id'];

        // Check other DB tables populated properly - is there a better way to do this? assertDBState() requires that we know the id already.
        require_once 'CRM/Case/DAO/CaseActivity.php';
        $dao = new CRM_Case_DAO_CaseActivity( );
        $dao->case_id = 1;
        $dao->activity_id = $this->_caseActivityId;
        $this->assertEquals( $dao->find( ), 1, 'case_activity table not populated correctly in line ' . __LINE__) ;
        $dao->free();

        require_once 'CRM/Activity/DAO/ActivityTarget.php';
        $dao = new CRM_Activity_DAO_ActivityTarget( );
        $dao->activity_id = $this->_caseActivityId;
        $dao->target_contact_id = $this->_params['contact_id'];
        $this->assertEquals( $dao->find( ), 1, 'activity_target table not populated correctly in line ' . __LINE__) ;
        $dao->free();

// TODO: There's more things we could check

    }

    /**
     *  Test activity api update for case activities
     */
    function testCaseActivityUpdate( )
    {
        // Need to create the case and activity before we can update it
        $this->testCaseActivityCreate( );

        $params = array( 'activity_id' => $this->_caseActivityId,
                         'case_id' => 1,
                         'activity_type_id' => 14,
                         'source_contact_id' => $this->_loggedInUser,
                         'subject' => 'New subject',
                         'version' => $this->_apiversion,
                       );
        $result = civicrm_api('activity','create', $params );

        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['subject'], $params['subject'],'in line ' . __LINE__);
        
        // id should be one greater, since this is a new revision
        $this->assertEquals( $result['values'][$result['id']]['id'],
                             $this->_caseActivityId + 1,
                             'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['original_id'],
                             $this->_caseActivityId,
                             'in line ' . __LINE__);

        // Check revision is as expected
        $revParams = array( 'activity_id' => $this->_caseActivityId,
                            'version' => $this->_apiversion,
                          );
        $revActivity =& civicrm_api( 'activity', 'get', $revParams );
        $this->assertEquals( $revActivity['values'][$this->_caseActivityId]['is_current_revision'],
                             0, 
                             'in line ' . __LINE__);
        $this->assertEquals( $revActivity['values'][$this->_caseActivityId]['is_deleted'],
                             0, 
                             'in line ' . __LINE__);
        
//TODO: check some more things
    }
    
    function testCaseActivityUpdateCustom() {
        // Create a case first
        $params = $this->_params;
        $result =& civicrm_api('case','create', $params );

        // Create custom field group
        // Note the second parameter is Activity on purpose, not Case.
        $custom_ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__, 'ActivityTest.php');
        
        // create activity
        $params = array( 'case_id' => 1,
                         'activity_type_id' => 14, // follow up
                         'subject' => 'Test followup',
                         'source_contact_id' => $this->_loggedInUser,
                         'target_contact_id' => $this->_params['contact_id'],
                         'custom_'.$custom_ids['custom_field_id'] => "custom string",
                         'version' => $this->_apiversion,
                       );
        $result =& civicrm_api('activity','create', $params );  

        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );

        $aid = $result['values'][$result['id']]['id'];
        
        // Update activity
        $params = array( 'activity_id' => $aid,
                         'case_id' => 1,
                         'activity_type_id' => 14,
                         'source_contact_id' => $this->_loggedInUser,
                         'subject' => 'New subject',
                         'version' => $this->_apiversion,
                       );
        $revAct =& civicrm_api('activity','create', $params );

        $this->assertEquals( $revAct['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $revAct ) .' in line ' . __LINE__ );

        // Retrieve revision and check custom fields got copied
        $revParams = array( 'activity_id' => $aid + 1,
                            'version' => $this->_apiversion,
                            'return.custom_'.$custom_ids['custom_field_id'] => 1,
                          );
        $revAct =& civicrm_api( 'activity', 'get', $revParams );

        $this->assertEquals( $revAct['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $revAct ) .' in line ' . __LINE__ );
        $this->assertEquals( $revAct['values'][$aid + 1]['custom_'.$custom_ids['custom_field_id']], "custom string",
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $revAct ) .' in line ' . __LINE__ );

        $this->customFieldDelete($custom_ids['custom_field_id']);
        $this->customGroupDelete($custom_ids['custom_group_id']);  
    }
}
// -- set Emacs parameters --
// Local variables:
// mode: php;
// tab-width: 4
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
