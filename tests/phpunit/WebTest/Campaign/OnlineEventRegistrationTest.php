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


 
class WebTest_Campaign_OnlineEventRegistrationTest extends CiviSeleniumTestCase {

    protected function setUp()
    {
        parent::setUp();
    }

    function testCreateCampaign()
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );

        // Logging in. Remember to wait for page to load. In most cases,
        // you can rely on 30000 as the value that allows your test to pass, however,
        // sometimes your test might fail because of this. In such cases, it's better to pick one element
        // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
        // page contents loaded and you can continue your test execution.
        $this->webtestLogin();

        // Create new group
        $title = substr(sha1(rand()), 0, 7);
        $groupName = $this->WebtestAddGroup( );
      
        // Adding contact
        // We're using Quick Add block on the main page for this.
        $firstName1 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName1, "Smith", "$firstName1.smith@example.org" );

        // add contact to group
        // visit group tab
        $this->click("css=li#tab_group a");
        $this->waitForElementPresent("group_id");
        
        // add to group
        $this->select("group_id", "label=$groupName");
        $this->click("_qf_GroupContact_next");
        $this->waitForPageToLoad("30000");
        
        $firstName2 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName2, "John", "$firstName2.john@example.org" );
        
        // add contact to group
        // visit group tab
        $this->click("css=li#tab_group a");
        $this->waitForElementPresent("group_id");

        // add to group
        $this->select("group_id", "label=$groupName");
        $this->click("_qf_GroupContact_next");
        $this->waitForPageToLoad("30000");

        // Enable CiviCampaign module if necessary
        $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent("_qf_Component_next-bottom");
        $enabledComponents = $this->getSelectOptions("enableComponents-t");
        if (! in_array( "CiviCampaign", $enabledComponents ) ) {
            $this->addSelection("enableComponents-f", "label=CiviCampaign");
            $this->click("//option[@value='CiviCampaign']");
            $this->click("add");
            $this->click("_qf_Component_next-bottom");
            $this->waitForPageToLoad("30000");          
            $this->assertTrue($this->isTextPresent("Your changes have been saved."));    
        }

        // add the required Drupal permission
        $permissions = array('edit-2-administer-civicampaign', 'edit-1-register-for-events');
        $this->changePermissions( $permissions );

        // Go directly to the URL of the screen that you will be testing
        $this->open($this->sboxPath . "civicrm/campaign/add?reset=1");

        // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent("_qf_Campaign_upload-bottom");

        // Let's start filling the form with values.
        $campaignTitle = "Campaign $title";
        $this->type( "title", $campaignTitle );

        // select the campaign type
        $this->select("campaign_type_id", "value=2");

        // fill in the description
        $this->type("description", "This is a test campaign");

        // include groups for the campaign
        $this->addSelection("includeGroups-f", "label=$groupName");
        $this->click("//option[@value=4]");
        $this->click("add");
        
        // fill the end date for campaign
        $this->webtestFillDate("end_date", "+1 year");
      
        // select campaign status
        $this->select("status_id", "value=2");

        // click save
        $this->click("_qf_Campaign_upload-bottom");
        $this->waitForPageToLoad("30000");
      
        $this->assertTrue($this->isTextPresent("Campaign Campaign $title has been saved."), 
                          "Status message didn't show up after saving campaign!");
      
        $this->waitForElementPresent("//div[@id='campaignList']/div[@class='dataTables_wrapper']/table/tbody/tr/td[text()='{$campaignTitle}']/../td[1]");
        $id = (int) $this->getText("//div[@id='campaignList']/div[@class='dataTables_wrapper']/table/tbody/tr/td[text()='{$campaignTitle}']/../td[1]");
 
        $this->onlineParticipantAddTest( $campaignTitle, $id );
    }
  
   function onlineParticipantAddTest( $campaignTitle, $id )
   {
       // We need a payment processor
       $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
       $this->webtestAddPaymentProcessor($processorName);
       
       // Go directly to the URL of the screen that you will be testing (New Event).
       $this->open($this->sboxPath . "civicrm/event/add?reset=1&action=add");
       
       $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
       $eventDescription = "Here is a description for this conference.";
       $this->_testAddEventInfo( $id, $eventTitle, $eventDescription );
       
       $streetAddress = "100 Main Street";
       $this->_testAddLocation( $streetAddress );
       
       $this->_testAddFees( false, false, $processorName );
       
       // intro text for registration page
       $registerIntro = "Fill in all the fields below and click Continue.";
       $multipleRegistrations = true;
       $this->_testAddOnlineRegistration( $registerIntro, $multipleRegistrations );
       
       $eventInfoStrings = array( $eventTitle, $eventDescription, $streetAddress );
       $this->_testVerifyEventInfo( $eventTitle, $eventInfoStrings );
       
       $registerStrings = array("250.00 Member", "325.00 Non-member", $registerIntro );
       $registerUrl = $this->_testVerifyRegisterPage( $registerStrings );
       
       $numberRegistrations = 3;
       $anonymous = true;
       $this->_testOnlineRegistration( $campaignTitle, $registerUrl, $numberRegistrations, $anonymous );
   }

   function _testAddEventInfo( $id, $eventTitle, $eventDescription ) 
   {
       // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
       // button at the end of this page to show up, to make sure it's fully loaded.
       $this->waitForElementPresent("_qf_EventInfo_upload-bottom");
       
       // Let's start filling the form with values.
       $this->select("event_type_id", "value=1");

       // select campaign
       $this->click("campaign_id");
       $this->select("campaign_id", "value=$id" );
       
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
  
   function _testAddOnlineRegistration( $registerIntro, $multipleRegistrations=false )
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
  
   function _testVerifyEventInfo( $eventTitle, $eventInfoStrings )
   {
       // verify event input on info page
       // start at Manage Events listing
       $this->open($this->sboxPath . "civicrm/event/manage?reset=1");
       $this->click("link=$eventTitle");
      
       $this->waitForPageToLoad('30000');
       // Look for Register button
       $this->waitForElementPresent("link=Register Now");
      
       // Check for correct event info strings
       $this->assertStringsPresent( $eventInfoStrings );
   }

   function _testVerifyRegisterPage( $registerStrings )
   {
       // Go to Register page and check for intro text and fee levels
       $this->click("link=Register Now");
       $this->waitForElementPresent("_qf_Register_upload-bottom");
       $this->assertStringsPresent( $registerStrings );
       return $this->getLocation();
   }
  
   function _testOnlineRegistration( $campaignTitle, $registerUrl, $numberRegistrations=1, $anonymous=true )
   {
       if ( $anonymous ){
           $this->open($this->sboxPath . "civicrm/logout?reset=1");
           $this->waitForPageToLoad('30000');          
       }
       $this->open($registerUrl);

       $this->select("additional_participants", "value=" . $numberRegistrations);
       $email = "smith" . substr(sha1(rand()), 0, 7) . "@example.org";
       $this->type("email-5", $email );

       $this->select("credit_card_type", "value=Visa");
       $this->type("credit_card_number", "4111111111111111");
       $this->type("cvv2", "000");
       $this->select("credit_card_exp_date[M]", "value=1");
       $this->select("credit_card_exp_date[Y]", "value=2020");
       $this->type("billing_first_name", "Jane");
       $this->type("billing_last_name", "Smith" . substr(sha1(rand()), 0, 7));
       $this->type("billing_street_address-5", "15 Main St.");
       $this->type(" billing_city-5", "San Jose");
       $this->select("billing_country_id-5", "value=1228");
       $this->select("billing_state_province_id-5", "value=1004");
       $this->type("billing_postal_code-5", "94129");
       
       $this->click("_qf_Register_upload-bottom");
      
       if ( $numberRegistrations > 1 ){
           for ($i = 1; $i <= $numberRegistrations; $i++){
               $this->waitForPageToLoad('30000');
               // Look for Skip button
               $this->waitForElementPresent("_qf_Participant_{$i}_next_skip-Array");
               $this->type("email-5", "smith" . substr(sha1(rand()), 0, 7) . "@example.org" );
               $this->click("_qf_Participant_{$i}_next");
           }
       }

       $this->waitForPageToLoad('30000');
       $this->waitForElementPresent("_qf_Confirm_next-bottom");
       $confirmStrings = array("Event Fee(s)", "Billing Name and Address", "Credit Card Information");
       $this->assertStringsPresent( $confirmStrings );
       $this->click("_qf_Confirm_next-bottom");
       $this->waitForPageToLoad('30000');
       $thankStrings = array("Thank You for Registering", "Event Total", "Transaction Date");
       $this->assertStringsPresent( $thankStrings );
       
       $this->open( $this->sboxPath );
       $this->webtestLogin();
       $this->open( $this->sboxPath . 'civicrm/event/search?reset=1' );
       $this->waitForElementPresent( "_qf_Search_refresh" );
       
       $this->type( 'sort_name', $email );
       $this->click( "_qf_Search_refresh" );
       $this->waitForElementPresent( "_qf_Search_next_print" );
       $this->click( "xpath=//div[@id='participantSearch']/table/tbody/tr/td[11]/span/a[text()='Edit']" );
       $this->waitForElementPresent( "_qf_Participant_cancel-bottom" );
       $this->assertTrue($this->isTextPresent("$campaignTitle"));
   }
   
}
