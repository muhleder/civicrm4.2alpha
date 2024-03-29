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
 | Version 3, 19 November 2007.                                       |
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


 
class WebTest_Generic_CheckActivityTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testCheckDashboardElements()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes. 
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();
      
      // Adding contact with randomized first name
      // We're using Quick Add block on the main page for this.
      $contactFirstName1 = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $contactFirstName1, "Devis", true );

      // Adding another contact with randomized first name
      // We're using Quick Add block on the main page for this.
      $contactFirstName2 = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $contactFirstName2, "Anderson", true );
      $this->open($this->sboxPath . "civicrm/activity?reset=1&action=add&context=standalone");
      
      // make sure the form loaded, check the end element
      $this->waitForElementPresent("_qf_Activity_upload");
      $this->select("activity_type_id", "label=Meeting");
      
      $this->typeKeys("css=tr.crm-activity-form-block-target_contact_id input#token-input-target_contact_id", "$contactFirstName1");
      
      // ...waiting for drop down with results to show up...
      $this->waitForElementPresent("css=div.token-input-dropdown-facebook");
      $this->waitForElementPresent("css=li.token-input-dropdown-item2-facebook");
      
      // ...need to use mouseDownAt on first result (which is a li element), click does not work
      $this->mouseDownAt("css=li.token-input-dropdown-item2-facebook");

      // ...again, waiting for the box with contact name to show up (span with delete token class indicates that it's present)...
      $this->waitForElementPresent("css=tr.crm-activity-form-block-target_contact_id td ul li span.token-input-delete-token-facebook");
      
      // Now we're doing the same for "Assigned To" field.
      // Typing contact's name into the field (using typeKeys(), not type()!)...
      $this->typeKeys("css=tr.crm-activity-form-block-assignee_contact_id input#token-input-assignee_contact_id", "$contactFirstName2");

      // ...waiting for drop down with results to show up...
      $this->waitForElementPresent("css=div.token-input-dropdown-facebook");
      $this->waitForElementPresent("css=li.token-input-dropdown-item2-facebook");

      //..need to use mouseDownAt on first result (which is a li element), click does not work
      $this->mouseDownAt("css=li.token-input-dropdown-item2-facebook");

      // ...again, waiting for the box with contact name to show up...
      $this->waitForElementPresent("css=tr.crm-activity-form-block-assignee_contact_id td ul li span.token-input-delete-token-facebook");
  }
}
?>
