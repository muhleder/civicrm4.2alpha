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

class WebTest_Import_GroupTest extends ImportCiviSeleniumTestCase {
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
    protected function setUp() {
        parent::setUp();
    }
    
    /*
     *  Test contact import for Individuals.
     */
    function testIndividualImportWithGroup() {

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
        
        // Get sample import data.
        list($headers, $rows) = $this->_individualGroupCSVData( );
        
        // Group Name
        $groupName = substr(sha1(rand()), 0, 7);
        
        // Import and check Individual Contacts in Skip mode and Add them in Group
        $other = array( 'createGroup'     => true,
                        'createGroupName' => $groupName
                        );
        
        // Create New Group And Import Contacts In Group
        $this->importContacts($headers, $rows, 'Individual', 'Skip', array( ), $other );

        $count = count($rows);
        
        // Direct URL To Search
        $this->open($this->sboxPath . "/civicrm/contact/search?reset=1");
        
        // Select GroupName
        $this->select("group", "label={$groupName}");
        
        $this->click("_qf_Basic_refresh");
        $this->waitForPageToLoad("30000");
        
        // To Check Number Of Imported Contacts
        $this->assertTrue($this->isTextPresent("{$count} Contacts"), "Contacts Not Found");
        
        // To Add New Contacts In Already Existing Group
        $other = array( 'selectGroup' => $groupName );
        
        // Create New Individual Record
        list($headers, $rows) = $this->_individualGroupCSVData( );
        
        // Import Contacts In Existing Group
        $this->importContacts($headers, $rows, 'Individual', 'Skip', array( ), $other );
        $count += count($rows);
        
        // Direct URL To Search
        $this->open($this->sboxPath . "/civicrm/contact/search?reset=1");
        
        // Select GroupName
        $this->select("group", "label={$groupName}");
        
        $this->click("_qf_Basic_refresh");
        $this->waitForPageToLoad("30000");
        
        // To Check Imported Contacts
        $this->assertTrue($this->isTextPresent("{$count} Contacts"), "Contacts Not Found");
    }
    
    /*
     *  Helper function to provide data for contact import for Individuals.
     */
    function _individualGroupCSVData( ) {
        $headers = array( 'first_name'  => 'First Name',
                          'middle_name' => 'Middle Name',
                          'last_name'   => 'Last Name',
                          'email'       => 'Email',
                          'phone'       => 'Phone',  
                          'address_1'   => 'Additional Address 1',
                          'address_2'   => 'Additional Address 2',
                          'city'        => 'City',
                          'state'       => 'State',
                          'country'     => 'Country'
                          );
        
        $rows = 
            array( 
                  array(  'first_name'  => substr(sha1(rand()), 0, 7),
                          'middle_name' => substr(sha1(rand()), 0, 7) ,
                          'last_name'   => 'Anderson',
                          'email'       => substr(sha1(rand()), 0, 7).'@example.com',
                          'phone'       => '6949912154',  
                          'address_1'   => 'Add 1',
                          'address_2'   => 'Add 2',
                          'city'        => 'Watson',
                          'state'       => 'NY',
                          'country'     => 'United States'
                          ),
                  
                  array(  'first_name'  => substr(sha1(rand()), 0, 7),
                          'middle_name' => substr(sha1(rand()), 0, 7) ,
                          'last_name'   => 'Summerson',
                          'email'       => substr(sha1(rand()), 0, 7).'@example.com',
                          'phone'       => '6944412154',  
                          'address_1'   => 'Add 1',
                          'address_2'   => 'Add 2',
                          'city'        => 'Watson',
                          'state'       => 'NY',
                          'country'     => 'United States'
                          )
                   );
        return array($headers, $rows);
    }
}