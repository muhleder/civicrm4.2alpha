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

class WebTest_Contribute_ContactContextAddTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testContactContextAdd()
  {       
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );
      
      // Log in using webtestLogin() method
      $this->webtestLogin();

      // Create a contact to be used as soft creditor
      $softCreditFname = substr(sha1(rand()), 0, 7);
      $softCreditLname = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $softCreditFname, $softCreditLname, false );

      // Adding contact with randomized first name (so we can then select that contact when creating contribution.)
      // We're using Quick Add block on the main page for this.
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Anderson", true );
      
      // Get the contact id of the new contact
      $contactUrl = $this->parseURL( );
      $cid = $contactUrl['queryString']['cid'];
      $this->assertType( 'numeric', $cid );
      
      // go to contribution tab and add contribution.
      $this->click("css=li#tab_contribute a");
      
      // wait for Record Contribution elenment.
      $this->waitForElementPresent("link=Record Contribution (Check, Cash, EFT ...)");
      $this->click("link=Record Contribution (Check, Cash, EFT ...)");
      
      $this->waitForElementPresent("_qf_Contribution_cancel-bottom");
      // fill contribution type.
      $this->select("contribution_type_id", "Donation");
      
      // fill in Received Date
      $this->webtestFillDate('receive_date');
     
      // source
      $this->type("source", "Mailer 1");
      
      // total amount
      $this->type("total_amount", "100");

      // select payment instrument type = Check and enter chk number
      $this->select("payment_instrument_id", "value=4");
      $this->waitForElementPresent("check_number");
      $this->type("check_number", "check #1041");

      $this->type("trxn_id", "P20901X1" . rand(100, 10000));
      
      // soft credit
      $this->typeKeys("soft_credit_to", $softCreditFname);
      $this->fireEvent("soft_credit_to", "focus");
      $this->waitForElementPresent("css=div.ac_results-inner li");
      $this->click("css=div.ac_results-inner li");

      //Custom Data
      $this->waitForElementPresent('CIVICRM_QFID_3_6');
      $this->click('CIVICRM_QFID_3_6');

      //Additional Detail section
      $this->click("AdditionalDetail");
      $this->waitForElementPresent("thankyou_date");

      $this->type("note", "Test note for {$firstName}.");
      $this->type("non_deductible_amount", "10");
      $this->type("fee_amount", "0");
      $this->type("net_amount", "0");
      $this->type("invoice_id", time());
      $this->webtestFillDate('thankyou_date');
     
      //Honoree section
      $this->click("Honoree");
      $this->waitForElementPresent("honor_email");

      $this->click("CIVICRM_QFID_1_2");
      $this->select("honor_prefix_id", "label=Ms.");
      $this->type("honor_first_name", "Foo");
      $this->type("honor_last_name", "Bar");
      $this->type("honor_email", "foo@bar.com");

      //Premium section
      $this->click("Premium");
      $this->waitForElementPresent("fulfilled_date");
      $this->select("product_name[0]", "label=Coffee Mug ( MUG-101 )");
      $this->select("product_name[1]", "label=Black");
      $this->webtestFillDate('fulfilled_date');
      
      // Clicking save.
      $this->click("_qf_Contribution_upload-bottom");
      $this->waitForPageToLoad("30000");
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The contribution record has been saved"));
      
      $this->waitForElementPresent("xpath=//div[@id='Contributions']//table/tbody/tr/td[8]/span/a[text()='View']");

      // click through to the Contribution view screen
      $this->click("xpath=//div[@id='Contributions']//table/tbody/tr/td[8]/span/a[text()='View']");
      $this->waitForElementPresent('_qf_ContributionView_cancel-bottom');
      
      // verify Contribution created
      $verifyData = array(
                          'From'                            => $firstName . " Anderson",
                          'Contribution Type'               => 'Donation',
                          'Contribution Status'             => 'Completed',
                          'Paid By'                         => 'Check',
                          'How long have you been a donor?' => 'Less than 1 year',
                          'Total Amount'                    => '$ 100.00',
                          'Check Number'                    => 'check #1041'
                          );
      foreach ( $verifyData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='ContributionView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                             preg_quote( $value ) );   
      }
      
      // check values of contribution record in the DB
      $viewUrl = $this->parseURL( );
      $id = $viewUrl['queryString']['id'];
      $this->assertType( 'numeric', $id );
      
      $searchParams  = array( 'id'              => $id );
      $compareParams = array( 'contact_id'      => $cid,
                              'total_amount'    => '100.00', );
      $this->assertDBCompareValues( 'CRM_Contribute_DAO_Contribution', $searchParams, $compareParams );

      
      // go to soft creditor contact view page
      $this->click( "xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[16]/td[2]/a[text()='{$softCreditFname} {$softCreditLname}']" );

      // go to contribution tab
      $this->waitForElementPresent("css=li#tab_contribute a");
      $this->click("css=li#tab_contribute a");
      $this->waitForElementPresent("link=Record Contribution (Check, Cash, EFT ...)");

      // verify soft credit details
      $expected = array( 3  => 'Donation', 
                         2  => '100.00',
                         5  => 'Completed',
                         1  => "{$firstName} Anderson", );
      foreach ( $expected as  $value => $label ) {
          $this->verifyText("xpath=id('Search')/div[2]/table[2]/tbody/tr[2]/td[$value]", preg_quote($label));
      }
  }
}