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
 
class WebTest_Member_OnlineMembershipAddPricesetTest extends CiviSeleniumTestCase
{

  protected function setUp()
  {
      parent::setUp();
  }

  function testAddPriceSet()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();

      // add the required Drupal permission
      
      $permissions = array('edit-1-make-online-contributions');
      $this->changePermissions( $permissions );
            
      $title            = substr(sha1(rand()), 0, 7);
      $setTitle         = "Membership Fees - $title";
      $usedFor          = 'Membership';
      $contributionType = 'Donation';
      $setHelp          = 'Select your membership options.';
      $this->_testAddSet( $setTitle, $usedFor, $contributionType, $setHelp );

      // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
      // which is where we are after adding Price Set.
      $elements = $this->parseURL( );
      $sid = $elements['queryString']['sid'];
      $this->assertType( 'numeric', $sid );

      $fields = array( "National Membership $title" => 'Radio',
                       "Local Chapter $title"       => 'CheckBox' );

      list( $memTypeTitle1, $memTypeTitle2 ) = $this->_testAddPriceFields( $fields, $validateStrings, false, $title, $sid );
      //var_dump($validateStrings);
      
      // load the Price Set Preview and check for expected values
      $this->_testVerifyPriceSet( $validateStrings, $sid );

      $contributionPageTitle = "Contribution Page $title";
      $paymentProcessor      = "Webtest Dummy $title";
      $this->webtestAddContributionPage( null, null, $contributionPageTitle,  array($paymentProcessor => 'Dummy'),
                                         true, false, false, false, false, true, $sid, false, 1, null );

      // Sign up for membership
      $registerUrl = $this->_testVerifyRegisterPage( $contributionPageTitle );

      $firstName = 'John_' . substr(sha1(rand()), 0, 7);
      $lastName  = 'Anderson_' . substr(sha1(rand()), 0, 7);
      $email     = "{$firstName}.{$lastName}@example.com";

      $contactParams = array( 'first_name' => $firstName,
                              'last_name'  => $lastName,
                              'email-5'    => $email );
      $this->_testSignUpOrRenewMembership( $registerUrl, $contactParams, $memTypeTitle1, $memTypeTitle2 );

      // Renew this membership
      $this->_testSignUpOrRenewMembership( $registerUrl, $contactParams, $memTypeTitle1, $memTypeTitle2, $renew = true );
  }

  function _testAddSet( $setTitle, $usedFor, $contributionType = null, $setHelp )
  {
      $this->open($this->sboxPath . 'civicrm/admin/price?reset=1&action=add');
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Set_next-bottom');

      // Enter Priceset fields (Title, Used For ...)
      $this->type('title', $setTitle);
      if ( $usedFor == 'Event' ){
          $this->check('extends[1]');
      } elseif ( $usedFor == 'Contribution') {
          $this->check('extends[2]');
      } elseif ( $usedFor == 'Membership') {
          $this->click('extends[3]');
          $this->waitForElementPresent( 'contribution_type_id' );
          $this->select( "css=select.form-select", "label={$contributionType}" );
      }

      $this->type('help_pre', $setHelp);

      $this->assertChecked('is_active', 'Verify that Is Active checkbox is set.');
      $this->click('_qf_Set_next-bottom');      

      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Field_next-bottom');
      $this->assertTrue( $this->isTextPresent( "Your Set '{$setTitle}' has been added. You can add fields to this set now." ) );
  }
  
  function _testAddPriceFields( &$fields, &$validateString, $dateSpecificFields = false, $title, $sid  )
  {
      $memTypeParams1 = $this->webtestAddMembershipType( );
      $memTypeTitle1  = $memTypeParams1['membership_type'];
      $memTypeId1     = explode( '&id=', $this->getAttribute( "xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[10]/span/a[3]@href" ) );
      $memTypeId1     = $memTypeId1[1];

      $memTypeParams2 = $this->webtestAddMembershipType( );
      $memTypeTitle2  = $memTypeParams2['membership_type'];
      $memTypeId2     = explode( '&id=', $this->getAttribute( "xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[10]/span/a[3]@href" ) );
      $memTypeId2     = $memTypeId2[1];

      $this->open( $this->sboxPath . "civicrm/admin/price/field?reset=1&action=add&sid={$sid}" );

      foreach ( $fields as $label => $type ) {
          $validateStrings[] = $label;
          
          $this->type('label', $label);
          $this->select('html_type', "value={$type}");
          
          switch ( $type ) {
          case 'Radio':
              $options = array( 1 => array( 'label'              => "$memTypeTitle1",
                                            'membership_type_id' => $memTypeId1,
                                            'amount'             => 100.00 ),
                                2 => array( 'label'              => "$memTypeTitle2", 
                                            'membership_type_id' => $memTypeId2,
                                            'amount'             => 50.00 ),
                                );
              $this->addMultipleChoiceOptions( $options, $validateStrings );
              break;

          case 'CheckBox':
              $options = array( 1 => array( 'label'              => "$memTypeTitle1",
                                            'membership_type_id' => $memTypeId1,
                                            'amount'             => 100.00 ),
                                2 => array( 'label'              => "$memTypeTitle2", 
                                            'membership_type_id' => $memTypeId2,
                                            'amount'             => 50.00 ),
                                );
              $this->addMultipleChoiceOptions( $options, $validateStrings );
              break;
              
          default:
              break;
          }
          $this->click( '_qf_Field_next_new-bottom' );
          $this->waitForPageToLoad( '30000' );
          $this->waitForElementPresent( '_qf_Field_next-bottom' );
          $this->assertTrue( $this->isTextPresent( "Price Field '{$label}' has been saved." ) );
      }
      return array( $memTypeTitle1, $memTypeTitle2 );
  }
  
  function _testVerifyPriceSet( $validateStrings, $sid )
  {
      // verify Price Set at Preview page
      // start at Manage Price Sets listing
      $this->open($this->sboxPath . 'civicrm/admin/price?reset=1');
      $this->waitForPageToLoad('30000');
      
      // Use the price set id ($sid) to pick the correct row
      $this->click("css=tr#row_{$sid} a[title='Preview Price Set']");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent('_qf_Preview_cancel-bottom');
      
      // Check for expected price set field strings
      $this->assertStringsPresent( $validateStrings );
  }
  
  function _testVerifyRegisterPage( $contributionPageTitle )
  {
      $this->open( $this->sboxPath . 'civicrm/admin/contribute?reset=1' );
      $this->waitForElementPresent( '_qf_SearchContribution_refresh' );
      $this->type( 'title', $contributionPageTitle );
      $this->click( '_qf_SearchContribution_refresh' );
      $this->waitForPageToLoad( '50000' );
      $id = $this->getAttribute("//div[@id='configure_contribution_page']//div[@class='dataTables_wrapper']/table/tbody/tr@id");
      $id = explode( '_', $id );
      $registerUrl = "civicrm/contribute/transact?reset=1&id=$id[1]";
      return $registerUrl;
  }

  function _testSignUpOrRenewMembership( $registerUrl, $contactParams, $memTypeTitle1, $memTypeTitle2, $renew = false )
  {
      $this->open( $this->sboxPath . 'civicrm/logout?reset=1' );
      $this->waitForPageToLoad( '30000' );

      $this->open( $this->sboxPath . $registerUrl );
      $this->waitForElementPresent( '_qf_Main_upload-bottom' );

      //build the membership dates.
      require_once 'CRM/Core/Config.php';
      require_once 'CRM/Utils/Array.php';
      require_once 'CRM/Utils/Date.php';
      $currentYear  = date( 'Y' );
      $currentMonth = date( 'm' );
      $previousDay  = date( 'd' ) - 1;
      $endYear      = ( $renew ) ? $currentYear + 2 : $currentYear + 1;
      $joinDate     = date('Y-m-d', mktime( 0, 0, 0, $currentMonth, date( 'd' ), $currentYear  ) );
      $startDate    = date('Y-m-d', mktime( 0, 0, 0, $currentMonth, date( 'd' ), $currentYear  ) );
      $endDate      = date('Y-m-d', mktime( 0, 0, 0, $currentMonth, $previousDay, $endYear ) );
      $configVars   = new CRM_Core_Config_Variables( );        
      foreach ( array( 'joinDate', 'startDate', 'endDate' ) as $date ) {
          $$date = CRM_Utils_Date::customFormat( $$date, $configVars->dateformatFull ); 
      }
      
      $this->click( "xpath=//div[@id='priceset']/div[2]/div[2]/div/span/input" );
      $this->click( "xpath=//div[@id='priceset']/div[3]/div[2]/div[2]/span/input" );

      $this->type( 'email-5', $contactParams['email-5'] );
      $this->type( 'first_name', $contactParams['first_name'] );
      $this->type( 'last_name', $contactParams['last_name'] );

      $streetAddress = "100 Main Street";
      $this->type("street_address-1", $streetAddress);
      $this->type("city-1", "San Francisco");
      $this->type("postal_code-1", "94117");
      $this->select("country-1", "value=1228");
      $this->select("state_province-1", "value=1001");
        
      //Credit Card Info
      $this->select("credit_card_type", "value=Visa");
      $this->type("credit_card_number", "4111111111111111");
      $this->type("cvv2", "000");
      $this->select("credit_card_exp_date[M]", "value=1");
      $this->select("credit_card_exp_date[Y]", "value=2020");
      
      //Billing Info
      $this->type("billing_first_name", $contactParams['first_name'] . "billing");
      $this->type("billing_last_name", $contactParams['last_name'] . "billing" );
      $this->type("billing_street_address-5", "15 Main St.");
      $this->type(" billing_city-5", "San Jose");
      $this->select("billing_country_id-5", "value=1228");
      $this->select("billing_state_province_id-5", "value=1004");
      $this->type("billing_postal_code-5", "94129");  
      $this->click("_qf_Main_upload-bottom");
      
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Confirm_next-bottom");
      
      $this->click("_qf_Confirm_next-bottom");
      $this->waitForPageToLoad('30000');

      //login to check membership
      $this->open( $this->sboxPath );
        
      // Log in using webtestLogin() method
      $this->webtestLogin();

      $this->open($this->sboxPath . "civicrm/member/search?reset=1");
      $this->waitForElementPresent("member_end_date_high");
        
      $this->type("sort_name", "{$contactParams['first_name']} {$contactParams['last_name']}" );
      $this->click("_qf_Search_refresh");
        
      $this->waitForPageToLoad('30000');
      $this->assertTrue( $this->isTextPresent( "2 Results " ) );
        
      $this->waitForElementPresent( "xpath=//div[@id='memberSearch']/table/tbody/tr" );
      $this->click( "xpath=//div[@id='memberSearch']/table/tbody//tr/td[4][text()='{$memTypeTitle1}']/../td[11]/span/a[text()='View']" );
      $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );

      //View Membership Record
      $verifyData = array( 'Membership Type' => "$memTypeTitle1",
                           'Status'          => 'New',
                           'Member Since'    => $joinDate,
                           'Start date'      => $startDate,
                           'End date'        => $endDate
                          );
      foreach ( $verifyData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                             preg_quote( $value ) );
      }

      $this->click( '_qf_MembershipView_cancel-bottom' );
      $this->waitForElementPresent( "xpath=//div[@id='memberSearch']/table/tbody/tr[2]" );
      $this->click( "xpath=//div[@id='memberSearch']/table/tbody//tr/td[4][text()='{$memTypeTitle2}']/../td[11]/span/a[text()='View']" );
      $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );

      //View Membership Record
      $verifyData = array( 'Membership Type' => "$memTypeTitle2",
                           'Status'          => 'New',
                           'Member Since'    => $joinDate,
                           'Start date'      => $startDate,
                           'End date'        => $endDate
                          );
      foreach ( $verifyData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                             preg_quote( $value ) );
      }
  }
}
