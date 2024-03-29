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


//Tests for the ability to add a CMS user from a contact's record
//See http://issues.civicrm.org/jira/browse/CRM-8723
 
class WebTest_Contact_CreateCmsUserFromContactTest extends CiviSeleniumTestCase {
    
  protected function setUp()
  {
      parent::setUp();
  }



  //Test that option to create a cms user is present on a contact who does not
  //have a cms account already( in this case, a new contact )
  function testCreateContactLinkPresent( ) {
    $this->open( $this->sboxPath );

    //login
    $this->webtestLogin( true );

    //create a New Contact
    $firstName = substr(sha1(rand()), 0, 7)."John";
    $lastName  = substr(sha1(rand()), 0, 7)."Smith";
    $email     = $this->webtestAddContact( $firstName, $lastName, true );

    //Assert that the user actually does have a CMS Id displayed
    $this->assertTrue( ! $this->isTextPresent("User ID"));

    //Assert that the contact user record link says create user record
    $this->assertElementContainsText("css=#actions li.crm-contact-user-record", "Create User Record", "Create User Record link not in action menu of new contact");
  }

  //Test that the action link is missing for users who already have a contact
  //record. The contact record for drupal user 1 is used
  function testCreateContactLinkMissing( ) {
    $this->open( $this->sboxPath );

    //login
    $this->webtestLogin( true );

    // go to My Account page
    $this->open( $this->sboxPath . "user" );

    // click "View Contact Record" link
    $this->waitForElementPresent("link=» View Contact Record");
    $this->click("link=» View Contact Record");
    $this->waitForPageToLoad("30000");

    //Assert that the user actually does have a CMS Id displayed
    $this->assertTrue($this->isTextPresent("User ID"));

    //Assert that the text of the user record link does not say Create User Record
    $this->assertElementNotContainsText("css=#actions li.crm-contact-user-record", "Create User Record", "Create User Record link not in action menu of new contact");
  }

  //Test the ajax "check username availibity" link when adding cms user
  function testCheckUsernameAvailability( ) {
    $this->open( $this->sboxPath );
    $this->webtestLogin( true );

    $email = $this->_createUserAndGotoForm();
    $password = "abc123";

    //use the username of the admin user to test if the username is taken
    $username = $this->settings->adminUsername;

    $this->_fillCMSUserForm($username, $password, $password);
    $this->click("checkavailability");
    $this->waitForCondition("selenium.browserbot.getCurrentWindow().jQuery('#msgbox').text() != 'Checking...'");
    $this->assertElementContainsText("msgbox", "This username is taken", "Taken username is indicated as being available");

    //fill the form with a good username 
    $username = sha1(rand());
    $this->_fillCMSUserForm($username, $password, $password);
    $this->click("checkavailability");
    $this->waitForCondition("selenium.browserbot.getCurrentWindow().jQuery('#msgbox').text() != 'Checking...'");
    $this->assertElementContainsText("msgbox", "This username is currently available", "Available username is indicated as being taken");

  }


  //Test form submission when the username is taken
  function testTakenUsernameSubmission( ) {
    $this->open( $this->sboxPath );

    //login
    $this->webtestLogin( true );

    //create a New Contact
    list($cid, $firstName, $lastName, $email) = $this->_createUserAndGotoForm();
    $password  = 'abc123';

    
    //submit the form with the bad username
    $username = $this->settings->adminUsername;
    $this->_fillCMSUserForm($username, $password, $password);
    $this->click("_qf_Useradd_next-bottom");   
    $this->waitForPageToLoad("30000");
    
    //the civicrm messages should indicate the username is taken
    $this->assertElementContainsText("css=#crm-container div.messages", "already taken", "CiviCRM Message does not indicate the username is in user");

    //check the uf match table that no contact has been created
    $results = $this->webtest_civicrm_api("UFMatch", "get", array('contact_id' => $cid));
    $this->assertTrue($results['count'] == 0);
  }

  //Test form sumbission when user passwords dont match 
  function testMismatchPasswordSubmission( ) {
    $this->open( $this->sboxPath );

    //login
    $this->webtestLogin( true );

    //create a New Contact
    list($cid, $firstName, $lastName, $email) = $this->_createUserAndGotoForm();
    $password  = 'abc123';

    
    //submit with mismatch passwords
    $username = $this->settings->adminUsername;
    $this->_fillCMSUserForm($username, $password, $password . "mismatch");
    $this->click("_qf_Useradd_next-bottom");   
    $this->waitForPageToLoad("30000");

    //check that that there is a password mismatch text
    $this->assertElementContainsText("css=#crm-container div.crm-error", "Password mismatch", "No form error given on password missmatch");

    //check that no user was created;
    $results = $this->webtest_civicrm_api("UFMatch", "get", array('contact_id' => $cid));
    $this->assertTrue($results['count'] == 0);
  }

  function testMissingDataSubmission( ) {
    $this->open( $this->sboxPath );

    //login
    $this->webtestLogin( true );

    //create a New Contact
    list($cid, $firstName, $lastName, $email) = $this->_createUserAndGotoForm();
    $password  = 'abc123';

    
    //submit with mismatch passwords
    $username = $this->settings->adminUsername;
    $this->click("_qf_Useradd_next-bottom");   
    $this->waitForPageToLoad("30000");

    //the civicrm messages section should not indicate that a user has been created
    $this->assertElementNotContainsText("css=#crm-container div.messages", "User has been added", "CiviCRM messages say that a user was created when username left blank");

    //the civicrm message should say username is required
    $this->assertElementContainsText("css=#crm-container div.messages", "Username is required", "The CiviCRM messae does not indicate that the username is required");

    //the civicrm message should say password is required
    $this->assertElementContainsText("css=#crm-container div.messages", "Password is required", "The CiviCRM messae does not indicate that the password is required");


    //check that no user was created;
    $results = $this->webtest_civicrm_api("UFMatch", "get", array('contact_id' => $cid));
    $this->assertTrue($results['count'] == 0);
  }

  //Test a valid (username unique and passwords match) submission
  function testValidSubmission( ) {
    $this->open( $this->sboxPath );

    //login
    $this->webtestLogin( true );

    //create a New Contact
    list($cid, $firstName, $lastName, $email) = $this->_createUserAndGotoForm();
    $password  = 'abc123';

    
    //submit with matching passwords
    $this->_fillCMSUserForm($firstName, $password, $password );
    $this->click("_qf_Useradd_next-bottom");   
    $this->waitForPageToLoad("30000");
    
    //drupal messages should say user created
    $this->assertTrue($this->isTextPresent("Created a new user"), "Drupal does not report success creating user in the message");

    //civicrm messages should indicate success
    $this->assertElementContainsText("css=#crm-container div.messages", "User has been added", "CiviCRM message does not report success in the message");

    //The new user id should be on the page
    $this->assertTrue($this->isTextPresent("User ID"));

    //Assert that a user was actually created AND that they are tied to the record
    $results = $this->webtest_civicrm_api("UFMatch", "get", array('contact_id' => $cid));
    $this->assertTrue($results['count'] == 1);
  }


  function _fillCMSUserForm($username, $password, $confirm_password) {
    $this->type("cms_name", $username);
    $this->type("cms_pass", $password);
    $this->type("cms_confirm_pass", $confirm_password);
  }

  function _createUserAndGoToForm( ) {
    $firstName = substr(sha1(rand()), 0, 7)."John";
    $lastName  = substr(sha1(rand()), 0, 7)."Smith";
    $email     = $this->webtestAddContact( $firstName, $lastName, true );

    // Get the contact id of the new contact
    $contactUrl = $this->parseURL( );
    $cid = $contactUrl['queryString']['cid'];
    $this->assertType( 'numeric', $cid );
    
    //got to the new cms user form
    $this->open($this->sboxPath . "civicrm/contact/view/useradd?reset=1&action=add&cid=" . $cid );
    $this->waitForPageToLoad("30000");

    return array($cid, $firstName, $lastName, $email);
    
  }

  
}
