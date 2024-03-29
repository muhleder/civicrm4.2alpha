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
require_once 'WebTest/Event/AddEventTest.php';

class WebTest_Event_PCPAddTest extends CiviSeleniumTestCase {
    
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
    protected function setUp()
    {
        parent::setUp( );
    }
    
    function testPCPAdd()
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        // visit event search page

        //give permissions to anonymous user 
        $permission = array('edit-1-profile-listings-and-forms','edit-1-access-all-custom-data','edit-1-register-for-events','edit-1-make-online-contributions');
        $this->changePermissions( $permission );
        
        // set domain values
        $domainNameValue = 'civicrm organization ';
        $firstName  = 'Ma'.substr( sha1( rand( ) ), 0, 4 );
        $lastName   = 'An'.substr( sha1( rand( ) ), 0, 7 );
        $middleName = 'Mid'.substr( sha1( rand( ) ), 0, 7 );
        $email = substr(sha1(rand()), 0, 7) . '@example.org';
        $this->open( $this->sboxPath . 'civicrm/admin/domain?action=update&reset=1' );
        $this->waitForElementPresent( '_qf_Domain_cancel-bottom' );
        $this->type( 'name', $domainNameValue );
        $this->type( 'email_name', $firstName );
        $this->type( 'email_address', $email );

        $this->click( '_qf_Domain_next_view-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        // a random 7-char string and an even number to make this pass unique
        $conHash = substr(sha1(rand()), 0, 7);
        $conRand = $contributionAmount = 1000;
        $contributionPageTitle = 'Contribution page for pcp' . $conHash;
        $conProcessorType = 'Dummy';
        $conAmountSection = true;
        $conPayLater =  true;
        $conOnBehalf = false;
        $conPledges = false;
        $conRecurring = false;
        $conMemberships = false;
        $conMemPriceSetId = null;
        $conFriend = false;
        $conProfilePreId  = null;
        $conProfilePostId = null;
        $conPremiums = false;
        $conWidget = false;
        $conPcp = false;
        $conIsAprovalNeeded = true;
                
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
        $this->webtestAddPaymentProcessor($processorName);
        
        //create contribution page for event pcp with campaign type as contribution
        $contributionPageId = $this->webtestAddContributionPage(          $conHash, 
                                                                          $conRand, 
                                                                          $contributionPageTitle, 
                                                                          array($processorName => $conProcessorType), 
                                                                          $conAmountSection, 
                                                                          $conPayLater, 
                                                                          $conOnBehalf,
                                                                          $conPledges, 
                                                                          $conRecurring, 
                                                                          $conMemberships, 
                                                                          $conMemPriceSetId,
                                                                          $conFriend, 
                                                                          $conProfilePreId,
                                                                          $conProfilePostId,
                                                                          $conPremiums, 
                                                                          $conWidget, 
                                                                          $conPcp, 
                                                                          false,
                                                                          $conIsAprovalNeeded);
        
        //event add for contribute campaign type
        $campaignType = 'contribute';
        $this->_testAddEventForPCP( $processorName, $campaignType, $contributionPageId, $firstName, $lastName, $middleName, $email );
        
        //event add for contribute campaign type
        $campaignType = 'event';
        $firstName  = 'Pa'.substr( sha1( rand( ) ), 0, 4 );
        $lastName   = 'Cn'.substr( sha1( rand( ) ), 0, 7 );
        $middleName = 'PCid'.substr( sha1( rand( ) ), 0, 7 );
        $email = substr(sha1(rand()), 0, 7) . '@example.org';
        $this->_testAddEventForPCP( $processorName, $campaignType, null, $firstName, $lastName, $middleName, $email );
               
    }

    function _testAddEventForPCP( $processorName, $campaignType, $contributionPageId = null, $firstName, $lastName, $middleName, $email )
    {   
        // Go directly to the URL of the screen that you will be testing (New Event).
        $this->open($this->sboxPath . "civicrm/event/add?reset=1&action=add");
        
        $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
        $eventDescription = "Here is a description for this conference.";
        $this->_testAddEventInfo( $eventTitle, $eventDescription );
        
        $streetAddress = "100 Main Street";
        $this->_testAddLocation( $streetAddress );
        
        $this->_testAddFees( false, false, $processorName );
      
        // intro text for registration page
        $registerIntro = "Fill in all the fields below and click Continue.";
        $multipleRegistrations = true;
        $this->_testAddOnlineRegistration( $registerIntro, $multipleRegistrations );
        
        $pageId  =$this->_testEventPcpAdd( $campaignType, $contributionPageId );
        $this->_testOnlineRegistration( $eventTitle, $pageId, $firstName, $lastName, $middleName, $email,'', $campaignType, true );
        
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
  
  function _testOnlineRegistration( $eventTitle, $pageId, $firstName, $lastName, $middleName, $email, $numberRegistrations=1, $campaignType, $anonymous=true )
  {
      $hash = substr(sha1(rand()), 0, 7);
      $contributionAmount = 600 ;     
      
      // registering online 
      if ( $anonymous ){
          $this->open($this->sboxPath . "civicrm/logout?reset=1");
          $this->waitForPageToLoad('30000');          
      }
      
      //participant registeration
      $firstNameParticipants = 'Jane' . substr(sha1(rand()), 0, 7);
      $lastNameParticipants = 'Smith'. substr(sha1(rand()), 0, 7);
      $emailParticipants = 'jane' . substr(sha1(rand()), 0, 7) . "@example.org";
          
      $registerUrl = "civicrm/event/register?id={$pageId}&reset=1";
      $this->open($this->sboxPath . $registerUrl);
      
      $this->select("additional_participants", "value=" . $numberRegistrations);
      $this->type("email-5", $emailParticipants);
      
      $this->select("credit_card_type", "value=Visa");
      $this->type("credit_card_number", "4111111111111111");
      $this->type("cvv2", "000");
      $this->select("credit_card_exp_date[M]", "value=1");
      $this->select("credit_card_exp_date[Y]", "value=2020");
      $this->type("billing_first_name", "{$firstNameParticipants}");
      $this->type("billing_last_name", "{$lastNameParticipants}");
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
              $this->type("email-5", "{$firstName}" . substr(sha1(rand()), 0, 7) . "@example.org" );
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
      
      //pcp creation via different user
      $this->open( $this->sboxPath . "civicrm/contribute/campaign?action=add&reset=1&pageId=".$pageId."&component=event" );
      $this->waitForElementPresent( "_qf_PCPAccount_next-bottom" );
      
      $cmsUserName = 'CmsUser'.substr( sha1( rand( ) ), 0, 7 );
 
      $this->type( "cms_name",  $cmsUserName);
      $this->click( "checkavailability" );
      $this->waitForTextPresent( 'This username is currently available' ); 
      $this->type( "first_name", $firstName );
      $this->type( "last_name", $lastName );  
      $this->type( "email-Primary", $email ); 
      $this->click( "_qf_PCPAccount_next-bottom" );   
      $this->waitForElementPresent( "_qf_Campaign_upload-bottom" );
      
      $pcpTitle = 'PCPTitle'.substr( sha1( rand( ) ), 0, 7 );
      $this->type( "title", $pcpTitle );
      $this->type( "intro_text", "Welcome Text $hash" );
      $this->type( "goal_amount", $contributionAmount );
      $this->click( "_qf_Campaign_upload-bottom" );
      $this->waitForPageToLoad("30000"); 
                
      //admin pcp approval
      //login to check contribution
      $this->open( $this->sboxPath );
      
      // Log in using webtestLogin() method
      $this->webtestLogin( );
      
      $this->open( $this->sboxPath . "civicrm/admin/pcp?reset=1&page_type=event" );
      $this->waitForElementPresent( "_qf_PCP_refresh" );
      $this->select( 'status_id',  'value=1' );
      $this->click( "_qf_PCP_refresh" );
      $this->waitForElementPresent( "_qf_PCP_refresh" );
      $id = explode( 'id=' ,$this->getAttribute("xpath=//div[@id='option11_wrapper']/table[@id='option11']/tbody//tr/td/a[text()='$pcpTitle']@href") );
      $pcpUrl = "civicrm/pcp/info?reset=1&id=$id[1]";
      $this->click( "xpath=//div[@id='option11_wrapper']/table[@id='option11']/tbody//tr/td/a[text()='$pcpTitle']/../../td[7]/span/a[text()='Approve']" );
      
      $this->waitForPageToLoad("30000");

      $this->open( $this->sboxPath . 'civicrm/logout?reset=1' );
      // Wait for Login button to indicate we've logged out.
      $this->waitForElementPresent( 'edit-submit' );
      
      $this->open( $this->sboxPath . $pcpUrl );
      $this->waitForElementPresent( "xpath=//div[@class='pcp-donate']/a" );
      $this->click( "xpath=//div[@class='pcp-donate']/a" );
      
      if( $campaignType == 'contribute' ){
          $this->waitForElementPresent( "_qf_Main_upload-bottom" );
      }
      elseif( $campaignType == 'event' ){
          $this->waitForElementPresent( '_qf_Register_upload-bottom' );
      }
      
      if( $campaignType == 'contribute' ) {
          $this->click("amount_other");
          $this->type("amount_other", $contributionAmount);
          $feeLevel = null;
      }
      elseif( $campaignType == 'event' ){
          $contributionAmount = '250.00';
      }
      
      $firstNameDonar = 'Andrew' .substr( sha1( rand( ) ), 0, 7 );
      $lastNameDonar = 'Roger' .substr( sha1( rand( ) ), 0, 7 );
      $middleNameDonar = 'Nicholas' .substr( sha1( rand( ) ), 0, 7 );
      $this->type( "email-5", $firstNameDonar . "@example.com" );
      
      $this->webtestAddCreditCardDetails( );
      $this->webtestAddBillingDetails( $firstNameDonar, $middleNameDonar, $lastNameDonar );
      
      if( $campaignType == 'contribute' ){
          $this->click("_qf_Main_upload-bottom");
      }
      elseif( $campaignType == 'event' ){
          $this->click( '_qf_Register_upload-bottom' );
      }      
      
      $this->waitForPageToLoad( '30000' );
      $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
      $this->click( "_qf_Confirm_next-bottom" );
      
      if( $campaignType == 'contribute' ){
          $this->waitForTextPresent( "Your transaction has been processed successfully" );
      }
      elseif( $campaignType == 'event' ){
          $this->waitForTextPresent( "Thank You for Registering" );
      }
      
      //login to check contribution
      $this->open( $this->sboxPath );
      
      // Log in using webtestLogin() method
      $this->webtestLogin( );

      if( $campaignType == 'event' ){
          $this->_testParticipantSearchEventName( $eventTitle, $lastNameDonar, $firstNameDonar, $firstName, $lastName, $contributionAmount ); 
      }
      elseif( $campaignType == 'contribute' ){    
          $this->_testSearchTest( $firstNameDonar, $lastNameDonar, $firstName, $lastName, $contributionAmount );
      }
  }
  
  function _testEventPcpAdd( $campaignType, $contributionPageId )
  {   
      $hash = substr(sha1(rand()), 0, 7);
      $isPcpApprovalNeeded = true;
      
      // fill in step 9 (Enable Personal Campaign Pages)
      $this->click( 'link=Personal Campaigns' );
      $this->waitForElementPresent( 'pcp_active' );
      $this->click( 'pcp_active' );
      $this->waitForElementPresent( '_qf_Event_upload-bottom' );
      
      $this->select( 'target_entity_type', "value={$campaignType}" );
     
      if( $campaignType=='contribute' && !empty($contributionPageId) )
          $this->select( 'target_entity_id', "value={$contributionPageId}" );
      
      if( !$isPcpApprovalNeeded ) $this->click('is_approval_needed');
      $this->type( 'notify_email', "$hash@example.name" );
      $this->select( 'supporter_profile_id', 'value=2' );
      $this->type( 'tellfriend_limit', 7 );
      $this->type( 'link_text', "'Create Personal Campaign Page' link text $hash" );
      
      $this->click( '_qf_Event_upload-bottom' );
      $this->waitForElementPresent( '_qf_Event_upload-bottom' );
      $this->waitForPageToLoad('30000');
      $text = "'Event' information has been saved.";
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      
      // parse URL to grab the contribution page id
      $elements = $this->parseURL( );
      $pageId = $elements['queryString']['id'];
      return $pageId;
  }

  function _testParticipantSearchEventName( $eventName, $lastNameDonar, $firstNameDonar, $firstNameCreator, $lastNameCreator, $amount ) 
  {   
      $sortName = $lastNameDonar . ', ' .$firstNameDonar;
      $this->open($this->sboxPath . "civicrm/event/search?reset=1");
      $this->waitForPageToLoad("30000");
      
      $this->type("event_name", $eventName);
      $this->click("event_name");
      $this->waitForElementPresent("css=div.ac_results-inner li");
      $this->click("css=div.ac_results-inner li");        
        
      $this->click( "_qf_Search_refresh" );
      $this->waitForPageToLoad("30000");
      
      $this->click("xpath=//div[@id='participantSearch']/table/tbody//tr/td[@class='crm-participant-sort_name']/a[text()='{$sortName}']/../../td[11]/span/a[text()='View']" );
      $this->waitForPageToLoad("30000");
      
      $this->waitForElementPresent("xpath=//table[@class='selector']/tbody/tr/td[8]/span/a[text()='View']");
      $this->click( "xpath=//table[@class='selector']/tbody/tr/td[8]/span/a[text()='View']" );  
      $this->waitForPageToLoad("30000");
      
      $this->webtestVerifyTabularData( 
                                      array( 'From'            => "{$firstNameDonar} {$lastNameDonar}",
                                             'Total Amount' => $amount,
                                             'Contribution Status'     => 'Completed', 
                                             'Soft Credit To'          => "{$firstNameCreator} {$lastNameCreator}",
                                             )
                                       );      
  }
  
  function _testSearchTest( $firstName, $lastName, $pcpCreatorFirstName, $pcpCreatorLastName, $amount )
  {     
      $sortName    = "$pcpCreatorLastName, $pcpCreatorFirstName";
      $displayName = "$firstName $lastName";
      
      // visit contact search page
      $this->open($this->sboxPath . "civicrm/contact/search?reset=1");
      $this->waitForPageToLoad("30000");
      
      // fill name as first_name
      $this->type("css=.crm-basic-criteria-form-block input#sort_name", $pcpCreatorFirstName);

      // click to search
      $this->click("_qf_Basic_refresh");
      $this->waitForPageToLoad("30000");
      
      $this->click( "xpath=//div[@class='crm-search-results']/table/tbody//tr/td[3]/a[text()='{$sortName}']" );
      $this->waitForPageToLoad("30000");
      
      $this->click("css=li#tab_contribute a");
      $this->waitForElementPresent( "xpath=//div[@id='Contributions']/div/form[@id='Search']/div[@class='view-content']/table[2]/tbody/tr[@id='rowid']/td/a[text()='$displayName']" );
      $this->click( "xpath=//div[@id='Contributions']/div/form[@id='Search']/div[@class='view-content']/table[2]/tbody/tr[@id='rowid']/td[7]/a[text()='View']" );
      $this->waitForPageToLoad("30000");
      
      $this->webtestVerifyTabularData( 
                                      array( 'From'            => "{$firstName} {$lastName}",
                                             'Total Amount' => $amount,
                                             'Contribution Status'     => 'Completed', 
                                             'Soft Credit To'          => "{$pcpCreatorFirstName} {$pcpCreatorLastName}",
                                             )
                                       );
  }  
}
