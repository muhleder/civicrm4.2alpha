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

class WebTest_Contact_RelationshipAddTest extends CiviSeleniumTestCase
{

  protected function setUp()
  {
      parent::setUp();
  }

  function testRelationshipAddTest( )
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
      
      //create a relationship type between different contact types
      $params = array( 'label_a_b'       => 'Owner of '.rand( ),
                       'label_b_a'       => 'Belongs to '.rand( ),
                       'contact_type_a'  => 'Individual',
                       'contact_type_b'  => 'Household',
                       'description'     => 'The company belongs to this individual' );
      
      $this->webtestAddRelationshipType( $params );
      
      //create a New Individual
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Anderson", "$firstName@anderson.name" );
      $sortName    = "Anderson, $firstName";
      $displayName = "$firstName Anderson";
      
      // Go directly to the URL of the screen that you will be testing (New Household).
      $this->open($this->sboxPath . "civicrm/contact/add?reset=1&ct=Household");
      
      //fill in Household name
      $this->click("household_name");
      $name = "Fraddie Grant's home " . substr(sha1(rand()), 0, 7);
      $this->type("household_name", $name );
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForElementPresent("css=.crm-contact-tabs-list");
      
      // visit relationship tab of the household
      $this->click("css=li#tab_rel a");
      
      // wait for add Relationship link
      $this->waitForElementPresent('link=Add Relationship');
      $this->click('link=Add Relationship');
      
      //choose the created relationship type 
      $this->waitForElementPresent("relationship_type_id");
      $this->select('relationship_type_id', "label={$params['label_b_a']}");
      
      //fill in the individual
      $this->typeKeys( 'contact_1', $sortName );
      $this->fireEvent( 'contact_1', 'focus' );
      $this->waitForElementPresent("css=div.ac_results-inner li");
      $this->click("css=div.ac_results-inner li");
      
      $this->waitForElementPresent("quick-save");
      
      //fill in the relationship start date
      $this->webtestFillDate('start_date' , '-2 year' );
      
      $description = "Well here is some description !!!!";
      $this->type("description", $description );
      
      //save the relationship
      //$this->click("_qf_Relationship_upload");
      $this->click("quick-save");
      $this->waitForElementPresent("current-relationships");
      
      //check the status message
      $this->assertTrue($this->isTextPresent("1 new relationship record created."));
      
      $this->waitForElementPresent("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
      $this->click("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
      
      $this->waitForPageToLoad("300000"); 
      $this->webtestVerifyTabularData(
                                      array(
                                            'Description'         => $description,
                                            'Status'	          => 'Enabled'
                                            )
                                      );
      $this->assertTrue( $this->isTextPresent( $params['label_b_a'] ) );
      
      //create a New Individual subtype
      $this->open($this->sboxPath . "civicrm/admin/options/subtype?action=add&reset=1");
      $this->waitForPageToLoad("30000");
      $label = "IndividualSubtype" . substr(sha1(rand()), 0, 4);
      $this->type("label", $label);
      $this->type("description", "here is individual subtype");
      $this->click("_qf_ContactType_next-bottom");
      $this->waitForPageToLoad("30000");
      
      //create a new contact of individual subtype
      $this->open($this->sboxPath . "civicrm/contact/add?ct=Individual&cst={$label}&reset=1");
      $this->waitForElementPresent( '_qf_Contact_upload_view' );
      $firstName = substr(sha1(rand()), 0, 7);
      $lastName = 'And' . substr(sha1(rand()), 0, 7);
      $this->click( "first_name" );
      $this->type( "first_name", $firstName );
      $this->click( "last_name" );
      $this->type( "last_name", $lastName );
      $sortName    = "$lastName, $firstName";
      $displayName = "$firstName $lastName";
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForElementPresent("css=.crm-contact-tabs-list");
      
      //create a New household subtype
      $this->open($this->sboxPath . "civicrm/admin/options/subtype?action=add&reset=1");
      $this->waitForPageToLoad("30000");
      
      $label = "HouseholdSubtype" . substr(sha1(rand()), 0, 4); 
      $householdSubtypeName = $label;
      $this->click("label");
      $this->type("label", $label);
      $this->select("parent_id", "label=Household");
      $this->type("description", "here is household subtype");
      $this->click("_qf_ContactType_next-bottom");
      $this->waitForPageToLoad("30000");
      
      //create a new contact of household subtype
      $this->open($this->sboxPath . "civicrm/contact/add?ct=Household&cst={$label}&reset=1");
      $this->waitForElementPresent( '_qf_Contact_upload_view' );
      
      //fill in Household name
      $householdName = substr(sha1(rand()), 0, 4) . 'home';
      $this->click("household_name");
      $this->type("household_name", $householdName );
      $displayName = $householdName;
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
      
      //choose the created relationship type 
      $this->click( 'css=li#tab_rel a' );
      
      // wait for add Relationship link
      $this->waitForElementPresent( 'link=Add Relationship' );
      $this->click( 'link=Add Relationship' );
      $this->waitForElementPresent("relationship_type_id");
      $this->select('relationship_type_id', "label={$params['label_b_a']}");
      
      //fill in the individual
      $this->typeKeys( 'contact_1', $sortName );
      $this->fireEvent('contact_1', 'focus');
      
      $this->waitForElementPresent("css=div.ac_results-inner li");
      $this->click("css=div.ac_results-inner li");
      
      $this->waitForElementPresent("quick-save");
      
      //fill in the relationship start date
      $this->webtestFillDate('start_date' , '-2 year' );
      
      $description = "Well here is some description !!!!";
      $this->type("description", $description );
      
      //save the relationship
      $this->click("quick-save");
      $this->waitForElementPresent("current-relationships");
      
      $this->waitForElementPresent("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
      $this->click("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
      
      $this->waitForPageToLoad("300000"); 
      $this->webtestVerifyTabularData(
                                      array(
                                            'Description'         => $description,
                                            'Status'	          => 'Enabled'
                                            )
                                      );
      
      $this->assertTrue( $this->isTextPresent( $params['label_b_a'] ) );
      
      //test for individual contact and household subtype contact
      //relationship
      $typeb ="Household" . CRM_Core_DAO::VALUE_SEPARATOR . $householdSubtypeName;
      
      //create a relationship type between different contact types
      $params = array( 'label_a_b'       => 'Owner of '.rand( ),
                       'label_b_a'       => 'Belongs to '.rand( ),
                       'contact_type_a'  => 'Individual',
                       'contact_type_b'  => $typeb,
                       'description'     => 'The company belongs to this individual' );
    
      
      
      //create relationship type
      $this->open( $this->sboxPath . 'civicrm/admin/reltype?reset=1&action=add' );
      $this->type( 'label_a_b', $params['label_a_b'] );
      $this->type( 'label_b_a', $params['label_b_a'] );
      $this->select( 'contact_types_a', "value={$params['contact_type_a']}" );
      $this->select( 'contact_types_b', "value={$params['contact_type_b']}" );
      $this->type( 'description', $params['description'] );
      
      $params['contact_type_b'] = preg_replace('/' . CRM_Core_DAO::VALUE_SEPARATOR . '/',' - ',$params['contact_type_b']);
      
      //save the data.
      $this->click( '_qf_RelationshipType_next-bottom' );
      $this->waitForPageToLoad( '30000' );
      
      //does data saved.
      $this->assertTrue( $this->isTextPresent( 'The Relationship Type has been saved.' ), 
                         "Status message didn't show up after saving!" );
      
      $this->open( $this->sboxPath . 'civicrm/admin/reltype?reset=1' );
      $this->waitForPageToLoad( '30000' );
      
      //validate data on selector.
      $data = $params;
      if ( isset( $data['description'] ) ) {
          unset( $data['description'] );
      }
      $this->assertStringsPresent( $data );
      
      //create a New Individual
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Anderson", "$firstName@anderson.name" );
      $sortName    = "Anderson, $firstName";
      $displayName = "$firstName Anderson";
      
      //create a new contact of household subtype
      $this->open( $this->sboxPath . "civicrm/contact/add?ct=Household&cst=".$householdSubtypeName."&reset=1" );
      $this->waitForElementPresent( '_qf_Contact_upload_view' );
      
      //fill in Household name
      $householdName = substr(sha1(rand()), 0, 4) . 'home';
      $this->click( "household_name" );
      $this->type( "household_name", $householdName );
      $displayName = $householdName;
      
      // Clicking save.
      $this->click( "_qf_Contact_upload_view" );
      $this->waitForPageToLoad( "30000" );
      
      //choose the created relationship type 
      $this->click( 'css=li#tab_rel a' );
      
      // wait for add Relationship link
      $this->waitForElementPresent( 'link=Add Relationship' );
      $this->click( 'link=Add Relationship' );
      $this->waitForElementPresent( "relationship_type_id" );
      $this->select( 'relationship_type_id', "label={$params['label_b_a']}" );
      
      //fill in the individual
      $this->typeKeys( 'contact_1', $sortName );
      $this->fireEvent( 'contact_1', 'focus' );
      
      $this->waitForElementPresent( "css=div.ac_results-inner li" );
      $this->click( "css=div.ac_results-inner li" );
      
      $this->waitForElementPresent( "quick-save" );
      
      //fill in the relationship start date
      $this->webtestFillDate( 'start_date', '-2 year' );
      
      $description = "Well here is some description !!!!";
      $this->type( "description", $description );
      
      //save the relationship
      $this->click( "quick-save" );
      $this->waitForElementPresent( "current-relationships" );
      
      $this->waitForElementPresent( "xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']" );
      $this->click( "xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']" );
      
      $this->waitForPageToLoad( "300000" ); 
      $this->webtestVerifyTabularData(
                                      array(
                                            'Description' => $description,
                                            'Status'      => 'Enabled'
                                            )
                                      );
      
      $this->assertTrue( $this->isTextPresent( $params['label_b_a'] ) );
     
  }  

  function testRelationshipAddNewIndividualTest( )
  {
      $this->open( $this->sboxPath );
      
      $this->webtestLogin( );
      
      //create a relationship type between different contact types
      $params = array( 'label_a_b'       => 'Board Member of '.rand( ),
                       'label_b_a'       => 'Board Member is'.rand( ),
                       'contact_type_a'  => 'Individual',
                       'contact_type_b'  => 'Organization',
                       'description'     => 'Board members of organizations.' );
      
      $this->webtestAddRelationshipType( $params );
      
      //create a New Individual
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Anderson", "$firstName@anderson.name" );
      $sortName    = "Anderson, $firstName";
      $displayName = "$firstName Anderson";
            
      // visit relationship tab of the Individual
      $this->click("css=li#tab_rel a");
      
      // wait for add Relationship link
      $this->waitForElementPresent('link=Add Relationship');
      $this->click('link=Add Relationship');
      
      //choose the created relationship type 
      $this->waitForElementPresent("relationship_type_id");
      $this->select('relationship_type_id', "label={$params['label_a_b']}");
      
      // create a new organization
      $orgName = 'WestsideCoop' . substr(sha1(rand()), 0, 7);
      $this->webtestNewDialogContact( $orgName, "", "info@" . $orgName . ".com", 5 );
      
      $this->waitForElementPresent("quick-save");
      
      //fill in the relationship start date
      $this->webtestFillDate('start_date' , '-2 year' );
      
      $description = "Long-standing board member.";
      $this->type("description", $description );
      
      //save the relationship
      //$this->click("_qf_Relationship_upload");
      $this->click("quick-save");
      $this->waitForElementPresent("current-relationships");
      
      //check the status message
      $this->assertTrue($this->isTextPresent("1 new relationship record created."));
      
      $this->waitForElementPresent("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
      $this->click("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
      
      $this->waitForPageToLoad("300000"); 
      $this->webtestVerifyTabularData(
                                      array(
                                            'Description'         => $description,
                                            'Status'	          => 'Enabled'
                                            )
                                      );
      $this->assertTrue( $this->isTextPresent( $params['label_a_b'] ) );
   }

}
?>
