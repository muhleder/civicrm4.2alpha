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


 
class WebTest_Campaign_PetitionUsageScenarioTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/tmp/';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }
  
  function testPetitionUsageScenario()
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


      // Enable CiviCampaign module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if ( !in_array( "CiviCampaign", $enabledComponents ) ) {
          $this->addSelection("enableComponents-f", "label=CiviCampaign");
          $this->click("//option[@value='CiviCampaign']");
          $this->click("add");
          $this->click("_qf_Component_next-bottom");
          $this->waitForPageToLoad("30000");          
          $this->assertTrue($this->isTextPresent("Your changes have been saved."));    
      }

      // handle permissions early

      // let's give permission 'sign CiviCRM Petition' to anonymous user.
      $permissions = array(
                           // give profile related permision
                           "edit-1-sign-civicrm-petition",
                           "edit-1-profile-create",
                           "edit-1-profile-edit",
                           "edit-1-profile-listings",
                           "edit-1-profile-view",
                           // now give full permissions to CiviPetition to registered user
                           "edit-2-administer-civicampaign",
                           "edit-2-manage-campaign",
                           "edit-2-gotv-campaign-contacts",
                           "edit-2-interview-campaign-contacts",
                           "edit-2-release-campaign-contacts",
                           "edit-2-reserve-campaign-contacts",
                           "edit-2-sign-civicrm-petition"
                           );
      $this->changePermissions( $permissions );
      
      /////////////// Create Campaign ///////////////////////////////
      
      // Go directly to the URL of the screen that you will be add campaign
      $this->open($this->sboxPath . "civicrm/campaign/add?reset=1");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Campaign_upload-bottom");

      // Let's start filling the form with values.
      $title = substr(sha1(rand()), 0, 7);
      $this->type("title", "$title Campaign");

      // select the campaign type
      $this->select("campaign_type_id", "value=2");

      // fill in the description
      $this->type("description", "This is a test campaign");

      // include groups for the campaign
      $this->addSelection("includeGroups-f", "label=Advisory Board");
      $this->click("//option[@value=4]");
      $this->click("add");

      // fill the end date for campaign
      $this->webtestFillDate("end_date", "+1 year");
      
      // select campaign status
      $this->select("status_id", "value=2");

      // click save
      $this->click("_qf_Campaign_upload-bottom");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Campaign $title Campaign has been saved."), "Status message didn't show up after saving!");

      ////////////// Create petition using New Individual profile //////////////////////
      
      // Go directly to the URL of the screen that you will be add petition
      $this->open( $this->sboxPath . "civicrm/petition/add?reset=1" );
      
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Petition_next-bottom");

      // fill petition tile.
      $title = substr(sha1(rand()), 0, 7);
      $this->type("title", "$title Petition");

      // fill introduction 
      //$this->type("cke_instructions", "This is introduction of $title Petition");
      
      // select campaign 
      $this->select("campaign_id", "value=1");
      
      // select profile
      $this->select("contact_profile_id", "value=4" );
      
      // click save
      $this->click("_qf_Petition_next-bottom");
      $this->waitForPageToLoad("30000");

      $this->assertTrue($this->isTextPresent("Petition has been saved."));
      
      $this->waitForElementPresent( "link=Add Petition" );

      $this->waitForElementPresent( "petitions" );
      $this->click( "petitionSearch" );
      $this->type( "petition_title", $title );

      $this->click( "xpath=//div[@class='crm-accordion-body']/table/tbody/tr[2]/td/a[text()='Search']" );

      $this->waitForElementPresent( "xpath=//div[@id='petitions_wrapper']/table[@id='petitions']/tbody/tr/td[10]/span[2][text()='more ']/ul/li/a[text()='Sign']" );
      $url = $this->getAttribute( "xpath=//div[@id='petitions_wrapper']/table[@id='petitions']/tbody/tr/td[10]/span[2][text()='more ']/ul/li/a[text()='Sign']@href" );
      
      ////////////// Retrieve Sign Petition Url /////////////////////////
      
      // logout and sign as anonymous.
      $this->open( $this->sboxPath ."civicrm/logout?reset=1" );
      $this->waitForElementPresent("edit-submit");
      
      // go to the link that you will be sign as anonymous
      $this->open($url);
      $this->waitForElementPresent("_qf_Signature_next-bottom");

      // fill first name
      $firstName = substr(sha1(rand()), 0, 7);
      $this->type("first_name", $firstName);

      // fill last name
      $lastName = substr(sha1(rand()), 0, 7);
      $this->type("last_name", $lastName);

      // fill email
      $email = $firstName ."@" . $lastName . ".com";
      $this->type("email-Primary", $email);

      // click Sign the petition.
      $this->click("_qf_Signature_next-bottom");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent("Thank You"));
      
      // login 
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      $this->open($this->sboxPath . "civicrm/campaign?reset=1&subPage=petition");
      $this->waitForPageToLoad("30000");
      $this->waitForElementPresent("link=Add Petition");

      // check for unconfirmed petition signature
      $this->waitForElementPresent( "petitions" );
      $this->click( "petitionSearch" );
      $this->type( "petition_title", $title );
      $this->click( "xpath=//div[@class='crm-accordion-body']/table/tbody/tr[2]/td/a[text()='Search']" );

      $this->waitForElementPresent( "xpath=//div[@id='petitions_wrapper']/table[@id='petitions']/tbody/tr/td[10]/span[2][text()='more ']" );
      $this->click("xpath=//div[@id='petitions_wrapper']/table[@id='petitions']/tbody/tr/td[10]/span[2][text()='more ']/ul/li/a[text()='Signatures']");
      $this->waitForPageToLoad("30000");

      // verify tabular data
      $expected = array(
                        2 => 'Petition',
                        3 => "$title Petition", 
                        4 => "$firstName $lastName",
                        5 => "$lastName, $firstName", 
                        8 => 'Scheduled'
                        );      
      
      foreach ( $expected as $column => $value ) {
          $this->verifyText("xpath=//table[@class='selector']/tbody/tr[2]/td[$column]", preg_quote($value));
      }

  }
}