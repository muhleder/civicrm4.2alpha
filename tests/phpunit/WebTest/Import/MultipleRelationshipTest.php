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

 
class WebTest_Import_MultipleRelationshipTest extends ImportCiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

   /*
    *  Test Multiple Relationship import for Individuals.
    */
  function testMultipleRelationshipImport()
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
      
      // Get sample import data.
      list($headers, $rows, $fieldMapper) = $this->_individualRelationshipCSVData( );

      // Import Individuals with multiple relationships
      $this->importContacts($headers, $rows, 'Individual', 'Skip', $fieldMapper);
      
  }
  
  /*
   * Helper function to provide data for multiple relationship import
   * for Individuals. 
   */
  function _individualRelationshipCSVData( ) {
      
      $headers = array( 'first_name'  => 'First Name',
                        'middle_name' => 'Middle Name',
                        'last_name'   => 'Last Name',
                        'email'       => 'Email',
                        'phone'       => 'Phone',  
                        'address_1'   => 'Additional Address 1',
                        'address_2'   => 'Additional Address 2',
                        'city'        => 'City',
                        'state'       => 'State',
                        'country'     => 'Country',
                        'contact_relationships' => 
                        array( '4_a_b' => array( 'organization_name'    => 'Organization Name',
                                                 'organization_email'   => 'Organization Email',
                                                 'organization_add'     => 'Organization Street Address',
                                                 'organization_city'    => 'Organization City',
                                                 'organization_state'   => 'Organization State',
                                                 'organization_country' => 'Organization Country',
                                                 ),
                               '7_a_b' => array( 'household_name'       => 'Household Name',
                                                 'household_email'      => 'Household Name',
                                                 'household_add'        => 'Household Street Address',
                                                 'household_city'       => 'Household City',
                                                 'household_state'      => 'Household State',
                                                 'household_country'    => 'Household Country',
                                                 ),
                               '2_a_b' => array( 'spouse_f_name'        => 'Spouse First Name',
                                                 'spouse_l_name'        => 'Spouse Last Name',
                                                 'spouse_email'         => 'Spouse Email',
                                                 'spouse_add'           => 'Spouse Street Address',
                                                 'spouse_city'          => 'Spouse City',
                                                 'spouse_state'         => 'Spouse State',
                                                 'spouse_country'       => 'Spouse Country',
                                                 ), 
                               )
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
                        'country'     => 'United States',
                        'contact_relationships' => 
                        array( '4_a_b' => array( 'organization_name'    => 'Org ' . substr(sha1(rand()), 0, 7),
                                                 'organization_email'   => substr(sha1(rand()), 0, 7) . 'org@example.org',
                                                 'organization_add'     => 'Org Street Address',
                                                 'organization_city'    => 'Org City',
                                                 'organization_state'   => 'NY',
                                                 'organization_country' => 'United States',
                                                 ),
                               '7_a_b' => array( 'household_name'       => 'House ' . substr(sha1(rand()), 0, 7),
                                                 'household_email'      => substr(sha1(rand()), 0, 7) . 'house@example.org',
                                                 'household_add'        => 'House Street Address',
                                                 'household_city'       => 'House City',
                                                 'household_state'      => 'NY',
                                                 'household_country'    => 'United States',
                                                 ),
                               '2_a_b' => array( 'spouse_f_name'        => substr(sha1(rand()), 0, 7),
                                                 'spouse_l_name'        => substr(sha1(rand()), 0, 7),
                                                 'spouse_email'         => substr(sha1(rand()), 0, 7) . 'spouse@example.org',
                                                 'spouse_add'           => 'Spouse Street Address',
                                                 'spouse_city'       => 'Spouse City',
                                                 'spouse_state'      => 'NY',
                                                 'spouse_country'    => 'United States',
                                                 ), 
                               ) ),
                
                array(  'first_name'  => substr(sha1(rand()), 0, 7),
                        'middle_name' => substr(sha1(rand()), 0, 7) ,
                        'last_name'   => 'Summerson',
                        'email'       => substr(sha1(rand()), 0, 7).'@example.com',
                        'phone'       => '6944412154',  
                        'address_1'   => 'Add 1',
                        'address_2'   => 'Add 2',
                        'city'        => 'Watson',
                        'state'       => 'NY',
                        'country'     => 'United States',
                        'contact_relationships' => 
                        array( '4_a_b' => array( 'organization_name'    => 'Org ' . substr(sha1(rand()), 0, 7),
                                                 'organization_email'   => substr(sha1(rand()), 0, 7) . 'org@example.org',
                                                 'organization_add'     => 'Org Street Address',
                                                 'organization_city'    => 'Org City',
                                                 'organization_state'   => 'NY',
                                                 'organization_country' => 'United States',
                                                 ),
                               '7_a_b' => array( 'household_name'       => 'House ' . substr(sha1(rand()), 0, 7),
                                                 'household_email'      => substr(sha1(rand()), 0, 7) . 'house@example.org',
                                                 'household_add'        => 'House Street Address',
                                                 'household_city'       => 'House City',
                                                 'household_state'      => 'NY',
                                                 'household_country'    => 'United States',
                                                 ),
                               '2_a_b' => array( 'spouse_f_name'        => substr(sha1(rand()), 0, 7),
                                                 'spouse_l_name'        => substr(sha1(rand()), 0, 7),
                                                 'spouse_email'         => substr(sha1(rand()), 0, 7) . 'spouse@example.org',
                                                 'spouse_add'           => 'Spouse Street Address',
                                                 'spouse_city'       => 'Spouse City',
                                                 'spouse_state'      => 'NY',
                                                 'spouse_country'    => 'United States',
                                                 ), 
                               ) ),
                 );
      
      $fieldMapper = array( // for Employee of relationship
                            'mapper[10][0]' => '4_a_b',
                            'mapper[10][1]' => 'organization_name',
                            'mapper[11][0]' => '4_a_b',
                            'mapper[11][1]' => 'email',
                            'mapper[12][0]' => '4_a_b',
                            'mapper[12][1]' => 'street_address',
                            'mapper[13][0]' => '4_a_b',
                            'mapper[13][1]' => 'city',
                            'mapper[14][0]' => '4_a_b',
                            'mapper[14][1]' => 'state_province',
                            'mapper[15][0]' => '4_a_b',
                            'mapper[15][1]' => 'country',
                            
                            // for Household Member of relationship
                            'mapper[16][0]' => '7_a_b',
                            'mapper[16][1]' => 'household_name',
                            'mapper[17][0]' => '7_a_b',
                            'mapper[17][1]' => 'email',
                            'mapper[18][0]' => '7_a_b',
                            'mapper[18][1]' => 'street_address',
                            'mapper[19][0]' => '7_a_b',
                            'mapper[19][1]' => 'city',
                            'mapper[20][0]' => '7_a_b',
                            'mapper[20][1]' => 'state_province',
                            'mapper[21][0]' => '7_a_b',
                            'mapper[21][1]' => 'country',
                            
                            // for Spouse of relationship
                            'mapper[22][0]' => '2_a_b',
                            'mapper[22][1]' => 'first_name',
                            'mapper[23][0]' => '2_a_b',
                            'mapper[23][1]' => 'last_name',
                            'mapper[24][0]' => '2_a_b',
                            'mapper[24][1]' => 'email',
                            'mapper[25][0]' => '2_a_b',
                            'mapper[25][1]' => 'street_address',
                            'mapper[26][0]' => '2_a_b',
                            'mapper[26][1]' => 'city',
                            'mapper[27][0]' => '2_a_b',
                            'mapper[27][1]' => 'state_province',
                            'mapper[28][0]' => '2_a_b',
                            'mapper[28][1]' => 'country',
                            );

      return array($headers, $rows, $fieldMapper);
  }

}
