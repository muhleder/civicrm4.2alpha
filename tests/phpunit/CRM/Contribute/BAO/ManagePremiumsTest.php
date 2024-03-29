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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CRM/Contribute/BAO/ManagePremiums.php';

class CRM_Contribute_BAO_ManagePremiumsTest extends CiviUnitTestCase 
{
    
    function get_info( ) 
    {
        return array(
                     'name'        => 'ManagePremiums BAOs',
                     'description' => 'Test all Contribute_BAO_Contribution methods.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    function setUp( ) 
    {
        parent::setUp();
    }
    
 
    /**
     * check method add()
     */
    function testAdd()
    {
        $ids    = array( );
        $params = array (
                         'name' => 'Test Product',
                         'sku'  => 'TP-10',
                         'imageOption' => 'noImage',
                         'price' => 12,
                         'cost' => 5,
                         'min_contribution' => 5,
                         'is_active' => 1,

                        );

        $product = CRM_Contribute_BAO_ManagePremiums::add( $params, $ids );

        $result = $this->assertDBNotNull( 'CRM_Contribute_BAO_ManagePremiums', $product->id,
                                          'sku', 'id',
                                          'Database check on updated product record.' );
        
        $this->assertEquals( $result, 'TP-10', 'Verify products sku.');
    }
    
    /**
     * check method retrieve( )
     */
    function testRetrieve( ) 
    {
        $ids    = array( );
        $params = array (
                         'name' => 'Test Product',
                         'sku'  => 'TP-10',
                         'imageOption' => 'noImage',
                         'price' => 12,
                         'cost' => 5,
                         'min_contribution' => 5,
                         'is_active' => 1,
                        );

        $product = CRM_Contribute_BAO_ManagePremiums::add( $params, $ids );
        $params  = array( 'id' => $product->id );
        $default = array( );
        $result  = CRM_Contribute_BAO_ManagePremiums::retrieve( $params, $default );
        $this->assertEquals( empty($result) , false , 'Verify products record.');
    } 
   
    /**
     * check method setIsActive( )
     */
    function testSetIsActive( ) 
    { 
        $ids    = array( );
        $params = array (
                         'name' => 'Test Product',
                         'sku'  => 'TP-10',
                         'imageOption' => 'noImage',
                         'price' => 12,
                         'cost' => 5,
                         'min_contribution' => 5,
                         'is_active' => 1,
                        );

        $product = CRM_Contribute_BAO_ManagePremiums::add( $params, $ids );
        CRM_Contribute_BAO_ManagePremiums::setIsActive( $product->id , 0 );
        
        $isActive = $this->assertDBNotNull( 'CRM_Contribute_BAO_ManagePremiums',$product->id ,
                                            'is_active', 'id',
                                            'Database check on updated for product records is_active.' );
        
        $this->assertEquals( $isActive, 0, 'Verify product records is_active.');

    }

    /**
     * check method del( )
     */
    function testDel( ) 
    {
        $ids    = array( );
        $params = array (
                         'name' => 'Test Product',
                         'sku'  => 'TP-10',
                         'imageOption' => 'noImage',
                         'price' => 12,
                         'cost' => 5,
                         'min_contribution' => 5,
                         'is_active' => 1,
                        );

        $product = CRM_Contribute_BAO_ManagePremiums::add( $params, $ids );
        
        CRM_Contribute_BAO_ManagePremiums::del( $product->id );

        $params  = array('id' => $product->id );
        $default = array( );
        $result  = CRM_Contribute_BAO_ManagePremiums::retrieve( $params, $defaults );

        $this->assertEquals( empty($result), true, 'Verify product record deletion.');
         
    }
}
?>