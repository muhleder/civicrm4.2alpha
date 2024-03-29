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
require_once 'CRM/Member/BAO/MembershipStatus.php';

class CRM_Member_BAO_MembershipStatusTest extends CiviUnitTestCase
{
    function get_info( ) 
    {
        return array(
                     'name'        => 'MembershipStatus BAOs',
                     'description' => 'Test all Member_BAO_MembershipType methods.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    function setUp( ) 
    { 
        parent::setUp();

    }

    /* check function add()
     *
     */
    function testAdd( ) {
     
        $ids    = array();
        $params = array( 'name' => 'pending',
                         'is_active' => 1    
                         );
        
        $membershipStatus = CRM_Member_BAO_MembershipStatus::add( $params, $ids );

        $result = $this->assertDBNotNull( 'CRM_Member_BAO_MembershipStatus', $membershipStatus->id ,
                                          'name', 'id',
                                          'Database check on updated membership status record.' );
        $this->assertEquals( $result, 'pending', 'Verify membership status is_active.');
    }

    function testRetrieve( ) { 
 
        $ids    = array();
        $params = array( 'name' => 'testStatus',
                         'is_active' => 1    
                         );
        
        $membershipStatus = CRM_Member_BAO_MembershipStatus::add( $params, $ids );
        $defaults = array();
        $result = CRM_Member_BAO_MembershipStatus::retrieve( $params, $defaults );
        $this->assertEquals( $result->name, 'testStatus', 'Verify membership status name.');
        CRM_Member_BAO_MembershipStatus::del($membershipStatus->id);
    }

    function testSetIsActive( ) { 
    
        $ids    = array();
        $params = array( 'name' => 'pending',
                         'is_active' => 1    
                         );
        
        $membershipStatus = CRM_Member_BAO_MembershipStatus::add( $params, $ids );
        $result = CRM_Member_BAO_MembershipStatus::setIsActive( $membershipStatus->id, 0 );
        $this->assertEquals( $result, true , 'Verify membership status record updation.');
        
        $isActive = $this->assertDBNotNull( 'CRM_Member_BAO_MembershipStatus', $membershipStatus->id ,
                                            'is_active', 'id',
                                            'Database check on updated membership status record.' );
        $this->assertEquals( $isActive, 0, 'Verify membership status is_active.');
        
    }
     
    function testGetMembershipStatus( ) {
        $ids    = array();
        $params = array( 'name' => 'pending',
                         'is_active' => 1    
                         );
        
        $membershipStatus = CRM_Member_BAO_MembershipStatus::add( $params, $ids );  
        $result           = CRM_Member_BAO_MembershipStatus::getMembershipStatus($membershipStatus->id);
        $this->assertEquals( $result['name'], 'pending', 'Verify membership status name.');
    }

    function testDel( ) {
        $ids    = array();
        $params = array( 'name' => 'testStatus',
                         'is_active' => 1    
                         );
        
        $membershipStatus = CRM_Member_BAO_MembershipStatus::add( $params, $ids );  
        CRM_Member_BAO_MembershipStatus::del($membershipStatus->id);
        $defaults = array( );
        $result = CRM_Member_BAO_MembershipStatus::retrieve( $params, $defaults );
        $this->assertEquals( empty($result), true, 'Verify membership status record deletion.');
    }
    
    function testGetMembershipStatusByDate( ) {
        $ids    = array();
        $params = array( 'name' => 'Current',
                         'is_active' => 1,
                         'start_event' => 'start_date',
                         'end_event'   => 'end_date'
                         );
        
        $membershipStatus = CRM_Member_BAO_MembershipStatus::add( $params, $ids );  
        $toDate = date('Ymd');
        
        $result = CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( $toDate,$toDate,$toDate);
        $this->assertEquals( $result['name'], 'Current', 'Verify membership status record.');
    }

    function testgetMembershipStatusCurrent( ) {
        
        $ids    = array();
        $params = array( 'name' => 'Current',
                         'is_active' => 1,
                         'is_current_member' => 1,
                         );
        
        $membershipStatus = CRM_Member_BAO_MembershipStatus::add( $params, $ids ); 
        $result = CRM_Member_BAO_MembershipStatus::getMembershipStatusCurrent();
        
        $this->assertEquals( empty($result), false , 'Verify membership status records is_current_member.');
    }
     
}