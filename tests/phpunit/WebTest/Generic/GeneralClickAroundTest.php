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


 
class WebTest_Generic_GeneralClickAroundTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function login()
  {
      $this->open($this->sboxPath);
      $this->webtestLogin();
      $this->waitForPageToLoad("30000");
      $this->click("//a[contains(text(),'CiviCRM')]");
      $this->waitForPageToLoad("30000");
  }

  function testSearchMenu()
  {
      $this->login();
      // click Search -> Find Contacts
      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Search");
      $this->click("css=ul#civicrm-menu li.crm-Find_Contacts a");
      $this->waitForElementPresent('tag');

      $this->click('contact_type');
      $this->select('contact_type', 'label=Individual');
      $this->select('tag', 'label=Major Donor');
      $this->click('_qf_Basic_refresh');
      $this->waitForElementPresent('search-status');
      $this->assertText('search-status',"Contact Type - 'Individual'");
      $this->assertText('search-status','Tagged IN Major Donor');
      
      // Advanced Search by Tag
      $this->click("css=ul#civicrm-menu li.crm-Search");
      $this->click("css=ul#civicrm-menu li.crm-Advanced_Search a");
      $this->waitForElementPresent('_qf_Advanced_refresh');
      $this->click('crmasmSelect2');
      $this->select('crmasmSelect2', 'label=Major Donor');
      $this->waitForElementPresent("//ul[@id='crmasmList2']/li/span");
      $this->click('_qf_Advanced_refresh');
      $this->waitForElementPresent('search-status');
      $this->assertText('search-status','Tagged IN Major Donor');

  }

  function testNewIndividual()
  {
      $this->login();

      // Create New → Individual
      $this->click("crm-create-new-link");
      $this->click("link=Individual");
      $this->waitForPageToLoad("30000");

      $this->assertElementPresent("first_name");
      $this->assertElementPresent("email_1_email");
      $this->assertElementPresent("phone_1_phone");
      $this->assertElementPresent("contact_source");
      $this->assertTextPresent("Constituent Information");
      $this->click("//form[@id='Contact']/div[2]/div[4]/div[1]");
      $this->click("//div[@id='customData1']/table/tbody/tr[1]/td[1]/label");
      $this->assertTextPresent("Most Important Issue");
      $this->click("//form[@id='Contact']/div[2]/div[6]/div[1]");
      $this->assertTextPresent("Communication Preferences");
      $this->assertTextPresent("Do not phone");
  }

  function testManageGroups()
  {
      $this->login();

      // Contacts → Manage Groups
      $this->click("//ul[@id='civicrm-menu']/li[4]");
      $this->click("xpath=//div[@id='root-menu-div']//div/ul//li/div/a[text()='Manage Groups']");
      $this->waitForPageToLoad("30000");

      $this->assertTextPresent("Find Groups");
      $this->assertElementPresent("title");
      $this->assertTextPresent("Access Control");
      $this->assertTextPresent("Newsletter Subscribers");
      $this->assertTextPresent("Add Group");
  }

  function testContributionDashboard()
  {
      $this->login();
      // Enable CiviContribute module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! in_array( "CiviContribute", $enabledComponents ) ) {
        $this->addSelection("enableComponents-f", "label=CiviContribute");
        $this->click("//option[@value='CiviContribute']");
        $this->click("add");
        $this->click("_qf_Component_next-bottom");
        $this->waitForPageToLoad("30000");
      }

      // Contributions → Dashboard
      $this->click("css=ul#civicrm-menu li.crm-Contributions");
      $this->click("css=ul#civicrm-menu li.crm-Contributions li.crm-Dashboard a");
      $this->waitForPageToLoad("30000");

      $this->assertTextPresent("Contribution Summary");
      $this->assertTextPresent("Select Year (for monthly breakdown)");
      $this->assertTextPresent("Recent Contributions");
      $this->assertTextPresent("Find more contributions...");
  }

  function testEventDashboard()
  {
      $this->login();

      // Enable CiviEvent module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! in_array( "CiviEvent", $enabledComponents ) ) {
        $this->addSelection("enableComponents-f", "label=CiviEvent");
        $this->click("//option[@value='CiviEvent']");
        $this->click("add");
        $this->click("_qf_Component_next-bottom");
        $this->waitForPageToLoad("30000");
      }

      // Events → Dashboard
      $this->click("css=ul#civicrm-menu li.crm-Events");
      $this->click("css=ul#civicrm-menu li.crm-Events li.crm-Dashboard a");
      $this->waitForPageToLoad("30000");

      $this->assertTextPresent("Event Summary");
      $this->assertTextPresent("Fall Fundraiser Dinner");
      $this->assertTextPresent("Counted:");
      $this->assertTextPresent("Not Counted:");
      $this->assertTextPresent("Not Counted Due To Status:");
      $this->assertTextPresent("Not Counted Due To Role:");
      $this->assertTextPresent("Registered:");
      $this->assertTextPresent("Attended:");
      $this->assertTextPresent("No-show:");
      $this->assertTextPresent("Cancelled:");
      $this->assertTextPresent("Recent Registrations");
      $this->assertTextPresent("Find more event participants...");
  }

  function testMembershipsDashboard()
  {
      $this->login();

      // Enable CiviMember module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! in_array( "CiviMember", $enabledComponents ) ) {
        $this->addSelection("enableComponents-f", "label=CiviMember");
        $this->click("//option[@value='CiviMember']");
        $this->click("add");
        $this->click("_qf_Component_next-bottom");
        $this->waitForPageToLoad("30000");
      }

      // Memberships → Dashboard
      $this->click("css=ul#civicrm-menu li.crm-Memberships");
      $this->click("css=ul#civicrm-menu li.crm-Memberships li.crm-Dashboard a");
      $this->waitForPageToLoad("30000");

      $this->assertTextPresent("Membership Summary");
      $this->assertTextPresent("Members by Type");
      $this->assertTextPresent("Recent Memberships");
      $this->assertTextPresent("Find more members...");
  }

  function testFindContributions()
  {
      $this->login();

      // Enable CiviContribute module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! in_array( "CiviContribute", $enabledComponents ) ) {
        $this->addSelection("enableComponents-f", "label=CiviContribute");
        $this->click("//option[@value='CiviContribute']");
        $this->click("add");
        $this->click("_qf_Component_next-bottom");
        $this->waitForPageToLoad("30000");
      }

      // Search → Find Contributions
      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Search");
      $this->click("css=ul#civicrm-menu li.crm-Find_Contributions a");
      $this->waitForPageToLoad("30000");

      $this->assertTextPresent("Edit Search Criteria");
      $this->assertElementPresent("sort_name");
      $this->assertElementPresent("contribution_date_low");
      $this->assertElementPresent("contribution_amount_low");
      $this->assertElementPresent("contribution_check_number");
      $this->assertTextPresent("Contribution Type");
      $this->assertTextPresent("Contribution Page");
      $this->assertElementPresent("contribution_in_honor_of");
      $this->assertElementPresent("contribution_source");
      $this->assertTextPresent("Personal Campaign Page");
      $this->assertTextPresent("Personal Campaign Page Honor Roll");
      $this->assertTextPresent("Currency");
  }

  function testNewMailing( )
  {
      $this->login();

      // Enable CiviMail module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! in_array( "CiviMail", $enabledComponents ) ) {
          $this->addSelection("enableComponents-f", "label=CiviMail");
          $this->click("//option[@value='CiviMail']");
          $this->click("add");
          $this->click("_qf_Component_next-bottom");
          $this->waitForPageToLoad("30000");
      }

      // configure default mail-box
      $this->open( $this->sboxPath . "civicrm/admin/mailSettings?action=update&id=1&reset=1" );
      $this->waitForElementPresent( '_qf_MailSettings_cancel-bottom' );
      $this->type( 'name', 'Test Domain' );
      $this->type( 'domain', 'example.com' );
      $this->select( 'protocol', 'value=1' );
      $this->click( '_qf_MailSettings_next-bottom' );
      $this->waitForPageToLoad("30000");
      
      // New Mailing Form
      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Mailings");
      $this->click("css=ul#civicrm-menu li.crm-New_Mailing a");
      $this->waitForPageToLoad("30000");


      $this->assertTextPresent("New Mailing");
      $this->assertElementPresent("name");
      $this->assertElementPresent("includeGroups-f");
      $this->assertElementPresent("excludeGroups-t");
  }

  function testConstituentReportSummary( )
  {
      $this->login();

      // Constituent Report Summary
      $this->click("css=ul#civicrm-menu li.crm-Reports");
      $this->click("css=ul#civicrm-menu li.crm-Constituent_Report__Summary_ a");
      $this->waitForPageToLoad("30000");

      $this->assertTextPresent("Constituent Report (Summary)");
      $this->click("//form[@id='Summary']/div[2]/div/div/div/div");
      $this->assertTextPresent("Display Columns");
      $this->click("//form[@id='Summary']/div[2]//div[@id='id_default']/div/div/div");
      $this->assertTextPresent("Most Important Issue");
      $this->assertTextPresent("Set Filters");
      $this->assertTextPresent("Contact Name");
      $this->assertTextPresent("Contact Source");
      $this->assertTextPresent("Country");
      $this->assertTextPresent("State / Province");
      $this->assertTextPresent("Group");
      $this->assertTextPresent("Tag");
      $this->click("_qf_Summary_submit");
      $this->waitForElementPresent("_qf_Summary_submit_print");
      $this->assertTextPresent("Row(s) Listed");
      $this->assertTextPresent("Total Row(s)");
  }
  
  function testCustomData( )
  {
      $this->login();

      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Administer");
      $this->click("xpath=//div[@id='root-menu-div']//a[text()='Custom Fields']");
      $this->waitForPageToLoad("30000");

      $this->assertTextPresent("Custom Data");
      $this->assertTextPresent("Constituent Information");
      $this->assertTextPresent("Donor Information");
      $this->assertTextPresent("Food Preference");

      // Verify create form
      $this->click("//span[contains(text(), 'Add Set of Custom Fields')]");
      $this->waitForPageToLoad("30000");
      $this->assertElementPresent("title");
      $this->assertElementPresent("extends[0]");
      $this->assertElementPresent("weight");
      $this->assertTextPresent("Pre-form Help");
      $this->assertTextPresent("Post-form Help");
  }
  
  function testProfile( )
  {
      $this->login();

      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Administer");
      $this->click("css=ul#civicrm-menu li.crm-Customize_Data_and_Screens");
      $this->click("xpath=//div[@id='root-menu-div']//a[text()='Profiles']");

      $this->waitForPageToLoad("30000");

      $this->assertTextPresent("CiviCRM Profile");
      // Verify Reserved Profiles
      $this->assertTextPresent("New Household");
      $this->assertTextPresent("New Individual");
      $this->assertTextPresent("New Organization");
      $this->assertTextPresent("Participant Status");
      $this->assertTextPresent("Shared Address");
      $this->assertTextPresent("Summary Overlay");

      // Verify profiles that are not reserved
      $this->assertTextPresent("Name and Address");
      $this->assertTextPresent("Supporter Profile");

      // Verify create form
      $this->click("//span[contains(text(), 'Add Profile')]");
      $this->waitForPageToLoad("30000");
      $this->assertElementPresent("title");
      $this->assertElementPresent("uf_group_type[Profile]");
      $this->assertElementPresent("weight");
      $this->assertTextPresent("Pre-form Help");
      $this->assertTextPresent("Post-form Help");
      $this->click("//form[@id='Group']/div[2]/div[2]/div/div");
      $this->assertElementPresent("group");
      $this->assertElementPresent("post_URL");
      $this->assertTextPresent("Drupal user account registration option?");
      $this->assertTextPresent("What to do upon duplicate match");
      $this->assertTextPresent("Proximity search");
  }
 
  function testTags( )
  {
      $this->login();

      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Administer");
      $this->click("css=ul#civicrm-menu li.crm-Customize_Data_and_Screens");
      $this->click("xpath=//div[@id='root-menu-div']//a[text()='Tags (Categories)']");

      $this->waitForPageToLoad("30000");

      // Verify tags
      $this->assertTextPresent("Non-profit");
      $this->assertTextPresent("Company");
      $this->assertTextPresent("Government Entity");
      $this->assertTextPresent("Major Donor");
      $this->assertTextPresent("Volunteer");
  }
 
  function testActivityTypes( )
  {
      $this->login();

      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Administer");
      $this->click("css=ul#civicrm-menu li.crm-Customize_Data_and_Screens");
      $this->click("xpath=//div[@id='root-menu-div']//a[text()='Activity Types']");

      $this->waitForPageToLoad("30000");

      // Verify activity types
      $this->assertTextPresent("Meeting");
      $this->assertTextPresent("Print PDF Letter");
      $this->assertTextPresent("Event Registration");
      $this->assertTextPresent("Contribution");
      $this->assertTextPresent("Membership Signup");
  }

  function testRelationshipTypes( )
  {
      $this->login();

      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Administer");
      $this->click("css=ul#civicrm-menu li.crm-Customize_Data_and_Screens");
      $this->click("xpath=//div[@id='root-menu-div']//a[text()='Relationship Types']");

      $this->waitForPageToLoad("30000");

      // Verify relationship types
      $this->assertTextPresent("Child of");
      $this->assertTextPresent("Head of Household for");
      $this->assertTextPresent("Sibling of");
      $this->assertTextPresent("Spouse of");
      $this->assertTextPresent("Supervised by");
      $this->assertTextPresent("Volunteer for");
  }

  function testMessageTemplates( )
  {
      $this->login();

      // Use class names for menu items since li array can change based on which components are enabled
      $this->click("css=ul#civicrm-menu li.crm-Administer");
      $this->click("css=ul#civicrm-menu li.crm-Communications");
      $this->click("xpath=//div[@id='root-menu-div']//a[text()='Message Templates']");
      $this->waitForPageToLoad("30000");

      // Verify message templates
      $this->click("//a[contains(text(),'System Workflow Messages')]");
      $this->assertTextPresent("Contributions - Receipt (on-line)");
      $this->assertTextPresent("Events - Registration Confirmation and Receipt (off-line)");
      $this->assertTextPresent("Memberships - Signup and Renewal Receipts (off-line)");
      $this->assertTextPresent("Personal Campaign Pages - Supporter Status Change Notification");
      $this->assertTextPresent("Profiles - Admin Notification");
      $this->assertTextPresent("Tell-a-Friend Email");
  }
}
