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

class WebTest_Import_ContributionTest extends ImportCiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testContributionImportIndividual()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list( $headers, $rows ) = $this->_contributionIndividualCSVData( );
        
        // Create and import csv from provided data and check imported data.
        $fieldMapper = array( 'mapper[0][0]' => 'email',
                              'mapper[2][0]' => 'contribution_type',
                              'mapper[4][0]' => 'total_amount'
                              );
        $this->importCSVComponent( 'Contribution', $headers, $rows, 'Individual', 'Insert new contributions', $fieldMapper );
    }
    
    function testContributionImportOrganization()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list($headers, $rows) = $this->_contributionOrganizationCSVData( );
        $fieldMapper = array( 'mapper[0][0]' => 'organization_name', 
                              'mapper[2][0]' => 'contribution_type',
                              'mapper[4][0]' => 'total_amount'
                              );
        $this->importCSVComponent( 'Contribution', $headers, $rows, 'Organization', 'Insert new contributions', $fieldMapper );
    }
    
    function testContributionImportHousehold()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list($headers, $rows) = $this->_contributionHouseholdCSVData( );
        $fieldMapper = array( 'mapper[0][0]' => 'household_name',
                              'mapper[2][0]' => 'contribution_type',
                              'mapper[4][0]' => 'total_amount'
                              );
        $this->importCSVComponent( 'Contribution', $headers, $rows, 'Household', 'Insert new contributions', $fieldMapper );
    }
    
    function _contributionIndividualCSVData( ) 
    {
        $firstName1 = substr(sha1(rand()), 0, 7);
        $email1     = 'mail_' . substr(sha1(rand()), 0, 7) . '@example.com'; 
        $this->webtestAddContact( $firstName1, 'Anderson', $email1 );
        
        $firstName2 = substr(sha1(rand()), 0, 7);
        $email2     = 'mail_' . substr(sha1(rand()), 0, 7) . '@example.com'; 
        $this->webtestAddContact( $firstName2, 'Anderson', $email2 );
            
        $headers = array( 'email'                  => 'Email',
                          'fee_amount'             => 'Fee Amount',
                          'contribution_type'      => 'Contribution Type',
                          'contribution_status_id' => 'Contribution Status',
                          'total_amount'           => 'Total Amount'
                          );
        
        $rows = array( 
                      array( 'email'                  => $email1, 
                             'fee_amount'             => '200',
                             'contribution_type'      => 'Donation',
                             'contribution_status_id' => 'Completed',
                             'total_amount'           => '200'
                             ),
                      
                      array( 'email'                  => $email2,
                             'fee_amount'             => '400',
                             'contribution_type'      => 'Donation',
                             'contribution_status_id' => 'Completed',
                             'total_amount'           => '400'
                             )
                       );
        
        return array( $headers, $rows );
    }
    
    function _contributionHouseholdCSVData( ) 
    {
        $household1 = substr( sha1( rand( ) ), 0, 7 ) . ' home';
        $this->webtestAddHousehold( $household1, true );
        
        $household2 = substr( sha1( rand( ) ), 0, 7 ) . ' home';
        $this->webtestAddHousehold( $household2, true );
        
        $headers = array( 'household'              => 'Household Name',
                          'fee_amount'             => 'Fee Amount',
                          'contribution_type'      => 'Contribution Type',
                          'contribution_status_id' => 'Contribution Status',
                          'total_amount'           => 'Total Amount'
                          );
        
        $rows = 
            array( 
                  array( 'household'              => $household1, 
                         'fee_amount'             => '200',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '200'
                         ),
                  
                  array( 'household'              => $household2,
                         'fee_amount'             => '400',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '400'
                         )
                   );
        
        return array( $headers, $rows );
    } 
    
    function _contributionOrganizationCSVData( ) 
    {
        $organization1 = substr( sha1( rand( ) ), 0, 7 ) . ' org';
        $this->webtestAddOrganization( $organization1, true );
         
        $organization2 = substr( sha1( rand() ), 0, 7 ) . ' org';
        $this->webtestAddOrganization( $organization2, true );
        
        $headers = array( 'organization'           => 'Organization Name',
                          'fee_amount'             => 'Fee Amount',
                          'contribution_type'      => 'Contribution Type',
                          'contribution_status_id' => 'Contribution Status',
                          'total_amount'           => 'Total Amount'
                          );
        
        $rows = 
            array( 
                  array( 'organization'           => $organization1, 
                         'fee_amount'             => '200',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '200'
                         ),
                  
                  array( 'organization'           => $organization2,
                         'fee_amount'             => '400',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '400' 
                         )
                   );
        
        return array( $headers, $rows );   
    }
}