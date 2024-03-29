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

require_once 'WebTest/Import/ImportCiviSeleniumTestCase.php';

 
class WebTest_Import_ContactCustomDataTest extends ImportCiviSeleniumTestCase {
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testCustomDataImport( ) 
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
        
        $firstName1 = 'Ma_' . substr(sha1(rand()), 0, 7);
        // Add a custom group and custom field
        $customDataParams = $this->_addCustomData( );
        
        // Add New Strict Rule
        $newRuleTitle ='IndividualStrict_' . substr(sha1(rand()), 0, 7);      
        $this->open( $this->sboxPath . "civicrm/contact/deduperules?reset=1" );
        $this->waitForPageToLoad( "30000" );
        
        $this->click( "xpath=//div[@class='action-link']/a" );
        $this->waitForElementPresent( '_qf_DedupeRules_next-bottom' );
        $this->type( 'title', $newRuleTitle );
        $this->select( 'level', 'label=Strict' );
        $this->select( "where_0", "label=$customDataParams[1]" );
        $this->type( 'weight_0','10' );
        $this->type( 'threshold','10' );
        $this->click( '_qf_DedupeRules_next-bottom' );
        $this->waitForPageToLoad( "30000" );
        $this->assertTrue( $this->isTextPresent( "The rule '{$newRuleTitle}' has been saved." ) );

        $rgId = explode( '&rgid=', $this->getAttribute( "xpath=//div[@id='browseValues']//table/tbody//tr/td[text()='{$newRuleTitle}']/../td[3][text()='Strict']/../td[5]/span/a[text()='Use Rule']@href") );
        $rgId = explode('&', $rgId[1]);
        
        // Add Contact
        $firstName2 = 'An_' . substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName2, "Summerson" );
        $this->waitForPageToLoad( "30000" );

        // Edit and expand all tabs
        $this->click( 'link=Edit' );
        $this->waitForElementPresent( '_qf_Contact_cancel' );
        $this->click( 'link=Expand all tabs' );
        
        // Fill custom data
        $this->waitForElementPresent( "{$customDataParams[0]}_1" );
        $this->type( "{$customDataParams[0]}_1", 'This is a test field' );
        $this->click( '_qf_Contact_upload_view' );
                
        // Get sample import data.
        list( $headers, $rows ) = $this->_individualCustomCSVData( $customDataParams, $firstName1 );

        // Import and check Individual contacts in Skip mode.
        $other = array( 'saveMapping'           => true,
                        'callbackImportSummary' => 'checkDuplicateContacts',
                        'dedupe'                => $rgId[0]
                        );
        
        // Check duplicates
        $this->importContacts( $headers, $rows, 'Individual', 'Skip', array( ), $other );

        // Import without duplicate checking
        $other = array( 'saveMapping' => true );
        $this->importContacts( $headers, $rows, 'Individual', 'No Duplicate Checking', array( ), $other );
        
        // Type search name in autocomplete.
        $this->typeKeys( 'css=input#sort_name_navigation', $firstName1 );
        $this->click( 'sort_name_navigation' );
      
        // Wait for result list.
        $this->waitForElementPresent("css=div.ac_results-inner li");
      
        // Visit contact summary page.
        $this->click("css=div.ac_results-inner li");
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( 'This is a test field' ) );
    }
    

    /*
     *  Helper function to provide data for custom data import.
     */
    function _individualCustomCSVData( $customDataParams, $firstName1 ) 
    {
        $headers = array( 'first_name'                    => 'First Name',
                          'last_name'                     => 'Last Name',
                          "custom_{$customDataParams[0]}" => "{$customDataParams[1]} :: {$customDataParams[2]}"
                          );

        $rows    = array( array( 'first_name'                    => $firstName1,
                                 'last_name'                     => 'Anderson',
                                 "custom_{$customDataParams[0]}" => 'This is a test field' 
                                 ),
                          );
        
        return array( $headers, $rows );
    }

    function checkDuplicateContacts( $originalHeaders, $originalRows, $checkSummary ) 
    {
        $this->assertTrue( $this->isTextPresent( 'CiviCRM has detected one record which is a duplicate of existing CiviCRM contact record. These records have not been imported.' ) );
    }

    function _addCustomData( )
    {
        // Go directly to the URL of the screen that you will be testing (New Custom Group).
        $this->open( $this->sboxPath . "civicrm/admin/custom/group?reset=1" );
        
        //add new custom data
        $this->click("//a[@id='newCustomDataGroup']/span");
        $this->waitForPageToLoad("30000");
        
        //fill custom group title
        $customGroupTitle = 'Custom '.substr(sha1(rand()), 0, 7);
        $this->click( 'title' );
        $this->type( 'title', $customGroupTitle );
        
        //custom group extends 
        $this->click( 'extends[0]' );
        $this->select( 'extends[0]', "value=Contact" );
        $this->click( "//option[@value='Contact']" );
        $this->click( '_qf_Group_next-bottom' );
        $this->waitForElementPresent( '_qf_Field_cancel-bottom' );
        
        //Is custom group created?
        $this->assertTrue( $this->isTextPresent( "Your custom field set '{$customGroupTitle}' has been added. You can add custom fields now." ) );
        $url = explode( 'gid=', $this->getLocation( ) );
        $gid = $url[1];

        // create another custom field - Date
        $customField = 'Custom field ' . substr( sha1( rand( ) ), 0, 4 );
        $this->type( 'label', $customField );
        
        //enter pre help message
        $this->type("help_pre", "this is field pre help");
        
        //enter post help message
        $this->type("help_post", "this field post help");
        
        //Is searchable?
        $this->click("is_searchable");
        
        // clicking save
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForElementPresent( 'newCustomField' );

        $this->assertTrue( $this->isTextPresent( "Your custom field '{$customField}' has been saved." ) );
        $customFieldId = explode( '&id=', $this->getAttribute( "xpath=//div[@id='field_page']//table/tbody//tr/td[text()='$customField']/../td[8]/span/a@href" ) );
        $customFieldId = $customFieldId[1];

        return array( "custom_{$customFieldId}", $customField, $customGroupTitle );
    }

}