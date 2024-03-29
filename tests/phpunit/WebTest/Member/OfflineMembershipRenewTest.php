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

class WebTest_Member_OfflineMembershipRenewTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testOfflineMembershipRenew()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      // make sure period is correct for the membership type we testing for,
      // since it might have been modified by other tests
      // add membership type  
      $membershipTypes = $this->webtestAddMembershipType( 'rolling', 2 );
      
      // quick create a contact
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact($firstName, "Memberson", "{$firstName}@memberson.com");
      $contactName = "$firstName Memberson";

      // click through to the membership tab
      $this->click('css=li#tab_member a');

      $this->waitForElementPresent('link=Add Membership');
      $this->click('link=Add Membership');

      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      // fill in Membership Organization and Type
      $this->select( 'membership_type_id[0]', "label={$membershipTypes['member_org']}" );
      $this->select( 'membership_type_id[1]', "label={$membershipTypes['membership_type']}" );

      // fill in Source
      $sourceText = 'Offline Membership Renewal Webtest';
      $this->type('source', $sourceText);

      // Fill Member Since
      $this->webtestFillDate('join_date', '-2 year');

      // Let Start Date and End Date be auto computed

      // Clicking save.
      $this->click('_qf_Membership_upload');
      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("{$membershipTypes['membership_type']} membership for $firstName Memberson has been added."), "Status message didn't show up after saving!");

      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span[2][text()='more ']/ul/li/a[text()='Renew']");

      // click through to the Membership Renewal Link
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span[2][text()='more ']/ul/li/a[text()='Renew']");

      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');

      // save the renewed membership
      $this->click('_qf_MembershipRenewal_upload-bottom');

      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );

      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span/a[text()='View']");

      // click through to the membership view screen
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span/a[text()='View']");

      $this->waitForElementPresent('_qf_MembershipView_cancel-bottom');

      $joinDate = $startDate = date('F jS, Y', strtotime("-2 year"));
      $endDate  = date('F jS, Y', strtotime("+2 year -1 day"));

      // verify membership renewed
      $verifyMembershipRenewData = array(
                                         'Member'          => $contactName,
                                         'Membership Type' => $membershipTypes['membership_type'],
                                         'Status'          => 'Current',
                                         'Source'          => $sourceText,
                                         'Member Since'    => $joinDate,
                                         'Start date'      => $startDate,
                                         'End date'        => $endDate,
                                         );
      foreach ( $verifyMembershipRenewData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote( $value ) );
      }
  }

  function testOfflineMemberRenewOverride( )
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      // add membership type  
      $membershipTypes = $this->webtestAddMembershipType( 'rolling', 2 );

      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact($firstName, "Memberson", "{$firstName}@memberson.com");
      $contactName = "$firstName Memberson";

      // click through to the membership tab
      $this->click('css=li#tab_member a');

      $this->waitForElementPresent('link=Add Membership');
      $this->click('link=Add Membership');

      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      // fill in Membership Organization and Type
      $this->select( 'membership_type_id[0]', "label={$membershipTypes['member_org']}" );
      $this->select( 'membership_type_id[1]', "label={$membershipTypes['membership_type']}" );
 
      // fill in Source
      $sourceText = 'Offline Membership Renewal Webtest';
      $this->type('source', $sourceText);

      // Let Join Date stay default
      
      // fill in Start Date
      $this->webtestFillDate('start_date');
      
      // Let End Date be auto computed

      // fill in Status Override?
      $this->click('is_override', 'value=1');
      $this->waitForElementPresent('status_id');
      $this->select('status_id', 'value=3');
      
      // fill in Record Membership Payment?
      $this->click('record_contribution', 'value=1');
      $this->waitForElementPresent('contribution_status_id');
      
      // select the contribution type for the selected membership type
      $this->select('contribution_type_id', 'value=2');
      
      // the amount for the selected membership type
      $this->type('total_amount', '100.00');
      
      // select payment instrument type = Check and enter chk number
      $this->select("payment_instrument_id", "value=4");
      $this->waitForElementPresent("check_number");
      $this->type("check_number", "check #12345");
      $this->type("trxn_id", "P5476785" . rand(100, 10000));

      // fill  the payment status be default
      $this->select("contribution_status_id", "value=2");

      // Clicking save.
      $this->click('_qf_Membership_upload');
      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("{$membershipTypes['membership_type']} membership for $firstName Memberson has been added."), "Status message didn't show up after saving!");

      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span[2][text()='more ']/ul/li/a[text()='Renew']");
      
      // click through to the Membership Renewal Link
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span[2][text()='more ']/ul/li/a[text()='Renew']");
      
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      
      // save the renewed membership
      $this->click('_qf_MembershipRenewal_upload-bottom');
      
      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span/a[text()='View']");

      // click through to the membership view screen
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span/a[text()='View']");
      
      $this->waitForElementPresent('_qf_MembershipView_cancel-bottom');
      
      $joinDate  = date('F jS, Y');
      $startDate = date('F jS, Y', strtotime("+1 month"));
      $endDate   = date('F jS, Y', strtotime("+4 year 1 month -1 day"));

      // verify membership renew override
      $verifyMembershipRenewOverrideData =  array(
                                                  'Member'          => $contactName,
                                                  'Membership Type' => $membershipTypes['membership_type'],
                                                  'Status'          => 'New',
                                                  'Source'          => $sourceText,
                                                  'Member Since'    => $joinDate,
                                                  'Start date'      => $startDate,
                                                  'End date'        => $endDate,
                                                  );
      foreach ( $verifyMembershipRenewOverrideData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote( $value ) );
      }
  }
  
  function testOfflineMembershipRenewChangeType( )
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin( );
      
      // make sure period is correct for the membership type we testing for, 
      // since it might have been modified by other tests 
      // add membership type  
      $membershipTypes   = $this->webtestAddMembershipType( 'rolling', 1 );
      $newMembershipType = $this->webtestAddMembershipType( 'rolling', 1 );

      $firstName = substr( sha1( rand( ) ), 0, 7 );
      $this->webtestAddContact( $firstName, "Memberson", "{$firstName}@memberson.com" );
      $contactName = "$firstName Memberson";
      
      // click through to the membership tab
      $this->click( 'css=li#tab_member a' );
      
      $this->waitForElementPresent( 'link=Add Membership' );
      $this->click( 'link=Add Membership' );
      
      $this->waitForElementPresent( '_qf_Membership_cancel-bottom' );
      
      // fill in Membership Organization and Type
      $this->select( 'membership_type_id[0]', "label={$membershipTypes['member_org']}" );
      $this->select( 'membership_type_id[1]', "label={$membershipTypes['membership_type']}" );
      
      // fill in Source
      $sourceText = 'Offline Membership Renewal Webtest';
      $this->type( 'source', $sourceText );
      
      // Fill Member Since
      $this->webtestFillDate( 'join_date', '-2 year' );

      // Let Start Date and End Date be auto computed
      
      // Clicking save.
      $this->click( '_qf_Membership_upload' );
      $this->waitForPageToLoad( '30000' );
      
      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      // Is status message correct?
      $this->assertTrue( $this->isTextPresent( "{$membershipTypes['membership_type']} membership for $firstName Memberson has been added."), "Status message didn't show up after saving!" );

      $this->waitForElementPresent( "xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span[2][text()='more ']/ul/li/a[text()='Renew']" );
      
      // click through to the Membership Renewal Link
      $this->click( "xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span[2][text()='more ']/ul/li/a[text()='Renew']" );
      
      $this->waitForElementPresent( '_qf_MembershipRenewal_cancel-bottom' );
      
      //change membership type
      $this->click( "changeMembershipOrgType" );
      $this->waitForElementPresent( 'membership_type_id[1]' );
      $this->select( 'membership_type_id[0]', "label={$newMembershipType['member_org']}" );
      $this->select( 'membership_type_id[1]', "label={$newMembershipType['membership_type']}" );

      $this->click( 'membership_type_id[0]' );
      sleep(2);

      // save the renewed membership
      $this->click( '_qf_MembershipRenewal_upload-bottom' );

      $this->waitForPageToLoad( '30000' );
      
      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      $this->waitForElementPresent( "xpath=//div[@id='Memberships']//div[@id='inactive-memberships']//table/tbody/tr/td[7]/span/a[text()='View']" );
      
      // click through to the membership view screen
      $this->click( "xpath=//div[@id='Memberships']//div[@id='inactive-memberships']//table/tbody/tr/td[7]/span/a[text()='View']" );

      $this->waitForElementPresent( '_qf_MembershipView_cancel-bottom' );
      
      $joinDate  = date( 'F jS, Y', strtotime( "-2 year" ) );
      $startDate = date( 'F jS, Y', strtotime( "-2 year" ) );
      $endDate   = date( 'F jS, Y', strtotime( "-1 year -1 day" ) );
      
      // verify membership renewed and the membership type is changed
      $verifyMembershipData =  array(
                                     'Member'          => $contactName,
                                     'Membership Type' => $membershipTypes['membership_type'],
                                     'Status'          => 'Expired',
                                     'Source'          => $sourceText,
                                     'Member Since'    => $joinDate,
                                     'Start date'      => $startDate,
                                     'End date'        => $endDate,
                                     );
      foreach ( $verifyMembershipData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote( $value ) );
      }
  }

  function testOfflineMembershipRenewMultipleTerms()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      // make sure period is correct for the membership type we testing for,
      // since it might have been modified by other tests
      // add membership type  
      $membershipTypes = $this->webtestAddMembershipType( 'rolling', 2 );
      
      // quick create a contact
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact($firstName, "Memberson", "{$firstName}@memberson.com");
      $contactName = "$firstName Memberson";

      // click through to the membership tab
      $this->click('css=li#tab_member a');

      $this->waitForElementPresent('link=Add Membership');
      $this->click('link=Add Membership');

      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      // fill in Membership Organization and Type
      $this->select( 'membership_type_id[0]', "label={$membershipTypes['member_org']}" );
      $this->select( 'membership_type_id[1]', "label={$membershipTypes['membership_type']}" );

      // fill in Source
      $sourceText = 'Offline Membership Renewal Webtest';
      $this->type('source', $sourceText);

      // Fill Member Since
      $this->webtestFillDate('join_date', '-2 year');

      // Let Start Date and End Date be auto computed

      // Record contribution
      $this->click( 'record_contribution' );
      $this->waitForElementPresent( 'contribution_type_id' );
      $this->select( 'contribution_type_id', "label=Member Dues" );
      $this->select( 'payment_instrument_id', "label=Check" );
      $this->waitForElementPresent( 'check_number' );
      $this->type( 'check_number', '1023' );
      $this->select ( 'contribution_status_id', "label=Completed" );
      $this->click( 'send_receipt' );
      
      // Clicking save.
      $this->click( '_qf_Membership_upload' );
      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("{$membershipTypes['membership_type']} membership for $firstName Memberson has been added."), "Status message didn't show up after saving!");
      $this->assertTrue($this->isTextPresent("A membership confirmation and receipt has been sent to {$firstName}@memberson.com."), "Email sent to member message didn't show up after saving membership!");

      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span[2][text()='more ']/ul/li/a[text()='Renew']");

      // click through to the Membership Renewal Link
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span[2][text()='more ']/ul/li/a[text()='Renew']");

      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      // Record contribution and set number of terms to 2
      $this->click( 'record_contribution' );
      $this->waitForElementPresent( 'contribution_type_id' );
      $this->click( 'changeTermsLink' );
      $this->waitForElementPresent( 'num_terms' );
      $this->type( 'num_terms', '' );
      $this->type( 'num_terms', '2' );
      // added sleep to make sure jscript onchange for total_amount has a chance to fire
      sleep(2);
      $this->click( 'total_amount');
      $this->verifyValue( 'total_amount', "200.00" );
      $this->select( 'contribution_type_id', "label=Member Dues" );
      $this->select( 'payment_instrument_id', "label=Check" );
      $this->waitForElementPresent( 'check_number' );
      $this->type( 'check_number', '1024' );
      $this->select ( 'contribution_status_id', "label=Completed" );
      $this->click( 'send_receipt' );

      // save the renewed membership
      $this->click('_qf_MembershipRenewal_upload-bottom');

      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );

      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span/a[text()='View']");

      // click through to the membership view screen
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[7]/span/a[text()='View']");

      $this->waitForElementPresent('_qf_MembershipView_cancel-bottom');

      $joinDate = $startDate = date('F jS, Y', strtotime("-2 year"));
      // Adding 2 x 2 years renewal to initial membership.
      $endDate  = date('F jS, Y', strtotime("+4 year -1 day"));

      // verify membership renewed
      $verifyMembershipRenewData = array(
                                         'Member'          => $contactName,
                                         'Membership Type' => $membershipTypes['membership_type'],
                                         'Status'          => 'Current',
                                         'Source'          => $sourceText,
                                         'Member Since'    => $joinDate,
                                         'Start date'      => $startDate,
                                         'End date'        => $endDate,
                                         );
      foreach ( $verifyMembershipRenewData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote( $value ) );
      }
  }

}
