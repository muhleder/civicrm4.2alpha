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

class WebTest_Admin_Form_Setting_LocalizationTest extends CiviSeleniumTestCase
{

  protected function setUp()
  {
      parent::setUp();
  }

  function testDefaultCountryIsEnabled()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );
      $this->webtestLogin( );
      $this->open( $this->sboxPath . "civicrm/admin/setting/localization?reset=1" );
 
      $this->waitForPageToLoad("30000");
      $this->addSelection("countryLimit-t", "label=United States");
      $this->click("//select[@id='countryLimit-t']/option");
      $this->click("//input[@name='remove' and @value='<< Remove' and @type='button' and @onclick=\"QFAMS.moveSelection('countryLimit', this.form.elements['countryLimit-f[]'], this.form.elements['countryLimit-t[]'], this.form.elements['countryLimit[]'], 'remove', 'none'); return false;\"]");
      $this->addSelection("countryLimit-f", "label=Afghanistan");
      $this->removeSelection("countryLimit-f", "label=Afghanistan");
      $this->addSelection("countryLimit-f", "label=Cambodia");
      $this->removeSelection("countryLimit-f", "label=Cambodia");
      $this->addSelection("countryLimit-f", "label=Cameroon");
      $this->removeSelection("countryLimit-f", "label=Cameroon");
      $this->addSelection("countryLimit-f", "label=Canada");
      $this->click("//input[@name='add' and @value='Add >>' and @type='button' and @onclick=\"QFAMS.moveSelection('countryLimit', this.form.elements['countryLimit-f[]'], this.form.elements['countryLimit-t[]'], this.form.elements['countryLimit[]'], 'add', 'none'); return false;\"]");
      $this->click("_qf_Localization_next-bottom");
      $this->waitForPageToLoad("30000");
      try {
          $this->assertFalse($this->isTextPresent("Your changes have been saved."));
      } catch (PHPUnit_Framework_AssertionFailedError $e) {
          array_push($this->verificationErrors, $e->toString());
      }
  }
}
?>
