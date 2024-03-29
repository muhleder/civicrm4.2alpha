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

class WebTest_Contact_AdvancedSearchedRelatedContactTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testSearchRelatedContact()
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
        $this->webtestAddPaymentProcessor($processorName);
        
        // Go directly to the URL of the screen that you will be testing (New Event).
        $this->open($this->sboxPath . "civicrm/event/add?reset=1&action=add");
        
        $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
        $eventDescription = "Here is a description for this conference.";
        $this->_testAddEventInfo( $eventTitle, $eventDescription );
        
        $streetAddress = "100 Main Street";
        $this->_testAddLocation( $streetAddress );
        
        $this->_testAddFees( false, false, $processorName );
        $this->open($this->sboxPath . "civicrm/event/manage?reset=1");
        $this->waitForPageToLoad("300000");
        $this->type( 'title', $eventTitle );
        $this->click( '_qf_SearchEvent_refresh' );
        $this->waitForPageToLoad("300000");
        $Id =  explode( '_', $this->getAttribute("xpath=//div[@id='event_status_id']/div[3]/table/tbody/tr@id") );
        $eventId = $Id[1];
        
        $params = array( 'label_a_b'       => 'Owner of '.rand( ),
                         'label_b_a'       => 'Belongs to '.rand( ),
                         'contact_type_a'  => 'Individual',
                         'contact_type_b'  => 'Individual',
                         'description'     => 'The company belongs to this individual' );
        
        $this->webtestAddRelationshipType( $params );
        $relType = $params['label_b_a'];
        
        $firstName = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName, "Anderson", "$firstName@anderson.name" );
        $sortName    = "Anderson, $firstName";
        $displayName = "$firstName Anderson";
        
        //create a New Individual
        $firstName1 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName1, "Andy", "$firstName1@andy.name" );
        $sortName1    = "Andy, $firstName1";
        $displayName1 = "$firstName1 Andy";
        $this->_testAddRelationship($sortName1,$sortName, $relType ); 
        
        $firstName2 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName2, "David", "$firstName2@andy.name" );
        $sortName2    = "David, $firstName2";
        $displayName2 = "$firstName2 David";
        $this->_testAddRelationship($sortName2,$sortName, $relType );  
        
        $this->open($this->sboxPath . "civicrm/contact/search?reset=1" );
        $this->waitForElementPresent("_qf_Basic_refresh");
        $this->type("sort_name", $sortName );
        $this->select("contact_type", "value=Individual" );
        $this->click("_qf_Basic_refresh");
        $this->waitForPageToLoad("300000"); 
        $this->waitForElementPresent("xpath=//form[@id='Basic']/div[3]/div/div[2]/table/tbody/tr/");
        
        // click through to the Relationship view screen
        $this->click("xpath=//form[@id='Basic']/div[3]/div/div[2]/table/tbody/tr/td[11]/span/a[text()='View']");
        $this->waitForPageToLoad("300000"); 
        $this->click("css=li#tab_participant a");
        
        // wait for add Event link
        $this->waitForElementPresent("link=Add Event Registration");
        $this->click("link=Add Event Registration");
        $this->waitForElementPresent("_qf_Participant_upload-bottom" );
        $this->select("event_id", "value={$eventId}" );
        $this->click("_qf_Participant_upload-bottom" );
        $this->waitForPageToLoad("300000"); 
        
        $this->open($this->sboxPath . "civicrm/contact/search/advanced?reset=1");
        $this->waitForPageToLoad('30000');        
        
        $this->type("sort_name", $sortName);
        $this->click('_qf_Advanced_refresh');
        $this->waitForPageToLoad('60000');
        
        $this->assertTrue( $this->isTextPresent( '1 Contact' ) );

        $this->click('css=div.crm-advanced_search_form-accordion div.crm-accordion-header');
        $this->select("component_mode", "label=Related Contacts" );
        $this->select("display_relationship_type", $relType);
        $this->click('_qf_Advanced_refresh');
        $this->waitForPageToLoad('60000');

        $this->assertTrue( $this->isTextPresent( '2 Contacts' ) ); 

        $this->select("task", "label=Add Contacts to Group");

        $this->click('Go');
        $this->waitForPageToLoad('30000');
        
        $this->click('CIVICRM_QFID_1_4');
        
        $groupName = "Group " . substr(sha1(rand()), 0, 7);
        $this->type('title', $groupName);
        
        $this->click("_qf_AddToGroup_next-bottom");
        $this->waitForPageToLoad('30000');

        $this->assertTrue( $this->isTextPresent( "Added Contact(s) to $groupName" ) );
        $this->assertTrue( $this->isTextPresent( 'Total Selected Contact(s): 2' ) );
        $this->assertTrue( $this->isTextPresent( 'Total Contact(s) added to group: 2' ) );
        $this->_testSearchResult( $relType );
        
    }
    
    function _testAddEventInfo( $eventTitle, $eventDescription ) {
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
    
    function _testAddLocation( $streetAddress ) {
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
    
    function _testAddFees( $discount=false, $priceSet=false, $processorName = "PP Pro" ){
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
    
    function _testAddRelationship( $ContactName, $relatedName, $relType ) {
        
        $this->open($this->sboxPath . "civicrm/contact/search?reset=1" );
        $this->waitForElementPresent("_qf_Basic_refresh");
        $this->type("sort_name", $ContactName );
        $this->select("contact_type", "value=Individual" );
        $this->click("_qf_Basic_refresh");
        $this->waitForPageToLoad("300000"); 
        $this->waitForElementPresent("xpath=//form[@id='Basic']/div[3]/div/div[2]/table/tbody/tr/");
        
        // click through to the Contribution view screen
        $this->click("xpath=//form[@id='Basic']/div[3]/div/div[2]/table/tbody/tr/td[11]/span/a[text()='View']");
        $this->waitForPageToLoad("300000"); 
        
        $this->click("css=li#tab_rel a");
        
        // wait for add Relationship link
        $this->waitForElementPresent('link=Add Relationship');
        $this->click('link=Add Relationship');
        
        //choose the created relationship type 
        $this->waitForElementPresent("relationship_type_id");
        $this->select('relationship_type_id', "label={$relType}");
        
        //fill in the individual
        $this->typeKeys( 'contact_1', $relatedName );
        $this->fireEvent("contact_1", "focus");
        $this->waitForElementPresent("css=div.ac_results-inner li");
        $this->click("css=div.ac_results-inner li");
        
        $this->waitForElementPresent("quick-save");
        
        //fill in the relationship start date
        $this->webtestFillDate('start_date' , '-2 year' );
        
        $description = "Well here is some description !!!!";
        $this->type("description", $description );
        
        //save the relationship
        //$this->click("_qf_Relationship_upload");
        $this->click("quick-save");
        $this->waitForElementPresent("current-relationships");
        
        //check the status message
        $this->assertTrue($this->isTextPresent("1 new relationship record created."));
        
        $this->waitForElementPresent("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
        $this->click("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
        
        $this->waitForPageToLoad("300000"); 
        $this->webtestVerifyTabularData(
                                        array(
                                              'Description'         => $description,
                                              'Status'	          => 'Enabled'
                                              )
                                        );
        $this->assertTrue( $this->isTextPresent( $relType ) );
    }
    
    function _testSearchResult( $relType ) {
        
        //search related contact using Advanced Search
        $this->open($this->sboxPath . "civicrm/contact/search/advanced?reset=1" );
        $this->waitForPageToLoad("300000");
        $this->waitForElementPresent( "_qf_Advanced_refresh" ); 
        $this->select("component_mode", "label=Related Contacts");
        $this->select("display_relationship_type", "label={$relType}");
        $this->click("CiviEvent");
        $this->waitForElementPresent( "event_type" ); 
        $this->type("event_type", "Conference");
        $this->click("_qf_Advanced_refresh");
        $this->waitForPageToLoad("300000");
        $this->assertTrue( $this->isTextPresent( '2 Contacts' ) ); 
    }
}
