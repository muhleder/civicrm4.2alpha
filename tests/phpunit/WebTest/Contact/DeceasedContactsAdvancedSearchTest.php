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


 
class WebTest_Contact_DeceasedContactsAdvancedSearchTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testDeceasedContactsAdvanceSearch( )
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
      $this->webtestLogin( );
      $this->waitForPageToLoad("30000");

      // Create a group
      $groupName = $this->WebtestAddGroup( );

      // Add contacts from the quick add block
      $lastName = substr(sha1(rand()), 0, 7);
      $names = array( 'firstName1' => false,
                      'firstName2' => true,
                      'firstName3' => false,
                      'firstName4' => true,
                      'firstName5' => true
                      );

      foreach ( $names as $key => $value ) {
          $$key = substr(sha1(rand()), 0, 7);
          $this->_testAddContact( $$key, $lastName, "{$$key}.{$lastName}@example.com", $groupName, $value );
      }

      // Advanced Search
      $this->open( $this->sboxPath . 'civicrm/contact/search/advanced?reset=1' );
      $this->waitForElementPresent( '_qf_Advanced_refresh' );

      // Select the group and check deceased contacts
      $this->select( 'crmasmSelect1', "label={$groupName}" );
      $this->click( 'demographics' );
      $this->waitForElementPresent( 'CIVICRM_QFID_1_5' );
      $this->click( 'CIVICRM_QFID_0_7' );
      $this->click( '_qf_Advanced_refresh' );

      // Remove contacts from group
      $this->waitForElementPresent( 'Go' );
      $this->assertTrue( $this->isTextPresent( '2 Contacts' ) );
      $this->click( "xpath=//form[@id='Advanced']/div[3]/div/div[2]/table/thead/tr/th/input" );
     
      $this->select( 'task', 'label=Remove Contacts from Group' );
      $this->click( "xpath=//div[@id='search-status']/table/tbody/tr[3]/td/ul/input[2]" );
      $this->waitForElementPresent( '_qf_RemoveFromGroup_back-bottom' );
      $this->assertTrue( $this->isTextPresent( 'Number of selected contacts: 2' ) );
      $this->select( 'group_id', "label={$groupName}" );
      $this->click( '_qf_RemoveFromGroup_next-bottom' );
      $this->waitForPageToLoad( '30000' );
      $this->assertTrue( $this->isTextPresent( "Removed Contact(s) from {$groupName}" ) );

      // Search for the contacts who are not deceased 
      $this->open( $this->sboxPath . 'civicrm/contact/search/advanced?reset=1' );
      $this->waitForElementPresent( '_qf_Advanced_refresh' );
      $this->select( 'crmasmSelect1', "label={$groupName}" );
      $this->click( '_qf_Advanced_refresh' );
      
      // Check if non-deceased contacts are still present
      $this->waitForElementPresent( 'Go' );
      $this->assertTrue( $this->isTextPresent( '3 Contacts' ) );
  }

  function _testAddContact( $firstName, $lastName, $email, $groupName, $deceased = false )
  {
      $this->webtestAddContact( $firstName, $lastName, $email );
      if ( $deceased ) {
          $this->click( 'link=Edit' );
          $this->waitForElementPresent( '_qf_Contact_cancel-bottom' );
          
          // Click on the Demographics tab
          $this->click( 'demographics' );
          $this->waitForElementPresent( 'is_deceased' );
          $this->click( 'is_deceased' );

          // Click on Save
          $this->click( '_qf_Contact_upload_view-bottom' );
          $this->waitForPageToLoad( '30000' );
      }

      // Add contact to group
      $this->click( 'css=#tab_group a' );
      $this->waitForElementPresent( '_qf_GroupContact_next' );
      $this->select( 'group_id', "{$groupName}" );
      $this->click( '_qf_GroupContact_next' );
      $this->waitForPageToLoad( '30000' );
  }
}
      