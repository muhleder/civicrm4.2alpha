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

class WebTest_Contribute_OfflineRecurContributionTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testOfflineRecurContribution()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      // We need a payment processor
      $processorName = 'Webtest AuthNet' . substr(sha1(rand()), 0, 7);
      $this->webtestAddPaymentProcessor($processorName, 'AuthNet');

      // create a new contact for whom recurring contribution is to be created
      $firstName = 'Jane'.substr( sha1( rand( ) ), 0, 7 );
      $middleName = 'Middle';
      $lastName  = 'Recuroff_'.substr( sha1( rand( ) ), 0, 7 );
      $this->webtestAddContact($firstName, $lastName, "{$firstName}@example.com");
      $contactName = "$firstName $lastName";

      $this->click('css=li#tab_contribute a');

      $this->waitForElementPresent('link=Submit Credit Card Contribution');
      $this->click('link=Submit Credit Card Contribution');
      $this->waitForPageToLoad('30000');

      // since we don't have live credentials we will switch to test mode
      $url = $this->getLocation( );
      $url = str_replace('mode=live', 'mode=test', $url);
      $this->open($url);

      // start filling out contribution form
      $this->waitForElementPresent('payment_processor_id');
      $this->select('payment_processor_id',  "label={$processorName}");

      $this->click('contribution_type_id');
      $this->select('contribution_type_id', 'label=Donation');
      $this->type('total_amount', '10');

      // recurring contribution fields
      $this->click('CIVICRM_QFID_1_8');
      $this->type('frequency_interval', '1');
      $this->select('frequency_unit', 'label=month(s)');
      $this->type('installments', '12');

      $this->click('is_email_receipt');
      
      // enter credit card info on form
      $this->webtestAddCreditCardDetails( );

      // billing address
      $this->webtestAddBillingDetails( $firstName, $middleName, $lastName );

      $this->click('_qf_Contribution_upload-bottom');
      $this->waitForPageToLoad('30000');

      // Use Find Contributions to make sure test recurring contribution exists
      $this->open($this->sboxPath . 'civicrm/contribute/search?reset=1');
      $this->waitForElementPresent('contribution_currency_type');

      $this->type('sort_name', "$lastName, $firstName" );
      $this->click('contribution_test');
      $this->click('_qf_Search_refresh');

      $this->waitForElementPresent('css=#contributionSearch table tbody tr td span a.action-item-first');
      $this->click('css=#contributionSearch table tbody tr td span a.action-item-first');
      $this->waitForElementPresent( '_qf_ContributionView_cancel-bottom' );

      // View Recurring Contribution Record
      $verifyData = array(
                          'From'                 => "$contactName",
                          'Contribution Type'    => 'Donation (test)',
                          'Total Amount'         => 'Installments: 12, Interval: 1 month(s)',
                          'Contribution Status'  => 'Pending : Incomplete Transaction',
                          'Paid By'              => 'Credit Card',
                          );
      foreach ( $verifyData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='ContributionView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                             preg_quote( $value ) );   
      }
  }
}
