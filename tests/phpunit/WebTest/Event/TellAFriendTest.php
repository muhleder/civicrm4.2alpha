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



class WebTest_Event_TellAFriendTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testAddEvent( )
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin( true );
        
        // Go directly to the URL of the screen that you will be testing (New Event).
        $this->open($this->sboxPath . "civicrm/event/add?reset=1&action=add");
        
        $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
        $eventDescription = "Here is a description for this conference.";
        $this->_testAddEventInfo( $eventTitle, $eventDescription );
        
        $streetAddress = "100 Main Street";
        $this->_testAddLocation( $streetAddress );
        
        // intro text for registration page
        $registerIntro = "Fill in all the fields below and click Continue.";
        $multipleRegistrations = true;
        $this->_testAddOnlineRegistration( $registerIntro, $multipleRegistrations );
        
        // enable tell a friend
        $subject     = "$eventTitle Tell A Friend";
        $thankYouMsg = "$eventTitle Tell A Friend Test Thankyou Message";
        $this->_testAddTellAFriend( $subject, $thankYouMsg, $eventTitle );
                
        // get the url for registration
        $this->waitForElementPresent( "xpath=//div[@id='event_status_id']//div[@class='dataTables_wrapper']/table/tbody//tr/td[1]/a[text()='$eventTitle']" );
        $this->click( "link=$eventTitle" );
        $this->waitForElementPresent( "link=Register Now" );
        $this->click( "link=Register Now" );
        $this->waitForElementPresent( "_qf_Register_upload-bottom" );
        $registerUrl = $this->getLocation( );
        
        // give permissions for event registration
        $permission = array('edit-1-register-for-events');
        $this->changePermissions( $permission );
        
        // register as an anonymous user
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->waitForElementPresent( 'edit-submit' ); 
        $this->open( $registerUrl );
        $this->waitForElementPresent( '_qf_Register_upload-bottom' );
        
        $firstName = substr(sha1(rand()), 0, 7);
        $this->type( 'email-5', "$firstName@Anderson.com" );
        $this->click( '_qf_Register_upload-bottom' );
        $this->waitForPageToLoad('30000');
        $this->click( "css=div.crm-event-thankyou-form-block div#tell-a-friend a" );
        $this->waitForElementPresent( '_qf_Form_cancel' );
        
        $this->type( 'suggested_message', '$subject Test Message for the recipients' );
        
        // fill the recipients
        $firstName1 = 'John' . substr(sha1(rand()), 0, 7);
        $lastName1  = substr(sha1(rand()), 0, 7);
        $this->type( 'friend_1_first_name', "$firstName1" );
        $this->type( 'friend_1_last_name', "$lastName1" );
        $this->type( 'friend_1_email', "$firstName1@$lastName1.com" );
        
        $firstName2 = 'Smith' . substr(sha1(rand()), 0, 7);
        $lastName2  = substr(sha1(rand()), 0, 7);
        $this->type( 'friend_2_first_name', "$firstName2" );
        $this->type( 'friend_2_last_name', "$lastName2" );
        $this->type( 'friend_2_email', "$firstName2@$lastName2.com" );
        
        $firstName3 = 'James' . substr(sha1(rand()), 0, 7);
        $lastName3  = substr(sha1(rand()), 0, 7);
        $this->type( 'friend_3_first_name', "$firstName3" );
        $this->type( 'friend_3_last_name', "$lastName3" );
        $this->type( 'friend_3_email', "$firstName3@$lastName3.com" );
        
        $this->click( '_qf_Form_submit' );
        $this->waitForPageToLoad('30000');
        $this->assertTrue( $this->isTextPresent( $thankYouMsg ) );
        
        // Log in using webtestLogin() method
        $this->open( $this->sboxPath );
        $this->webtestLogin();
        
        // get all friends contact id
        $this->open($this->sboxPath . "civicrm/contact/search?reset=1" );
        $this->waitForElementPresent( '_qf_Basic_refresh ' );
        $this->type( 'sort_name', $firstName1 );
        $this->click( '_qf_Basic_refresh ' );
        $this->waitForPageToLoad( '30000' );
        $this->click ( "xpath=//div[@class='crm-search-results']/table/tbody/tr/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
                
        $this->open($this->sboxPath . "civicrm/contact/search?reset=1" );
        $this->waitForElementPresent( '_qf_Basic_refresh ' );
        $this->type( 'sort_name', $firstName2 );
        $this->click( '_qf_Basic_refresh ' );
        $this->waitForPageToLoad( '30000' );
        $this->click ( "xpath=//div[@class='crm-search-results']/table/tbody/tr/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
                
        $this->open($this->sboxPath . "civicrm/contact/search?reset=1" );
        $this->waitForElementPresent( '_qf_Basic_refresh ' );
        $this->type( 'sort_name', $firstName3 );
        $this->click( '_qf_Basic_refresh ' );
        $this->waitForPageToLoad( '30000' );
        $this->click ( "xpath=//div[@class='crm-search-results']/table/tbody/tr/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
                
        $this->open($this->sboxPath . "civicrm/contact/search?reset=1" );
        $this->waitForElementPresent( '_qf_Basic_refresh ' );
        $this->type( 'sort_name', $firstName );
        $this->click( '_qf_Basic_refresh ' );
        $this->waitForElementPresent( 'Print' );
        $this->assertTrue( $this->isTextPresent( '1 Contact' ) );
        
        // Verify Activity created
        $this->open( $this->sboxPath . "civicrm/activity/search?reset=1" );
        $this->waitForElementPresent( '_qf_Search_refresh' );
        $this->type( 'sort_name', $firstName1 );
        $this->click ( '_qf_Search_refresh' );
        $this->waitForElementPresent( "_qf_Search_next_print" );
        $this->click ( "xpath=//div[@class='crm-search-results']//table[@class='selector']/tbody/tr[2]/td[9]/span/a[text()='View']" );
        $this->waitForElementPresent( '_qf_Activity_cancel-bottom' );
       
        $this->verifyText( "xpath=//table[@class='crm-info-panel']/tbody/tr[1]/td[2]", 
                           preg_quote( "$firstName@Anderson.com" ) );
        
        $this->verifyText( "xpath=//table[@class='crm-info-panel']/tbody/tr[2]/td[2]/a[1]", 
                           preg_quote( "$lastName1, $firstName1" ) );
        $this->verifyText( "xpath=//table[@class='crm-info-panel']/tbody/tr[2]/td[2]/a[2]", 
                           preg_quote( "$lastName2, $firstName2" ) );
        $this->verifyText( "xpath=//table[@class='crm-info-panel']/tbody/tr[2]/td[2]/a[3]", 
                           preg_quote( "$lastName3, $firstName3" ) );
             
        $this->verifyText( "xpath=//table[@class='crm-info-panel']/tbody/tr[4]/td[2]", 
                           preg_quote( "Tell a Friend:" ) );
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
        $streetAddress = "100 Main Street";
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
    
    function _testAddOnlineRegistration($registerIntro, $multipleRegistrations=false)
    {
        // Go to Online Registration tab
        $this->click("link=Online Registration");
        $this->waitForElementPresent("_qf_Registration_upload-bottom");
        
        $this->check("is_online_registration");
        $this->assertChecked("is_online_registration");
        if ( $multipleRegistrations ){
            $this->check("is_multiple_registrations");
            $this->assertChecked("is_multiple_registrations");
        }
        
        $this->fillRichTextField("intro_text", $registerIntro);
        
        // enable confirmation email
        $this->click("CIVICRM_QFID_1_2");
        $this->type("confirm_from_name", "Jane Doe");
        $this->type("confirm_from_email", "jane.doe@example.org");
        
        $this->click("_qf_Registration_upload-bottom");
        $this->waitForPageToLoad("30000");
        $this->waitForTextPresent("'Registration' information has been saved.");
    }
    
    function _testAddTellAFriend( $subject, $thankYouMsg, $eventTitle ) 
    {
        // Go to Tell A Friend Tab
        $this->click( 'link=Tell a Friend' );
        $this->waitForElementPresent( '_qf_Event_cancel-bottom' );
        
        // Enable tell a friend feature
        $this->check( 'tf_is_active' );
        $this->waitForElementPresent( 'tf_thankyou_text' );
        
        // Modify the messages
        $this->type( 'intro', "This is $subject Test intro text" );
        $this->type( 'suggested_message', "$subject Test Message. This is amazing!" );
        
        $this->type( 'tf_thankyou_title', 'Test thank you title' );
        $this->type( 'tf_thankyou_text', $thankYouMsg );
        
        $this->click( '_qf_Event_upload_done-bottom' );
        $this->waitForElementPresent( "xpath=//div[@id='event_status_id']//div[@class='dataTables_wrapper']/table/tbody//tr/td[1]/a[text()='$eventTitle']" );
    }
}
