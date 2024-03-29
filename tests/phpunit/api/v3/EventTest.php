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

require_once 'api/v3/Event.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_EventTest extends CiviUnitTestCase 
{
    protected $_params;
    protected $_apiversion;    
    protected $_entity;
    
    function get_info( )
    {
        return array(
                     'name'        => 'Event Create',
                     'description' => 'Test all Event Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }  

    function setUp() 
    {
        parent::setUp();
        $this->_apiversion =3;  
        $this->_entity = 'event'; 
        $this->_params = array(
            'title'                   => 'Annual CiviCRM meet',
            'summary'                 => 'If you have any CiviCRM realted issues or want to track where CiviCRM is heading, Sign up now',
            'description'             => 'This event is intended to give brief idea about progess of CiviCRM and giving solutions to common user issues',
            'event_type_id'           => 1,
            'is_public'               => 1,
            'start_date'              => 20081021,
            'end_date'                => 20081023,
            'is_online_registration'  => 1,
            'registration_start_date' => 20080601,
            'registration_end_date'   => '2008-10-15',
            'max_participants'        => 100,
            'event_full_text'         => 'Sorry! We are already full',
            'is_monetory'             => 0, 
            'is_active'               => 1,
            'is_show_location'        => 0,
            'version'				=>$this->_apiversion,        
        );

        $params = array(
                        'title'         => 'Annual CiviCRM meet',
                        'event_type_id' => 1,
                        'start_date'    => 20081021,
                        'version'				=>$this->_apiversion,
                        );

        $this->_event   = civicrm_api('Event','Create',$params);
        $this->_eventId = $this->_event['id'];
       
    }

    function tearDown() 
    {
        if ( $this->_eventId ) {
            $this->eventDelete( $this->_eventId );
        }        
        $this->eventDelete( $this->_event['id'] );	
        $tablesToTruncate = array( 'civicrm_participant', 
                                   'civicrm_event',
                                   );
        $this->quickCleanup( $tablesToTruncate, true );
    }

///////////////// civicrm_event_get methods

    function testGetWrongParamsType()
    {
        $params = 'Annual CiviCRM meet';
        $result = civicrm_api('Event','Get', $params );

        $this->assertEquals( $result['is_error'], 1 );
    }


    function testGetEventEmptyParams( )
    {
        $params = array( );
        $result = civicrm_api('event','get', $params );

        $this->assertEquals( $result['is_error'], 1 );
   }
    
    function testGetEventById( )
    {
        $params = array( 'id' => $this->_event['id'],
                         'version'				=>$this->_apiversion, );
        $result = civicrm_api('event','get', $params );
        $this->assertEquals( $result['values'][$this->_eventId]['event_title'], 'Annual CiviCRM meet' );
    }
    
    function testGetEventByEventTitle( )
    {
        $params = array( 'title' => 'Annual CiviCRM meet',
                         'version'=>$this->_apiversion,
        );
        
        $result = civicrm_api('event','get', $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['id'], $this->_eventId );
    }
    /*
     * Test 'is.Current' option. Existing event is 'old' so only current should be returned
     */
    function testGetIsCurrent( )
    {  
        $result = civicrm_api( 'Event','Get',$params );  
        $params = array( 
                         'version'=>$this->_apiversion,
                         'isCurrent' => 1,
        );
        $currentEventParams = array('start_date' => date('Y-m-d',strtotime('+ 1 day')),
                                    'end_date' =>   date('Y-m-d',strtotime('+ 1 week')),);
        $currentEventParams = array_merge($this->_params, $currentEventParams);
        $currentEvent = civicrm_api('Event', 'Create', $currentEventParams);
        $description = "demonstrates use of is.Current option";
        $subfile = "IsCurrentOption";
        $result = civicrm_api( 'Event','Get',$params );  
 
        $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile); 
        $allEvents  = civicrm_api( 'Event','Get',array('version' => 3) ); 
        civicrm_api('Event','Delete',array('version' => 3, 'id' =>$currentEventParams['id'] ))  ;
        $this->assertEquals( 1,$result['count'], 'confirm only one event found in line ' . __LINE__ );   
        $this->assertEquals( 2,$allEvents ['count'], 'confirm two events exist (ie. one not found) ' . __LINE__ ); 
        $this->assertEquals($currentEvent['id'], $result['id'],'' );
  
    }

    
    /*
     * Test 'is.Current' option. Existing event is 'old' so only current should be returned
     */
    function testGetSingleReturnIsFull( )
    {  
        $contactID = $this->individualCreate();
        $params = array( 'id' => $this->_eventId,
                         'version'=> $this->_apiversion,
          							 'max_participants' => 1,
        );
        $result = civicrm_api( 'Event','Create',$params);  

        $getEventParams = array(  'id' => $this->_eventId,
        													'version'=> $this->_apiversion,
                                  'return.is_full' => 1,);

        $currentEvent = civicrm_api('Event', 'getsingle', $getEventParams);
        $description = "demonstrates use of return is_full ";
        $subfile = "IsFullOption";
        $this->assertEquals(0, $currentEvent['is_full'],' is full is set in line ' . __LINE__);
        $this->assertEquals(1, $currentEvent['available_places'], 'available places is set in line ' . __LINE__);        
        $participant = civicrm_api('Participant', 'create', array('version' => 3,'participant_status' => 1, 'role_id' => 1, 'contact_id' => $contactID,'event_id' => $this->_eventId));
        $currentEvent = civicrm_api('Event', 'getsingle', $getEventParams);
        
        $this->documentMe($getEventParams ,$currentEvent,__FUNCTION__,__FILE__,$description,$subfile,'getsingle'); 
        $this->assertEquals(1, $currentEvent['is_full'],' is full is set in line ' . __LINE__);
        $this->assertEquals(0, $currentEvent['available_places'], 'available places is set in line ' . __LINE__);        
        
        $this->contactDelete($contactID);

  
    }
    
///////////////// civicrm_event_create methods
        /**
     * check with complete array + custom field 
     * Note that the test is written on purpose without any
     * variables specific to participant so it can be replicated into other entities
     * and / or moved to the automated test suite
     */
    function testCreateWithCustom()
    {
        $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);

        $params = $this->_params;
        $params['custom_'.$ids['custom_field_id']]  =  "custom string";
 
        $result = civicrm_api($this->_entity,'create', $params);
        $this->documentMe($params,$result  ,__FUNCTION__,__FILE__);
        $this->assertNotEquals( $result['is_error'],1 ,$result['error_message'] . ' in line ' . __LINE__);

        $check = civicrm_api($this->_entity,'get',array('version' =>3,'return.custom_' . $ids['custom_field_id'] => 1, 'id' => $result['id']));
        $this->assertEquals("custom string", $check['values'][$check['id']]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);
   
        $this->customFieldDelete($ids['custom_field_id']);
        $this->customGroupDelete($ids['custom_group_id']); 
        civicrm_api($this->_entity,'Delete',array('version' => 3 , 'id' => $result['id'])) ;    

    }  
    function testCreateEventParamsNotArray( )
    {
        $params = null;
        $result = civicrm_api('event','create',$params );
        $this->assertEquals( 1, $result['is_error'] );
    }    
    
    function testCreateEventEmptyParams( )
    {
        $params = array( );
        $result = civicrm_api('event','create',$params );
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    function testCreateEventParamsWithoutTitle( )
    {
        unset($this->_params['title']);
        $result = civicrm_api('event','create',$this->_params );
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    function testCreateEventParamsWithoutEventTypeId( )
    {
        unset($this->_params['event_type_id']);
        $result = civicrm_api('event','create',$this->_params );
        $this->assertEquals( $result['is_error'], 1 );
   }
    
    function testCreateEventParamsWithoutStartDate( )
    {
        unset($this->_params['start_date']);
        $result = civicrm_api('event','create',$this->_params );
        $this->assertEquals( $result['is_error'], 1 );
   }
    
    function testCreateEventSuccess( )
    {
        $result = civicrm_api('Event','Create', $this->_params );
        $this->documentMe($this->_params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertArrayHasKey( 'id', $result['values'][$result['id']], 'In line ' . __LINE__  );
        $result = civicrm_api($this->_entity,'Get',array('version' => 3 , 'id' => $result['id'])) ; 
        civicrm_api($this->_entity,'Delete',array('version' => 3 , 'id' => $result['id'])) ;    
        
        $this->assertEquals('2008-10-21 00:00:00',$result['values'][$result['id']]['start_date'],'start date is not set in line ' . __LINE__);
        $this->assertEquals('2008-10-23 00:00:00',$result['values'][$result['id']]['end_date'],'end date is not set in line ' . __LINE__);
        $this->assertEquals('2008-06-01 00:00:00',$result['values'][$result['id']]['registration_start_date'],'start date is not set in line ' . __LINE__);
        $this->assertEquals('2008-10-15 00:00:00',$result['values'][$result['id']]['registration_end_date'],'end date is not set in line ' . __LINE__); 
        civicrm_api($this->_entity,'Delete',array('version' => 3 , 'id' => $result['id'])) ;    
        
    }
    /*
     * Test that passing in Unique field names works
     */
    function testCreateEventSuccessUniqueFieldNames( )
    {   $this->_params['event_start_date'] = $this->_params['start_date'];
        unset($this->_params['start_date']);
        $this->_params['event_title'] = $this->_params['title'];
        unset($this->_params['title']);
        $result = civicrm_api('Event','Create', $this->_params );
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $this->assertArrayHasKey( 'id', $result['values'][$result['id']], 'In line ' . __LINE__  );
        $result = civicrm_api($this->_entity,'Get',array('version' => 3 , 'id' => $result['id'])) ; 
        civicrm_api($this->_entity,'Delete',array('version' => 3 , 'id' => $result['id'])) ;    
        
        $this->assertEquals('2008-10-21 00:00:00',$result['values'][$result['id']]['start_date'],'start date is not set in line ' . __LINE__);
        $this->assertEquals('2008-10-23 00:00:00',$result['values'][$result['id']]['end_date'],'end date is not set in line ' . __LINE__);
        $this->assertEquals('2008-06-01 00:00:00',$result['values'][$result['id']]['registration_start_date'],'start date is not set in line ' . __LINE__);
        $this->assertEquals('2008-10-15 00:00:00',$result['values'][$result['id']]['registration_end_date'],'end date is not set in line ' . __LINE__); 
        $this->assertEquals($this->_params['event_title'],$result['values'][$result['id']]['title'],'end date is not set in line ' . __LINE__); 
  
        civicrm_api($this->_entity,'Delete',array('version' => 3 , 'id' => $result['id'])) ;    
        
    }
    
    function testUpdateEvent( )
    {
        $result = civicrm_api('event','create',$this->_params );

        $this->assertEquals( $result['is_error'], 0 );
        $params =array('id' => $result['id'], 'version'=>3, 'max_participants'        => 150,);
        civicrm_api('Event','Create',$params);
        $updated = civicrm_api('Event','Get',$params);
        $this->documentMe($this->_params,$updated,__FUNCTION__,__FILE__); 
        $this->assertEquals(  $updated['is_error'], 0 );
        $this->assertEquals( 150, $updated['values'][$result['id']]['max_participants'] );
        $this->assertEquals( 'Annual CiviCRM meet', $updated['values'][$result['id']]['title'] );
        civicrm_api($this->_entity,'Delete',array('version' => 3 , 'id' => $result['id'])) ;    
 
 
    }
    
///////////////// civicrm_event_delete methods

    function testDeleteWrongParamsType()
    {
        $params = 'Annual CiviCRM meet';
        $result =& civicrm_api('Event','Delete',$params);

        $this->assertEquals($result['is_error'], 1);        
    }

    function testDeleteEmptyParams( )
    {
        $params = array( );
        $result =& civicrm_api('Event','Delete',$params);
        $this->assertEquals($result['is_error'], 1);        
    }
    
    function testDelete( )
    {
        $params = array('id' => $this->_eventId,
                        'version'				=>$this->_apiversion,);
        $result =& civicrm_api('Event','Delete',$params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertNotEquals($result['is_error'], 1);
    }
    /*
     * check event_id still supported for delete
     */
    function testDeleteWithEventId( )
    {
        $params = array('event_id' => $this->_eventId,
                        'version'				=>$this->_apiversion,);
        $result =& civicrm_api('Event','Delete',$params);       
        $this->assertAPISuccess($result, 'in line ' . __LINE__);
    }   
    /*
     * Trying to delete an event with participants should return error
     */
    function testDeleteWithExistingParticipant(){
        $contactID = $this->individualCreate(null ) ;
        $participantID = $this->participantCreate(
                         array(
        								       'contactID' => $contactID,
        								       'eventID' => $this->_eventId  ));
        $result = civicrm_api('Event','Delete', array('version' => $this->_apiversion, 'id' => $this->_eventId));
        $this->assertEquals(0, $result['is_error'], "Deleting exist with participants");
       
    }
    function testDeleteWithWrongEventId( )
    {
        $params = array('event_id' => $this->_eventId, 'version' => $this->_apiversion);
        $result =& civicrm_api('Event','Delete',$params);
        // try to delete again - there's no such event anymore
        $params = array('event_id' => $this->_eventId);
        $result =& civicrm_api('Event','Delete',$params);
        $this->assertEquals($result['is_error'], 1);
    }

///////////////// civicrm_event_search methods

    /**
     *  Test civicrm_event_search with wrong params type
     */
    function testSearchWrongParamsType()
    {
        $params = 'a string';
        $result =& civicrm_api('event','get',$params);

        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );
   }

    /**
     *  Test civicrm_event_search with empty params
     */
     function testSearchEmptyParams()
     {
         $event  = civicrm_api('event','create',$this->_params );

         $getparams = array('version' => $this->_apiversion,
                         'sequential' => 1, );
         $result =& civicrm_api('event','get',$getparams);
         $this->assertEquals($result['count'],2, 'In line ' . __LINE__);
         $res    = $result['values'][0];
         $this->assertArrayKeyExists('title', $res, 'In line ' . __LINE__ );
         $this->assertEquals( $res['event_type_id'], $this->_params['event_type_id'] , 'In line ' . __LINE__ );
        
     }

    /**
     *  Test civicrm_event_search. Success expected.
     */
     function testSearch()
     {
          $params = array(
                    'event_type_id'        => 1,
                    'return.title'         => 1,
                    'return.id'            => 1,
                    'return.start_date'    => 1,
                    'version'				=>$this->_apiversion,
                    );
          $result =& civicrm_api('event','get',$params);

          $this->assertEquals( $result['values'][$this->_eventId]['id'], $this->_eventId , 'In line ' . __LINE__ );
          $this->assertEquals( $result['values'][$this->_eventId]['title'], 'Annual CiviCRM meet' , 'In line ' . __LINE__ );
     }

    /**
     *  Test civicrm_event_search. Success expected.
     *  return.offset and return.max_results test (CRM-5266)
     */
     function testSearchWithOffsetAndMaxResults()
     {
         $maxEvents = 5;
         $events    = array( );
         while( $maxEvents > 0 ) {
             $params = array(
                             'title'         => 'Test Event'.$maxEvents,
                             'event_type_id' => 2,
                             'start_date'    => 20081021,
                             );
             
             $events[$maxEvents]  = civicrm_api('event','create',$params);
             $maxEvents--;
         }
         $params = array(
                         'event_type_id'      => 2,
                         'return.id'          => 1,
                         'return.title'       => 1,
                         'return.offset'      => 2,
                         'return.max_results' => 2
                         );
         $result =& civicrm_api('event','get',$params);
         $this->assertEquals( count($result), 2 , 'In line ' . __LINE__ ); 
     }

    function testEventCreationPermissions()
    {
        require_once 'CRM/Core/Permission/UnitTests.php';
        $params = array('event_type_id' => 1, 'start_date' => '2010-10-03', 'title' => 'le cake is a tie', 'check_permissions' => true,
                        'version' => $this->_apiversion);

        CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM');
        $result = civicrm_api('event', 'create', $params);
        $this->assertEquals(1,                                                                                          $result['is_error'], 'lacking permissions should not be enough to create an event');
        $this->assertEquals('API permission check failed for event/create call; missing permission: access CiviEvent.', $result['error_message'], 'lacking permissions should not be enough to create an event');

        CRM_Core_Permission_UnitTests::$permissions = array('access CiviEvent', 'edit all events', 'access CiviCRM');
        $result = civicrm_api('event', 'create', $params);
        $this->assertEquals(0, $result['is_error'], 'overfluous permissions should be enough to create an event');
    }
    function testgetfields(){
      $description = "demonstrate use of getfields to interogate api";
      $params = array('version' => 3, 'action' => 'create');
      $result = civicrm_api('event', 'getfields',$params);
      $this->assertEquals(1, $result['values']['title']['api.required'], 'in line ' . __LINE__);
    }
    /* 
     * test api_action param also works
     */
    function testgetfieldsRest(){
      $description = "demonstrate use of getfields to interogate api";
      $params = array('version' => 3, 'api_action' => 'create');
      $result = civicrm_api('event', 'getfields',$params);
      $this->assertEquals(1, $result['values']['title']['api.required'], 'in line ' . __LINE__);
    }
    function testgetfieldsDelete(){
      $description = "demonstrate use of getfields to interogate api";
      $params = array('version' => 3, 'action' => 'delete');
      $result = civicrm_api('event', 'getfields',$params);
      $this->assertEquals(1, $result['values']['id']['api.required']);
    }
}
