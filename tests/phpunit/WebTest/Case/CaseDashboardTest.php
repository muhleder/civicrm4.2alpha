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


 
class WebTest_Case_AddCaseTest extends CiviSeleniumTestCase {

            

  protected function setUp()
  {
      parent::setUp();
  }

  function testAllOrMyCases()
  {

    $this->open( $this->sboxPath );

    // Log in as admin first to verify permissions for CiviCase
    $this->webtestLogin( );

    // Enable CiviCase module if necessary
    $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("_qf_Component_next-bottom");
    $enabledComponents = $this->getSelectOptions("enableComponents-t");
    if (! in_array( "CiviCase", $enabledComponents ) ) {
      $this->addSelection("enableComponents-f", "label=CiviCase");
      $this->click("//option[@value='CiviCase']");
      $this->click("add");
      $this->click("_qf_Component_next-bottom");
      $this->waitForPageToLoad("30000");          
    }

    // let's give full CiviCase permissions to demo user (registered user).
    $permission = array('edit-2-access-all-cases-and-activities','edit-2-access-my-cases-and-activities','edit-2-administer-civicase','edit-2-delete-in-civicase');
    $this->changePermissions( $permission );
    
    // Go directly to the URL of the screen that you will be testing (Dashboard).
    $this->open($this->sboxPath . "civicrm/case?reset=1");

    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("css=a.button");

    // Should default to My Cases
    $this->assertTrue( $this->isChecked("name=allupcoming value=0"), 'Case dashboard should default to My Cases.' );
    // The header text of the table changes too
    $this->assertTextPresent("Summary of Case Involvement");

    $this->click("name=allupcoming value=1");
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("css=a.button");

    $this->assertTrue( $this->isChecked("name=allupcoming value=1"), 'Selection of All Cases failed.' );
    $this->assertTextPresent("Summary of All Cases");
  }

}
