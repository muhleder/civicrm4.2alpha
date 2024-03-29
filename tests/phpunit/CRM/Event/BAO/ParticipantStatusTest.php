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
require_once 'CRM/Event/BAO/ParticipantStatusType.php';

/**
 * Test class for CRM_Event_BAO_ParticipantStatus BAO
 *
 *  @package   CiviCRM
 */
class CRM_Event_BAO_ParticipantStatusTest extends CiviUnitTestCase 
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    /**
     *  test info
     */
    function get_info( ) 
    {
        return array(
                     'name'        => 'ParticipantStatus BAOs',
                     'description' => 'Test all Event_BAO_ParticipantStatusType methods.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    /**
     *  create() and deleteParticipantStatusType() method
     */
    function testCreateAndDelete ( ) {
        
        // create using required params
        $params = array( 'name'          => 'testStatus',
                         'label'         => 'testParticipant',
                         'class'         => 'Positive',
                         'weight'        => 13,
                         'visibility_id' => 1                );
        
        $statusType   = CRM_Event_BAO_ParticipantStatusType::create( $params );
        // Checking for participant status type id in db.
        $statusTypeId = $this->assertDBNotNull( 'CRM_Event_DAO_ParticipantStatusType', $statusType->id, 'id', 
                                                'id', 'Check DB for status type id' );
        
        CRM_Event_BAO_ParticipantStatusType::deleteParticipantStatusType( $statusType->id );
        // Checking for participant status type id after delete.
        $statusTypeId = $this->assertDBNull( 'CRM_Event_DAO_ParticipantStatusType', $statusType->id, 'id', 
                                             'id', 'Check DB for status type id' );
        
    }
    
    /**
     *  add() method (add and edit modes of participant status type)
     */
    function testAddStatusType ( ) {
        
        $params = array( 'name'          => 'testStatus',
                         'label'         => 'testParticipant',
                         'class'         => 'Positive',
                         'is_active'     => 1,
                         'is_counted'    => 1,
                         'weight'        => 13,
                         'visibility_id' => 1                );
        
        // check for add participant status type
        $statusType = CRM_Event_BAO_ParticipantStatusType::add( $params );
        foreach ( $params as $param => $value ) {
            $this->assertEquals( $value, $statusType->$param );
        }
        
        $params = array( 'id'            => $statusType->id,
                         'name'          => 'testStatus',
                         'label'         => 'testAlterParticipant',
                         'class'         => 'Pending',
                         'is_active'     => 0,
                         'is_counted'    => 0,
                         'weight'        => 14,
                         'visibility_id' => 2                );
        
        // check for add participant status type
        $statusType = CRM_Event_BAO_ParticipantStatusType::add( $params );
        foreach ( $params as $param => $value ) {
            $this->assertEquals( $value, $statusType->$param );
        }
        
    }
    
    /**
     * retrieve() method of participant status type
     */
    function testRetrieveStatusType( ) {
        
        $params = array( 'name'          => 'testStatus',
                         'label'         => 'testParticipant',
                         'class'         => 'Positive',
                         'is_active'     => 1,
                         'is_counted'    => 1,
                         'weight'        => 13,
                         'visibility_id' => 1                );
        
        $statusType = CRM_Event_BAO_ParticipantStatusType::create( $params );
        
        // retrieve status type
        $retrieveParams     = array( 'id' => $statusType->id );
        $default            = array( );
        $retrieveStatusType = CRM_Event_BAO_ParticipantStatusType::retrieve( $retrieveParams, $default );
        
        // check on retrieve values
        foreach ( $params as $param => $value ) {
            $this->assertEquals( $value, $retrieveStatusType->$param );
        }
        
    }
    
    /**
     * setIsActive() method of participant status type
     */
    function testSetIsActiveStatusType( ) {
        
        $params = array( 'name'          => 'testStatus',
                         'label'         => 'testParticipant',
                         'class'         => 'Positive',
                         'is_active'     => 0,
                         'is_counted'    => 1,
                         'weight'        => 15,
                         'visibility_id' => 1                );
        
        $statusType = CRM_Event_BAO_ParticipantStatusType::create( $params );
        $isActive   = 1;
        
        // set participant status type active
        CRM_Event_BAO_ParticipantStatusType::setIsActive( $statusType->id, $isActive );
        
        // compare expected value in db
        $this->assertDBCompareValue( 'CRM_Event_DAO_ParticipantStatusType', $statusType->id, 'is_Active', 
                                     'id', $isActive,'Check DB for is_Active value' );
        
    }
    
}
