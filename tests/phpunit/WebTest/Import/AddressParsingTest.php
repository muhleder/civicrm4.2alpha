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

class WebTest_Import_AddressParsingTest extends ImportCiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    /*
     *  Function to check for Valid Street Address
     */
    function testValidStreetAddressParsing( )
    {   
        
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Logging in.
        $this->webtestLogin( );
        
        //Go to the URL of Address Setting to enable street address parsing option
        $this->open($this->sboxPath ."civicrm/admin/setting/preferences/address?reset=1");
        $this->waitForPageToLoad("30000");
        
        //check the street address parsing is already enabled
        if ( !$this->isChecked("address_options[13]") ) {
            $this->click("address_options[13]");
            $this->click("_qf_Address_next");
            $this->waitForPageToLoad("30000");
        }
        
        // Get sample import data.
        list( $headers, $rows ) = $this->_validStreetAddressCSVData( );
        
        $this->importContacts( $headers, $rows );
        
        // Get imported contact Ids
        $importedContactIds = $this->_getImportedContactIds( $rows );
        
        //Go to the url of edit contact
        $this->open($this->sboxPath . "civicrm/contact/add?reset=1&action=update&cid={$importedContactIds[1]}");        
        $this->click("addressBlock");
        $this->click("//div[@id='addressBlockId']/div[1]");
        $this->waitForElementPresent("address_1_street_address");
        
        //Edit Address Elements
        $this->click("link=Edit Address Elements");
        $this->waitForElementPresent("address_1_street_unit");
        
        //verify all the address fields whether parsed correctly
        $verifyData = array( 'address_1_street_number' => '22',
                             'address_1_street_name'   => 'Adams Avenue',
                             'address_1_street_unit'   => 'Unit 3c' );
        foreach( $verifyData as $key => $expectedvalue ) {
            $actualvalue = $this->getValue( $key );
            $this->assertEquals( $expectedvalue, $actualvalue );
        }
        
        //Go to the URL of Address Setting to disable street address parsing option
        $this->open($this->sboxPath ."civicrm/admin/setting/preferences/address?reset=1");
        $this->waitForPageToLoad("30000");
        
        //Disable street address parsing
        $this->click("address_options[13]");
        $this->click("_qf_Address_next");
        $this->waitForPageToLoad("30000");
    }
    
    /*
     *  Function to check for Invalid Street Address
     */
    function testInvalidStreetAddressParsing( )
    {
        $this->open( $this->sboxPath );
        
        // Logging in.
        $this->webtestLogin( );
        
        //Go to the URL of Address Setting to enable street address parsing option
        $this->open($this->sboxPath ."civicrm/admin/setting/preferences/address?reset=1");
        $this->waitForPageToLoad("30000");
        
        //check the street address parsing is already enabled
        if ( !$this->isChecked("address_options[13]") ) {
            $this->click("address_options[13]");
            $this->click("_qf_Address_next");
            $this->waitForPageToLoad("30000");
        }
        
        // Get sample import data.
        list( $headers, $rows ) = $this->_invalidStreetAddressCSVData( );
        
        $this->importContacts( $headers, $rows );
        $this->assertTrue($this->isTextPresent("Records imported successfully but unable to parse some of the street addresses"));
        $this->assertTrue($this->isTextPresent("You can Download Street Address Records . You may then edit those contact records and update the street address accordingly."));        
        
        // Get imported contact Ids
        $importedContactIds = $this->_getImportedContactIds( $rows ); 
        
        //Go to the url of edit contact
        $this->open($this->sboxPath . "civicrm/contact/add?reset=1&action=update&cid={$importedContactIds[1]}");
        $this->click("addressBlock");
        $this->click("//div[@id='addressBlockId']/div[1]");
        $this->waitForElementPresent("address_1_street_address");
        
        //Edit Address Elements
        $this->click("link=Edit Address Elements"); 
        $this->waitForElementPresent("address_1_street_unit");
        
        //verify all the address fields whether parsed correctly
        $verifyData = array( 'address_1_street_number' => '',
                             'address_1_street_name'   => '',
                             'address_1_street_unit'   => '' );
        foreach( $verifyData as $key => $expectedvalue ) {
            $actualvalue = $this->getValue( $key );
            $this->assertEquals( $expectedvalue, $actualvalue );
        }
        
        //Go to the URL of Address Setting to disable street address parsing option
        $this->open($this->sboxPath ."civicrm/admin/setting/preferences/address?reset=1");
        $this->waitForPageToLoad("30000");
        
        //Disable street address parsing
        $this->click("address_options[13]");
        $this->click("_qf_Address_next");
        $this->waitForPageToLoad("30000");
    }
    
    /*
     *  Function to check Street Address when Address Parsing is Disabled
     */
    function testStreetAddress( )
    {
        $this->open( $this->sboxPath );
        
        // Logging in.
        $this->webtestLogin( );
        
        //Go to the URL of Address Setting to enable street address parsing option
        $this->open($this->sboxPath ."civicrm/admin/setting/preferences/address?reset=1");
        $this->waitForPageToLoad("30000");
        
        //check the street address parsing is already disabled
        if ($this->isChecked("address_options[13]") ) {
            $this->click("address_options[13]");
            $this->click("_qf_Address_next");
            $this->waitForPageToLoad("30000");
        } 
        
        // Get sample import data.
        list( $headers, $rows ) = $this->_validStreetAddressCSVData( );
        
        $this->importContacts( $headers, $rows );
        
        // Get imported contact Ids
        $importedContactIds = $this->_getImportedContactIds( $rows ); 
        
        //Go to the url of edit contact
        $this->open($this->sboxPath . "civicrm/contact/add?reset=1&action=update&cid={$importedContactIds[1]}");
        $this->click("addressBlock");
        $this->click("//div[@id='addressBlockId']/div[1]");
        $this->waitForElementPresent("address_1_street_address");
        
        //verify the address field
        $verifyData = array( 'address_1_street_address' => '22 Adams Avenue Unit 3c');
        $actualvalue = $this->getValue( 'address_1_street_address' );
        $this->assertEquals( '22 Adams Avenue Unit 3c', $actualvalue );
    }
    
    /*
     *  Helper function to provide csv data with Valid Street Address.
     */ 
    function _validStreetAddressCSVData( ) {
        $headers = array( 'first_name'     => 'First Name',
                          'middle_name'    => 'Middle Name',
                          'last_name'      => 'Last Name',
                          'email'          => 'Email',
                          'phone'          => 'Phone',
                          'street_address' => 'Street Address',
                          'address_1'      => 'Additional Address 1',
                          'address_2'      => 'Additional Address 2',
                          'city'           => 'City',
                          'state'          => 'State',
                          'country'        => 'Country'
                          );
        
        $rows = 
            array( 
                  array(  'first_name'     => 'A'.substr(sha1(rand()), 0, 7),
                          'middle_name'    => substr(sha1(rand()), 0, 7),
                          'last_name'      => substr(sha1(rand()), 0, 7).'and',
                          'email'          => substr(sha1(rand()), 0, 7).'@example.com',
                          'phone'          => '6949912154',
                          'street_address' => '54A Excelsior Ave. Apt 1C', 
                          'address_1'      => 'Add 1',
                          'address_2'      => 'Add 2',
                          'city'           => 'Watson',
                          'state'          => 'NY',
                          'country'        => 'United States'
                          ),
                  
                  array(  'first_name'     => 'S'.substr(sha1(rand()), 0, 7),
                          'middle_name'    => substr(sha1(rand()), 0, 7),
                          'last_name'      => substr(sha1(rand()), 0, 7).'sum',
                          'email'          => substr(sha1(rand()), 0, 7).'@example.com',
                          'phone'          => '6944412154',
                          'street_address' => '22 Adams Avenue Unit 3c',
                          'address_1'      => 'Add 1',
                          'address_2'      => 'Add 2',
                          'city'           => 'Watson',
                          'state'          => 'NY',
                          'country'        => 'United States'
                          )
                   );
        
        return array( $headers, $rows );
    }
    
    /*
     *  Helper function to provide csv data with Invalid Street Address.
     */ 
    function _invalidStreetAddressCSVData( ) {
        $headers = array( 'first_name'     => 'First Name',
                          'middle_name'    => 'Middle Name',
                          'last_name'      => 'Last Name',
                          'email'          => 'Email',
                          'phone'          => 'Phone',
                          'street_address' => 'Street Address',
                          'address_1'      => 'Additional Address 1',
                          'address_2'      => 'Additional Address 2',
                          'city'           => 'City',
                          'state'          => 'State',
                          'country'        => 'Country'
                          );
        
        $rows = 
            array( 
                  array(  'first_name'     => 'A'.substr(sha1(rand()), 0, 7),
                          'middle_name'    => substr(sha1(rand()), 0, 7),
                          'last_name'      => substr(sha1(rand()), 0, 7).'and',
                          'email'          => substr(sha1(rand()), 0, 7).'@example.com',
                          'phone'          => '6949912154',
                          'street_address' => 'West St. Apt 1', 
                          'address_1'      => 'Add 1',
                          'address_2'      => 'Add 2',
                          'city'           => 'Watson',
                          'state'          => 'NY',
                          'country'        => 'United States'
                          ),
                  
                  array(  'first_name'     => 'S'.substr(sha1(rand()), 0, 7),
                          'middle_name'    => substr(sha1(rand()), 0, 7),
                          'last_name'      => substr(sha1(rand()), 0, 7).'sum',
                          'email'          => substr(sha1(rand()), 0, 7).'@example.com',
                          'phone'          => '6944412154',
                          'street_address' => 'SW 440N Lincoln Dr S',
                          'address_1'      => 'Add 1',
                          'address_2'      => 'Add 2',
                          'city'           => 'Watson',
                          'state'          => 'NY',
                          'country'        => 'United States'
                          )
                   );
        
        return array( $headers, $rows );
    }
}
?>
