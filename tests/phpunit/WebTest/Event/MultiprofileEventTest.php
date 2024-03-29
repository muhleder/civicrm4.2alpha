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
   | License along with this program; if not, contact CiviCRM LLC       |
   | at info[AT]civicrm[DOT]org. If you have questions about the        |
   | GNU Affero General Public License or the licensing of CiviCRM,     |
   | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
   +--------------------------------------------------------------------+
  */

require_once 'CiviTest/CiviSeleniumTestCase.php';

class WebTest_Event_MultiprofileEventTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testCreateEventRegisterPage()
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        $customGrp1 = "Custom Data1_" . substr(sha1(rand()), 0, 7);
        $firstName = 'Ma' . substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An' . substr( sha1( rand( ) ), 0, 7 );
        $participantfname = 'Dany'. substr( sha1( rand( ) ), 0, 4 );
        $participantlname = 'Dan'. substr( sha1( rand( ) ), 0, 4 );
        $email1 = $firstName."@test.com";
        $email2 = $participantfname."@test.com";
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
        $this->webtestAddPaymentProcessor($processorName);
        
        // create custom group1
        $this->open($this->sboxPath . "civicrm/admin/custom/group?reset=1");
        $this->click("newCustomDataGroup");
        $this->waitForPageToLoad('30000');
        $this->type("title",$customGrp1);
        $this->select("extends[0]","value=Contact");
        $this->click("_qf_Group_next-bottom");
        $this->waitForPageToLoad('30000');
        
        // get custom group id
        $elements = $this->parseURL( );
        $customGrpId1 = $elements['queryString']['gid'];
        
        $customId = $this->_testGetCustomFieldId( $customGrpId1 );
        
        $profileId = $this->_testGetProfileId( $customId );
        
        // Go directly to the URL of the screen that you will be testing (New Event).
        $this->open($this->sboxPath . "civicrm/event/add?reset=1&action=add");
        
        $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
        $eventDescription = "Here is a description for this conference.";
        $this->_testAddEventInfo( $eventTitle, $eventDescription );
        
        $streetAddress = "100 Main Street";
        $this->_testAddLocation( $streetAddress );
        
        $this->_testAddFees( false, false, $processorName );
        
        $eventPageId = $this->_testAddMultipleProfile( $profileId );
        
        $this->_testEventRegistration( $eventPageId , $customId , $firstName , $lastName ,
                                       $participantfname , $participantlname , $email1 , $email2 );
        $this->waitForPageToLoad( '30000' );
        
        // Find Main Participant
        $this->open( $this->sboxPath . "civicrm/event/search?reset=1" );
        $this->type( "sort_name", $firstName );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ParticipantView_cancel-top" );
        
        $name = $firstName." ".$lastName;
        $status = 'Registered';
        
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[1]/td[2]/a", preg_quote( $name ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[3]/td[2]/a", preg_quote( $eventTitle ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[6]/td[2]", preg_quote( $status ) ); 
        
        // Find additional  Participant
        $this->open( $this->sboxPath . "civicrm/event/search?reset=1" );
        $this->type( "sort_name", $participantfname );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ParticipantView_cancel-top" );
        
        $name = $participantfname . " " . $participantlname;
        $status = 'Registered';
        
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[1]/td[2]/a", preg_quote( $name ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[3]/td[2]/a", preg_quote( $eventTitle ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[6]/td[2]", preg_quote( $status ) ); 
        
        // delete all custom data
        $this->open( $this->sboxPath . "civicrm/admin/custom/group/field?action=delete&reset=1&gid=" . $customGrpId1 . "&id=" . $customId[0] );
        $this->waitForPageToLoad("30000");
        $this->click( "_qf_DeleteField_next-bottom" );
        $this->waitForPageToLoad("30000");
        
        $this->open( $this->sboxPath . "civicrm/admin/custom/group/field?action=delete&reset=1&gid=" . $customGrpId1 . "&id=" . $customId[1] );
        $this->waitForPageToLoad("30000");
        $this->click( "_qf_DeleteField_next-bottom" );
        $this->waitForPageToLoad("30000");
        
        $this->open( $this->sboxPath . "civicrm/admin/custom/group/field?action=delete&reset=1&gid=" . $customGrpId1 . "&id=" . $customId[2] );
        $this->waitForPageToLoad("30000");
        $this->click( "_qf_DeleteField_next-bottom" );
        $this->waitForPageToLoad("30000");
        
        $this->open( $this->sboxPath . "civicrm/admin/custom/group?action=delete&reset=1&id=" . $customGrpId1 );
        $this->waitForPageToLoad("30000");
        $this->click( "_qf_DeleteGroup_next-bottom" );
        $this->waitForPageToLoad("30000");
        
        // logout
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->waitForPageToLoad( '30000' );
    }
    
    function testAnoumyousRegisterPage()
    {
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin( true );
        
        // add the required Drupal permission
        $permission = array('edit-1-access-all-custom-data');
        $this->changePermissions( $permission );
       
        $customGrp1 = "Custom Data1_" . substr(sha1(rand()), 0, 7);
        $firstName = 'Ma' . substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An' . substr( sha1( rand( ) ), 0, 7 );
        $participantfname = 'Dany'. substr( sha1( rand( ) ), 0, 4 );
        $participantlname = 'Dan'. substr( sha1( rand( ) ), 0, 4 );
        $email1 = $firstName."@test.com";
        $email2 = $participantfname."@test.com";
        $firstName2 = 'Man' . substr( sha1( rand( ) ), 0, 4 );
        $lastName2  = 'Ann' . substr( sha1( rand( ) ), 0, 7 );
        $participantfname2 = 'Adam'. substr( sha1( rand( ) ), 0, 4 );
        $participantlname2 = 'Gil'. substr( sha1( rand( ) ), 0, 4 );
        $email3 = $participantfname2."@test.com";
        $email4 = $firstName2."@test.com";
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
        $this->webtestAddPaymentProcessor($processorName);
        
        // create custom group1
        $this->open($this->sboxPath . "civicrm/admin/custom/group?reset=1");
        $this->click("newCustomDataGroup");
        $this->waitForPageToLoad('30000');
        $this->type("title",$customGrp1);
        $this->select("extends[0]","value=Contact");
        $this->click("_qf_Group_next-bottom");
        $this->waitForPageToLoad('30000');
        
        // get custom group id
        $elements = $this->parseURL( );
        $customGrpId1 = $elements['queryString']['gid'];
        
        $customId = $this->_testGetCustomFieldId( $customGrpId1 );
        
        $profileId =$this->_testGetProfileId( $customId );
        
        // Go directly to the URL of the screen that you will be testing (New Event).
        $this->open($this->sboxPath . "civicrm/event/add?reset=1&action=add");
        
        $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
        $eventDescription = "Here is a description for this conference.";
        $this->_testAddEventInfo( $eventTitle, $eventDescription );
        
        $streetAddress = "100 Main Street";
        $this->_testAddLocation( $streetAddress );
        
        $this->_testAddFees( false, false, $processorName );
        
        $eventPageId = $this->_testAddMultipleProfile( $profileId );
        
        // logout
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        $this->_testEventRegistration( $eventPageId , $customId , $firstName , $lastName , $participantfname , $participantlname , $email1 , $email2 );
        $this->waitForPageToLoad( '30000' );
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // Find Main Participant
        $this->open( $this->sboxPath . "civicrm/event/search?reset=1" );
        $this->type( "sort_name", $firstName );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ParticipantView_cancel-top" );
        
        $name = $firstName." ".$lastName;
        $status = 'Registered';
        
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[1]/td[2]/a", preg_quote( $name ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[3]/td[2]/a", preg_quote( $eventTitle ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[6]/td[2]", preg_quote( $status ) ); 
        
        // Find additional  Participant
        $this->open( $this->sboxPath . "civicrm/event/search?reset=1" );
        $this->type( "sort_name", $participantfname );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ParticipantView_cancel-top" );
        
        $name = $participantfname." ".$participantlname;
        $status = 'Registered';
        
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[1]/td[2]/a", preg_quote( $name ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[3]/td[2]/a", preg_quote( $eventTitle ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[6]/td[2]", preg_quote( $status ) ); 
        
        // Edit page and remove some profile
        $this->_testRemoveProfile( $eventPageId );
        
        // logout
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        $this->_testEventRegistrationAfterRemoving( $eventPageId , $customId , $firstName2 , $lastName2 , $participantfname2 , $participantlname2 , $email3 , $email4 );
        $this->waitForPageToLoad( '30000' );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // Find Main Participant
        $this->open( $this->sboxPath . "civicrm/event/search?reset=1" );
        $this->type( "sort_name", $firstName2 );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ParticipantView_cancel-top" );
        
        $name = $firstName2." ".$lastName2;
        $status = 'Registered';
        
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[1]/td[2]/a", preg_quote( $name ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[3]/td[2]/a", preg_quote( $eventTitle ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[6]/td[2]", preg_quote( $status ) ); 
        
        // Find additional  Participant
        $this->open( $this->sboxPath . "civicrm/event/search?reset=1" );
        $this->type( "sort_name", $participantfname2 );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='participantSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ParticipantView_cancel-top" );
        
        $name = $participantfname2." ".$participantlname2;
        $status = 'Registered';
        
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[1]/td[2]/a", preg_quote( $name ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[3]/td[2]/a", preg_quote( $eventTitle ) ); 
        $this->verifyText( "xpath=//form[@id='ParticipantView']/div[2]/table/tbody/tr[6]/td[2]", preg_quote( $status ) ); 
        
        // delete all custom data
        $this->open( $this->sboxPath . "civicrm/admin/custom/group/field?action=delete&reset=1&gid=" . $customGrpId1 . "&id=" . $customId[0] );
        $this->waitForPageToLoad("30000");
        $this->click( "_qf_DeleteField_next-bottom" );
        $this->waitForPageToLoad("30000");
        
        $this->open( $this->sboxPath . "civicrm/admin/custom/group/field?action=delete&reset=1&gid=" . $customGrpId1 . "&id=" . $customId[1] );
        $this->waitForPageToLoad("30000");
        $this->click( "_qf_DeleteField_next-bottom" );
        $this->waitForPageToLoad("30000");
        
        $this->open( $this->sboxPath . "civicrm/admin/custom/group/field?action=delete&reset=1&gid=" . $customGrpId1 . "&id=" . $customId[2] );
        $this->waitForPageToLoad("30000");
        $this->click( "_qf_DeleteField_next-bottom" );
        $this->waitForPageToLoad("30000");
        
        $this->open( $this->sboxPath . "civicrm/admin/custom/group?action=delete&reset=1&id=" . $customGrpId1 );
        $this->waitForPageToLoad("30000");
        $this->click( "_qf_DeleteGroup_next-bottom" );
        $this->waitForPageToLoad("30000");
    }
    
    function _testGetCustomFieldId( $customGrpId1 )
    {
        $customId = array();
        
        // Create a custom data to add in profile
        
        $field1 = "Fname" . substr(sha1(rand()), 0, 7);
        $field2 = "Mname" . substr(sha1(rand()), 0, 7);
        $field3 = "Lname" . substr(sha1(rand()), 0, 7);
        
        // add custom fields for group 1
        $this->open( $this->sboxPath . "civicrm/admin/custom/group/field/add?reset=1&action=add&gid=" .$customGrpId1 );
        $this->waitForPageToLoad('30000');
        $this->type("label",$field1);
        $this->check("is_searchable");
        $this->click("_qf_Field_next_new-bottom");
        $this->waitForPageToLoad('30000');
        
        $this->type("label",$field2);
        $this->check("is_searchable");
        $this->click("_qf_Field_next_new-bottom");
        $this->waitForPageToLoad('30000');
        
        $this->type("label",$field3);
        $this->check("is_searchable");
        $this->click("_qf_Field_next-bottom");
        $this->waitForPageToLoad('30000');
        
        // get id of custom fields
        $this->open( $this->sboxPath . "civicrm/admin/custom/group/field?reset=1&action=browse&gid=" .$customGrpId1 );
        $custom1 = explode( '&id=', $this->getAttribute( "xpath=//div[@id='field_page']//table/tbody//tr[1]/td[8]/span/a[text()='Edit Field']/@href" ) );
        $custom1 = $custom1[1];
        array_push($customId , $custom1);
        $custom2 = explode( '&id=', $this->getAttribute( "xpath=//div[@id='field_page']//table/tbody//tr[2]/td[8]/span/a[text()='Edit Field']/@href" ) );
        $custom2 = $custom2[1];
        array_push($customId , $custom2);
        $custom3 = explode( '&id=', $this->getAttribute( "xpath=//div[@id='field_page']//table/tbody//tr[3]/td[8]/span/a[text()='Edit Field']/@href" ) );
        $custom3 = $custom3[1];
        array_push($customId , $custom3);
        
        return $customId;
    }
    
    
    function _testRemoveProfile( $eventPageId )
    {
        $this->open($this->sboxPath . "civicrm/event/manage/settings?reset=1&action=update&id=" . $eventPageId );
        
        // Go to Online Contribution tab
        $this->click("link=Online Registration");
        $this->waitForElementPresent("_qf_Registration_upload-bottom");
        $this->select("additional_custom_post_id_multiple_1" , "value=none");
        $this->select("additional_custom_post_id_multiple_2" , "value=none");
        $this->select("additional_custom_post_id_multiple_3" , "value=none");
        $this->select("additional_custom_post_id_multiple_4" , "value=none");
        $this->click("_qf_Registration_upload-bottom");
        $this->waitForPageToLoad('30000');
    }
    
    function _testGetProfileId( $customId )
    {
        // create profiles 
        $profileId = array();
        $profilefield = array(
                              'street_address' => 'street_address',
                              'supplemental_address_1' => 'supplemental_address_1',
                              'city' => 'city'
                              );
        $location = 1;
        $type = "Contact";
        $profileId1 = $this->_testCreateProfile($profilefield,$location,$type);
        array_push($profileId,$profileId1);
        
        $profilefield = array(
                              'street_address' => 'street_address',
                              'city' => 'city',
                              'phone' => 'phone',
                              'postal_code' => 'postal_code'
                              );
        $location = 0;
        $type = "Contact";
        $profileId2 = $this->_testCreateProfile($profilefield,$location,$type);
        array_push($profileId,$profileId2);
        
        $profilefield = array(
                              'nick_name' => 'nick_name',
                              'url' => 'url'
                              );
        $location = 0;
        $type = "Contact";
        $profileId3 = $this->_testCreateProfile($profilefield,$location,$type);
        array_push($profileId,$profileId3);
        
        $profilefield = array(
                              'current_employer' => 'current_employer',
                              'job_title' => 'job_title'
                              );
        $location = 0;
        $type = "Individual";
        $profileId4 = $this->_testCreateProfile($profilefield,$location,$type);
        array_push($profileId,$profileId4);
        
        $profilefield = array(
                              'middle_name' => 'middle_name',
                              'gender' => 'gender'
                              );
        $location = 0;
        $type = "Individual";
        $profileId5 = $this->_testCreateProfile($profilefield,$location,$type);
        array_push($profileId,$profileId5);
        
        $profilefield = array(
                              'custom_'.$customId[0] =>  'custom_'.$customId[0],
                              'custom_'.$customId[1] =>  'custom_'.$customId[1],
                              'custom_'.$customId[2] =>  'custom_'.$customId[2]
                              );
        $location = 0;
        $type = "Contact";
        $profileId6 = $this->_testCreateProfile($profilefield,$location,$type);
        array_push($profileId,$profileId6);
        
        $profilefield = array(
                              'participant_role' => 'participant_role'
                              );
        $location = 0;
        $type = "Participant";
        $profileId7 = $this->_testCreateProfile($profilefield,$location,$type);
        array_push($profileId,$profileId7);
        
        return $profileId;      
    }
    
    function _testCreateProfile($profilefield,$location = 0,$type)
    {
        $locationfields = array(
                                'supplemental_address_1',
                                'supplemental_address_2',
                                'city',
                                'country',
                                'email',
                                'state',
                                'street_address',
                                'postal_code'
                                );
        
        // Go directly to the URL of the screen that you will be
        // testing (Add new profile ).
        $profilename = "Profile_" . substr(sha1(rand()), 0, 7);
        $this->open($this->sboxPath . 'civicrm/admin/uf/group?reset=1');
        $this->waitForPageToLoad('30000');
        $this->click('newCiviCRMProfile-top');
        $this->waitForElementPresent('_qf_Group_next-top');
        
        //Name of profile
        $this->type('title' , $profilename );
        $this->click('_qf_Group_next-top');
        $this->waitForPageToLoad('30000');
        $elements = $this->parseURL( );
        $profileId = $elements['queryString']['gid'];
        
        //Add field to profile_testCreateProfile
        foreach( $profilefield as $key => $value ) {
            $this->open( $this->sboxPath . 'civicrm/admin/uf/group/field/add?reset=1&action=add&gid=' . $profileId );
            $this->waitForPageToLoad('30000');
            if ( in_array( $value,$locationfields ) ) {                      
                $this->select("field_name[0]", "value={$type}");
                $this->select("field_name[1]","value={$value}");
                $this->select("field_name[2]","value={$location}");
                $this->type("label",$value);
            } else {
                $this->select("field_name[0]", "value={$type}");
                $this->select("field_name[1]","value={$value}");
                $this->type("label",$value);
            }
            $this->click('_qf_Field_next-top');
            $this->waitForPageToLoad('30000');
        }
        return $profileId;
    }
    
    function _testAddEventInfo( $eventTitle, $eventDescription ) 
    {
        // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent("_qf_EventInfo_upload-bottom");
        
        // Let's start filling the form with values.
        $this->select("event_type_id", "value=1");
        
        // Attendee role s/b selected now.
        $this->select("default_role_id", "value=1");
        
        // Enter Event Title, Summary and Description
        $this->type("title", $eventTitle);
        $this->type("summary", "This is a great conference. Sign up now!");
        
        // Type description in ckEditor (fieldname, text to type, editor)
        $this->fillRichTextField( "description", $eventDescription,'CKEditor' );
        
        // Choose Start and End dates.
        // Using helper webtestFillDate function.
        $this->webtestFillDateTime("start_date", "+1 week");
        $this->webtestFillDateTime("end_date", "+1 week 1 day 8 hours ");
        
        $this->type("max_participants", "50");
        $this->click("is_map");
        $this->click("_qf_EventInfo_upload-bottom");      
    }
    
    function _testAddLocation( $streetAddress ) 
    {
        // Wait for Location tab form to load
        $this->waitForPageToLoad("30000");
        $this->waitForElementPresent("_qf_Location_upload-bottom");
        
        // Fill in address fields
        //$streetAddress = "100 Main Street";
        $this->type("address_1_street_address", $streetAddress);
        $this->type("address_1_city", "San Francisco");
        $this->type("address_1_postal_code", "94117");
        $this->select("address_1_state_province_id", "value=1004");
        $this->type("email_1_email", "info@civicrm.org");
        
        $this->click("_qf_Location_upload-bottom");      
        
        // Wait for "saved" status msg
        $this->waitForPageToLoad('30000');
        $this->waitForTextPresent("'Location' information has been saved.");
        
    }
    
    function _testAddFees( $discount=false, $priceSet=false, $processorName = "PP Pro" )
    {
        // Go to Fees tab
        $this->click("link=Fees");
        $this->waitForElementPresent("_qf_Fee_upload-bottom");
        $this->click("CIVICRM_QFID_1_2");
        $this->select("payment_processor_id", "label=" . $processorName);
        $this->select("contribution_type_id", "value=4");
        if ( $priceSet) {
            // get one - TBD
        } else {
            $this->type("label_1", "Member");
            $this->type("value_1", "250.00");
            $this->type("label_2", "Non-member");
            $this->type("value_2", "325.00");          
        }
        
        if ( $discount ) {
            // enter early bird discounts TBD
        }
        
        $this->click("_qf_Fee_upload-bottom");      
        
        // Wait for "saved" status msg
        $this->waitForPageToLoad('30000');
        $this->waitForTextPresent("'Fee' information has been saved.");      
    }
    
    function _testAddMultipleProfile( $profileId )
    {
        // Go to Online Contribution tab
        $this->click("link=Online Registration");
        $this->waitForElementPresent("_qf_Registration_upload-bottom");
        $this->click("is_online_registration");
        $this->check("is_multiple_registrations");
        $this->select("custom_pre_id", "value=1");
        $this->select("custom_post_id", "value=" . $profileId[3] );
        
        $this->waitForElementPresent( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "custom_post_id_multiple_1" );
        $this->select("custom_post_id_multiple_1", "value=". $profileId[2] );
        
        $this->waitForElementPresent( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "custom_post_id_multiple_2" );
        $this->select("custom_post_id_multiple_2", "value=". $profileId[1] );
        
        $this->waitForElementPresent( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "custom_post_id_multiple_3" );
        $this->select("custom_post_id_multiple_3", "value=". $profileId[4] );
        
        $this->waitForElementPresent( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "custom_post_id_multiple_4" );
        $this->select("custom_post_id_multiple_4", "value=". $profileId[5] );
        
        $this->waitForElementPresent( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[2]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "custom_post_id_multiple_5" );
        $this->select("custom_post_id_multiple_5", "value=". $profileId[6] );
        
        $this->waitForElementPresent("xpath=//div[@id='registration_screen']/table[3]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[3]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "additional_custom_post_id_multiple_1" );
        $this->select("additional_custom_post_id_multiple_1", "value=" . $profileId[5] );
        
        $this->waitForElementPresent("xpath=//div[@id='registration_screen']/table[3]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[3]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "additional_custom_post_id_multiple_2" );
        $this->select("additional_custom_post_id_multiple_2", "value=" . $profileId[1] );
        
        $this->waitForElementPresent("xpath=//div[@id='registration_screen']/table[3]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[3]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "additional_custom_post_id_multiple_3" );
        $this->select("additional_custom_post_id_multiple_3", "value=" . $profileId[2] );
        
        $this->waitForElementPresent("xpath=//div[@id='registration_screen']/table[3]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->click( "xpath=//div[@id='registration_screen']/table[3]//tbody/tr[2]/td[2]/span/a[text()='add profile']" );
        $this->waitForElementPresent( "additional_custom_post_id_multiple_4" );
        $this->select("additional_custom_post_id_multiple_4", "value=" . $profileId[3] );
        
        $this->click("CIVICRM_QFID_1_2");
        $this->type("confirm_from_name","TestEvent");
        $this->type("confirm_from_email","testevent@test.com");
        $this->click("_qf_Registration_upload-bottom"); 
        
        // Wait for "saved" status msg
        $this->waitForPageToLoad('30000');
        //sleep(5);
        $elements = $this->parseURL( );
        $eventPageId = $elements['queryString']['id'];
        
        $this->waitForTextPresent("'Registration' information has been saved.");   
        
        return $eventPageId;   
    }
    
    
    function _testEventRegistration( $eventPageId , $customId , $firstName , $lastName , 
                                     $participantfname , $participantlname , $email1 , $email2 )
    {                
        $this->open($this->sboxPath . 'civicrm/event/register?id=' .$eventPageId.'&reset=1');
        $this->waitForElementPresent( "_qf_Register_upload-bottom" );
        $this->select("additional_participants","value=1");
        
        $this->type("email-5",$email1);
        $this->type("first_name",$firstName);
        $this->type("last_name",$lastName);
        $this->type("street_address-1","Test street addres");
        $this->type("city-1","Mumbai");
        $this->type("postal_code-1","2354");
        $this->select("state_province-1","value=1001");
        
        // Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        
        //Billing Info
        $this->type( "billing_first_name", $firstName . 'billing' );
        $this->type( "billing_last_name", $lastName . 'billing'  );
        $this->type( "billing_street_address-5", "0121 Mount Highschool." );
        $this->type( " billing_city-5", "Shangai" );
        $this->select( "billing_country_id-5", "value=1228" );
        $this->select( "billing_state_province_id-5", "value=1004" );
        $this->type( "billing_postal_code-5", "94129" );  
        
        $this->type( "current_employer", "ABCD" ); 
        $this->type( "job_title", "Painter" ); 
        $this->type( "nick_name", "Nick" ); 
        $this->type( "url-1", "http://www.test.com" ); 
        
        $this->type( "street_address-Primary", "Primary street address" );
        $this->type( "city-Primary", "primecity" );
        $this->type( "phone-Primary-1", "98667764" );
        $this->type( "postal_code-Primary", "6548" );
        
        $this->type( "custom_".$customId[0], "fname_custom1" );
        $this->type( "custom_".$customId[1], "mname_custom1" );
        $this->type( "custom_".$customId[2], "lname_custom1" );
        
        $this->type( "middle_name", "xyz" );
        $this->click( "name=gender value=2");
        $this->select("participant_role","value=2");
        
        $this->click("_qf_Register_upload-bottom");
        $this->waitForElementPresent( "_qf_Participant_1_next-Array" );
        
        $this->type("email-5",$email2);
        $this->type("first_name",$participantfname);
        $this->type("last_name",$participantlname);
        $this->type("street_address-1","participant street addres");
        $this->type("city-1","pune");
        $this->type("postal_code-1","2354");
        $this->select("state_province-1","value=1001");
        
        $this->type( "current_employer", "ABCD" ); 
        $this->type( "job_title", "Potato picker" ); 
        
        $this->type( "custom_".$customId[0], "participant_custom1" );
        $this->type( "custom_".$customId[1], "participant_custom1" );
        $this->type( "custom_".$customId[2], "participant_custom1" );
        
        $this->type( "street_address-Primary", "Primary street address" );
        $this->type( "city-Primary", "primecity" );
        $this->type( "phone-Primary-1", "98667764" );
        $this->type( "postal_code-Primary", "6548" );
        
        $this->type( "nick_name", "Nick1" ); 
        $this->type( "url-1", "http://www.part.com" ); 
        
        $this->click("_qf_Participant_1_next-Array");
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        $this->click("_qf_Confirm_next-bottom");
        $this->waitForPageToLoad('30000');
    }
    
    function _testEventRegistrationAfterRemoving( $eventPageId , $customId , $firstName2 , $lastName2 , $participantfname2 , $participantlname2 , $email3 , $email4 )
    {
        $this->open($this->sboxPath . 'civicrm/event/register?id=' .$eventPageId.'&reset=1');
        $this->waitForElementPresent( "_qf_Register_upload-bottom" );
        $this->select("additional_participants","value=1");
        
        $this->type("email-5",$email4);
        $this->type("first_name",$firstName2);
        $this->type("last_name",$lastName2);
        $this->type("street_address-1","Test street addres");
        $this->type("city-1","Mumbai");
        $this->type("postal_code-1","2354");
        $this->select("state_province-1","value=1001");
        
        // Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        
        //Billing Info
        $this->type( "billing_first_name", $firstName2 . 'billing' );
        $this->type( "billing_last_name", $lastName2 . 'billing'  );
        $this->type( "billing_street_address-5", "0121 Mount Highschool." );
        $this->type( " billing_city-5", "Shangai" );
        $this->select( "billing_country_id-5", "value=1228" );
        $this->select( "billing_state_province_id-5", "value=1004" );
        $this->type( "billing_postal_code-5", "94129" );  
        
        $this->type( "current_employer", "ABCD" ); 
        $this->type( "job_title", "Painter" ); 
        
        $this->type( "nick_name", "Nickkk" ); 
        $this->type( "url-1", "http://www.testweb.com" ); 
        
        $this->type( "street_address-Primary", "Primary street address" );
        $this->type( "city-Primary", "primecity" );
        $this->type( "phone-Primary-1", "9866776422" );
        $this->type( "postal_code-Primary", "6534" );
        
        $this->type( "custom_".$customId[0], "fname_custom1" );
        $this->type( "custom_".$customId[1], "mname_custom1" );
        $this->type( "custom_".$customId[2], "lname_custom1" );
        
        $this->type( "middle_name", "xyz" );
        $this->click( "name=gender value=2");
        $this->select("participant_role","value=2");
        
        $this->click("_qf_Register_upload-bottom");
        $this->waitForElementPresent( "_qf_Participant_1_next-Array" );
        
        $this->type("email-5",$email3);
        $this->type("first_name",$participantfname2);
        $this->type("last_name",$participantlname2);
        $this->type("street_address-1","participant street addres");
        $this->type("city-1","pune");
        $this->type("postal_code-1","2354");
        $this->select("state_province-1","value=1001");
        
        $this->type( "current_employer", "ABCD" ); 
        $this->type( "job_title", "BATCHER" ); 
        
        $this->click("_qf_Participant_1_next-Array");
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        $this->click("_qf_Confirm_next-bottom");
        
    }
}
