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
 
class WebTest_Contact_AdvanceSearchPrivacyOptionsTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testSearchForPrivacyOptions( )
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
      
      $privacyOptions = array( 
                              'dn_phone_mail'  => array('do_not_phone', 'do_not_mail'),
                              'dn_phone_email' => array('do_not_phone', 'do_not_email'),
                              'dn_trade_sms'   => array('do_not_trade', 'do_not_sms')
                               );
      $randString = substr(sha1(rand()), 0, 7) ;
      
      $contactsReffOptions = array(
                                   'dn_phone_mail'  => array('first_name' => $randString . 'John', 'last_name' => $randString . 'Smith'),
                                   'dn_phone_email' => array('first_name' => $randString . 'Jeff', 'last_name' => $randString . 'Adams'),
                                   'dn_trade_sms'   => array('first_name' => $randString . 'Rocky', 'last_name' => $randString . 'Stanley')
                                   );
      
      //creating individuals
      $this->_addIndividual( $contactsReffOptions['dn_phone_mail']['first_name'], $contactsReffOptions['dn_phone_mail']['last_name'], $privacyOptions['dn_phone_mail'] );
      $this->_addIndividual( $contactsReffOptions['dn_phone_email']['first_name'], $contactsReffOptions['dn_phone_email']['last_name'], $privacyOptions['dn_phone_email'] );
      $this->_addIndividual( $contactsReffOptions['dn_trade_sms']['first_name'], $contactsReffOptions['dn_trade_sms']['last_name'], $privacyOptions['dn_trade_sms'] );
      
      //advance search for created contacts
      $this->open( $this->sboxPath . "civicrm/contact/search/advanced?reset=1" );
      $this->waitForElementPresent('_qf_Advanced_refresh');
      $allPrivacyOptions = array(
                                 'do_not_phone',
                                 'do_not_mail',
                                 'do_not_email',
                                 'do_not_sms', 
                                 'do_not_trade'
                                 );
      
      $this->_addPrivacyCriteria('include', $privacyOptions['dn_phone_mail'], 'OR', $allPrivacyOptions);
      $this->click('_qf_Advanced_refresh');
      $this->waitForPageToLoad('30000');
      
      if ( $this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_phone_mail']['last_name']}, {$contactsReffOptions['dn_phone_mail']['first_name']}']") && $this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_phone_email']['last_name']}, {$contactsReffOptions['dn_phone_email']['first_name']}']") && !$this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_trade_sms']['last_name']}, {$contactsReffOptions['dn_trade_sms']['first_name']}']") ){
          $assertCheck = true;
      } else {
          $assertCheck = false;
      }
      $this->assertTrue( $assertCheck, 'Do not phone / mail assertion failed using criteria(include , OR )' );
     
      $this->_addPrivacyCriteria('exclude', $privacyOptions['dn_phone_mail'], 'OR', $allPrivacyOptions);
      $this->click('_qf_Advanced_refresh');
      $this->waitForPageToLoad('30000');
      
      if ( !$this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_phone_mail']['last_name']}, {$contactsReffOptions['dn_phone_mail']['first_name']}']") && $this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_trade_sms']['last_name']}, {$contactsReffOptions['dn_trade_sms']['first_name']}']") ){
          $assertCheck = true;
      } else {
          $assertCheck = false;
      }
      $this->assertTrue( $assertCheck, 'Do not phone / mail assertion failed using criteria(exclude , OR )' );

      $this->_addPrivacyCriteria('include', $privacyOptions['dn_phone_mail'], 'AND', $allPrivacyOptions);
      $this->click('_qf_Advanced_refresh');
      $this->waitForPageToLoad('30000');
      
      if ( $this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_phone_mail']['last_name']}, {$contactsReffOptions['dn_phone_mail']['first_name']}']") && !$this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_phone_email']['last_name']}, {$contactsReffOptions['dn_phone_email']['first_name']}']") && !$this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_trade_sms']['last_name']}, {$contactsReffOptions['dn_trade_sms']['first_name']}']") ){
          $assertCheck = true;
      } else {
          $assertCheck = false;
      }
      $this->assertTrue( $assertCheck, 'Do not phone / mail assertion failed using criteria(include , AND )' );
      
      $this->_addPrivacyCriteria('exclude', $privacyOptions['dn_phone_mail'], 'AND', $allPrivacyOptions);
      $this->click('_qf_Advanced_refresh');
      $this->waitForPageToLoad('30000');
      
      if ( !$this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_phone_mail']['last_name']}, {$contactsReffOptions['dn_phone_mail']['first_name']}']") && $this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_trade_sms']['last_name']}, {$contactsReffOptions['dn_trade_sms']['first_name']}']") ){
          $assertCheck = true;
      } else {
          $assertCheck = false;
      }
      $this->assertTrue( $assertCheck, 'Do not phone / mail assertion failed using criteria(exclude , AND )' );
      
      $this->_addPrivacyCriteria('include', $privacyOptions['dn_trade_sms'], 'AND', $allPrivacyOptions);
      $this->click('_qf_Advanced_refresh');
      $this->waitForPageToLoad('30000');
      
      if ( !$this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_phone_mail']['last_name']}, {$contactsReffOptions['dn_phone_mail']['first_name']}']") && !$this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_phone_email']['last_name']}, {$contactsReffOptions['dn_phone_email']['first_name']}']") && $this->isElementPresent("xpath=//div[@class='crm-search-results']/table//tr/td[3]/a[text()='{$contactsReffOptions['dn_trade_sms']['last_name']}, {$contactsReffOptions['dn_trade_sms']['first_name']}']") ){
          $assertCheck = true;
      } else {
          $assertCheck = false;
      }
      $this->assertTrue( $assertCheck, 'Do not trade / sms assertion failed using criteria(include , AND )' );
 
  }
  
  function _addPrivacyCriteria($inEx, $privacyOptions, $privacyOperator, $allPrivacyOptions){
      $inExId = ($inEx == 'include') ? 'CIVICRM_QFID_2_8' : 'CIVICRM_QFID_1_6'; 
      $this->click( $inExId );
      $this->select('privacy_operator', "{$privacyOperator}");
      foreach($privacyOptions as $privacyOption){
          $privacyOptionVal = $this->getOptionVal($privacyOption);
          if ( !$this->isElementPresent( "xpath=//ul[@id='crmasmList3']//li//span[text()='{$privacyOptionVal}']" ) ){
              $this->select( 'crmasmSelect3', "value={$privacyOption}" );        
          
              $this->waitForElementPresent( "xpath=//ul[@id='crmasmList3']//li//span[text()='{$privacyOptionVal}']" );
          }
      }
      
      foreach($allPrivacyOptions as $allPrivacyOption){
          
          if(!in_array($allPrivacyOption, $privacyOptions)){
              $privacyOptionVal = $this->getOptionVal($allPrivacyOption); 
              if($this->isElementPresent( "xpath=//ul[@id='crmasmList3']//li//span[text()='{$privacyOptionVal}']" )){
                  $this->click("xpath=//ul[@id='crmasmList3']//li//span[text()='{$privacyOptionVal}']/../a[@class='crmasmListItemRemove']");
              }
          }
      } 
  }
  
  function getOptionVal( $privacyOption ){
      if ($privacyOption == 'do_not_phone'){
          $privacyOptionVal = 'Do not phone';
      } elseif ($privacyOption == 'do_not_mail'){
          $privacyOptionVal = 'Do not mail';
      } elseif ($privacyOption == 'do_not_email'){
          $privacyOptionVal = 'Do not email';
      } elseif ($privacyOption == 'do_not_trade'){
          $privacyOptionVal = 'Do not trade';
      } elseif ($privacyOption == 'do_not_sms'){
          $privacyOptionVal = 'Do not sms';
      }
      return $privacyOptionVal;
  }
  
  function _addIndividual( $firstName, $lastName, $options ){

      $this->open($this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual");

      //fill in first name
      $this->type("first_name", $firstName);
      
      //fill in last name
      $this->type("last_name", $lastName);
            
      //fill in email
      $this->type("email_1_email", "{$firstName}@{$lastName}.com");
      
      //fill in phone
      $this->type("phone_1_phone", "2222-4444");
      
      foreach($options as $option){
          //Select preferred method for Privacy
          $this->click("privacy[{$option}]");
      }
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Your Individual contact record has been saved."));
  }

}

 