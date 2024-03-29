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
 
class WebTest_Event_ChangeParticipantStatus extends CiviSeleniumTestCase {

    protected function setUp()
    {
        parent::setUp();
    }
    
    function testParticipationAdd()
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // Adding contact with randomized first name (so we can then select that contact when creating event registration)
        // We're using Quick Add block on the main page for this.
        $firstName1 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName1, 'Anderson', true );
        $sortName1  = "Anderson, $firstName1";
        $this->addParticipant( $firstName1 );
    
        $firstName2 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName2, 'Anderson', true );
        $sortName2  = "Anderson, $firstName2";
        $this->addParticipant( $firstName2 );

        // Search the participants
        $this->open( $this->sboxPath . 'civicrm/event/search?reset=1' );
        $this->waitForElementPresent( '_qf_Search_refresh' );
        
        $eventName = 'Rain';
        $this->click( "event_name" );
        $this->typeKeys( "event_name", $eventName );
        $this->waitForElementPresent( "css=div.ac_results-inner li" );
        $this->click( "css=div.ac_results-inner li" );
        $this->assertContains( $eventName, $this->getValue( "event_name" ), "autocomplete expected $eventName but didn’t find it in " . $this->getValue( "event_name" ) );
        $this->click( '_qf_Search_refresh' );
     
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName1']" );
        $id1 = $this->getAttribute( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName1']/../../td[1]/input@id" );
        $this->click( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName1']/../../td[1]/");
        $this->click( $id1 );

        $id2 = $this->getAttribute( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName2']/../../td[1]/input@id" );
        $this->click( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName2']/../../td[1]/");
        $this->click( $id2 );

        // Change participant status for selected participants
        $this->select( 'task', "label=Change Participant Status" );
        $this->click( 'Go' );
        $this->waitForElementPresent( '_qf_ParticipantStatus_next' );

        $this->select( 'status_change', "label=Attended" );
        $this->click( '_qf_ParticipantStatus_next' );
        $this->waitForElementPresent( 'Go' );
        $this->assertTrue( $this->isTextPresent( 'The updates have been saved.' ), 
                           "Status message didn't show up after saving!" );

        // Verify the changed status
        $this->open( $this->sboxPath . 'civicrm/event/search?reset=1' );
        $this->waitForElementPresent('_qf_Search_refresh' );
        $this->type( 'sort_name', $firstName1 );
        $this->click( '_qf_Search_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName1']" );
        $this->click( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName1']/../../td[11]/span/a[text()='View']" );
        $this->waitForElementPresent( '_qf_ParticipantView_cancel-bottom' );
        $this->webtestVerifyTabularData( array( 'Status' => 'Attended' ) );

        $this->open( $this->sboxPath . 'civicrm/event/search?reset=1' );
        $this->waitForElementPresent('_qf_Search_refresh' );
        $this->type( 'sort_name', $firstName2 );
        $this->click( '_qf_Search_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName2']" );
        $this->click( "xpath=//div[@id='participantSearch']/table/tbody//tr/td[3]/a[text()='$sortName2']/../../td[11]/span/a[text()='View']" );
        $this->waitForElementPresent( '_qf_ParticipantView_cancel-bottom' );
        $this->webtestVerifyTabularData( array( 'Status' => 'Attended' ) );
    }

    function addParticipant ( $firstName ) 
    {
        // Go directly to the URL of the screen that you will be testing (Register Participant for Event-standalone).
        $this->open( $this->sboxPath . 'civicrm/participant/add?reset=1&action=add&context=standalone' );
        
        // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent( '_qf_Participant_upload-bottom' );
        
        // Let's start filling the form with values.
        // Type contact last name in contact auto-complete, wait for dropdown and click first result
        $this->webtestFillAutocomplete( $firstName );
        
        // Select event. Based on label for now.
        $this->select( 'event_id', "label=regexp:Rain-forest Cup Youth Soccer Tournament." );
        
        // Select role
        $this->click( 'role_id[2]' );
        
        // Choose Registration Date.
        // Using helper webtestFillDate function.
        $this->webtestFillDate( 'register_date', 'now' );
        $today = date( 'F jS, Y', strtotime('now') );
        
        // Select participant status
        $this->select( 'status_id', 'value=1' );
        
        // Setting registration source
        $this->type( 'source', 'Event StandaloneAddTest Webtest' );
        
        // Since we're here, let's check of screen help is being displayed properly
        $this->assertTrue( $this->isTextPresent( 'Source for this registration (if applicable).' ) );
        
        // Select an event fee
        $feeHelp = 'Event Fee Level (if applicable).';
        $this->waitForTextPresent( $feeHelp );
        
        $this->click( "xpath=id('feeBlock')/table/tbody/tr[1]/td/table/tbody/tr/td[2]/label[1]" );
        
        // Select 'Record Payment'
        $this->click( 'record_contribution' );
        
        // Enter amount to be paid (note: this should default to selected fee level amount, s/b fixed during 3.2 cycle)
        $this->type( 'total_amount', '800' );
        
        // Select payment method = Check and enter chk number
        $this->select( 'payment_instrument_id', 'value=4' );
        $this->waitForElementPresent( 'check_number' );
        $this->type( 'check_number', '1044' );
        
        // Clicking save.
        $this->click( '_qf_Participant_upload-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        // Is status message correct?
        $this->assertTrue( $this->isTextPresent( "Event registration for $firstName Anderson has been added" ), 
                           "Status message didn't show up after saving!" );
        
        $this->waitForElementPresent( "xpath=//div[@id='Events']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
        //click through to the participant view screen
        $this->click( "xpath=//div[@id='Events']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
        $this->waitForElementPresent( '_qf_ParticipantView_cancel-bottom' );
                
        $this->webtestVerifyTabularData( 
                                        array( 'Event'            => 'Rain-forest Cup Youth Soccer Tournament',
                                               'Participant Role' => 'Attendee',
                                               'Status'           => 'Registered',
                                               'Event Source'     => 'Event StandaloneAddTest Webtest', 
                                               'Event Level'      => 'Tiny-tots (ages 5-8) - $ 800.00',
                                               )
                                         );
        
    }
}