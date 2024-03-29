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
require_once 'CiviTest/ContributionPage.php';
require_once 'CRM/Pledge/BAO/PledgeBlock.php';

/**
 * Test class for CRM_Pledge_BAO_PledgeBlock BAO
 *
 *  @package   CiviCRM
 */
class CRM_Pledge_BAO_PledgeBlockTest extends CiviUnitTestCase 
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_contributionPageId = ContributionPage::create();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }


    /**
     *  test info
     */
    function get_info( ) 
    {
        return array(
                     'name'        => 'PledgeBlock BAOs',
                     'description' => 'Test all Pledge_BAO_PledgeBlock methods.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    /**
     *  create() and deletepledgeblock() method
     */
    function testCreateAndDeletePledgeBlock ( ) {
        
        $pledgeFrequencyUnit = array( 'week' => 1,
                                      'month'=> 1,
                                      'year' => 1 );
        
		$params = array( 'entity_id'               => $this->_contributionPageId,
                         'entity_table'            => 'civicrm_contribution_page',
                         'pledge_frequency_unit'   => $pledgeFrequencyUnit,
                         'max_reminders'           => 2,
                         'initial_reminder_day'    => 2,
                         'additional_reminder_day' => 1                          );
        
		//Checking for pledgeBlock id in the Pledge_block table.
        $pledgeBlock = CRM_Pledge_BAO_PledgeBlock::create( $params );
        $pledgeBlockId = $this->assertDBNotNull('CRM_Pledge_DAO_PledgeBlock', $pledgeBlock->id, 'id', 
                                                'id', 'Check DB for Pledge block id');
        
        //Checking for pledgeBlock id after delete.
        CRM_Pledge_BAO_PledgeBlock::deletePledgeBlock( $pledgeBlock->id );
        $pledgeBlockId = $this->assertDBNull('CRM_Pledge_DAO_PledgeBlock', $pledgeBlock->id, 'id', 
                                             'id', 'Check DB for Pledge block id');
        
    }
    
    /**
     * add() method (add and edit modes of pledge block)
     */
    function testAddPledgeBlock( ) {
        
        $pledgeFrequencyUnit = array( 'week' => 1,
                                      'month'=> 1,
                                      'year' => 1 );
        
		$params = array( 'entity_id'               => $this->_contributionPageId,
                         'entity_table'            => 'civicrm_contribution_page',
                         'pledge_frequency_unit'   => $pledgeFrequencyUnit,
                         'max_reminders'           => 2,
                         'initial_reminder_day'    => 2,
                         'additional_reminder_day' => 1                          );
		
        // check for add pledge block
        $pledgeBlock = CRM_Pledge_BAO_PledgeBlock::add($params);
        foreach ( $params as $param => $value ) {
            $this->assertEquals( $value, $pledgeBlock->$param );
        }
		
        $params = array( 'id'                      => $pledgeBlock->id,
                         'entity_id'               => $this->_contributionPageId,
                         'entity_table'            => 'civicrm_contribution_page',
                         'pledge_frequency_unit'   => $pledgeFrequencyUnit,
                         'max_reminders'           => 3,
                         'initial_reminder_day'    => 3,
                         'additional_reminder_day' => 2,
                         'is_pledge_interval'      => 1                   );
        
        // also check for edit pledge block
        $pledgeBlock = CRM_Pledge_BAO_PledgeBlock::add($params);
        foreach ( $params as $param => $value ) {
            $this->assertEquals( $value, $pledgeBlock->$param );
        }
        
    }
    
    /**
     * retrieve() and getPledgeBlock() method of  pledge block
     */
    function testRetrieveAndGetPledgeBlock( ) {
        
        $pledgeFrequencyUnit = array( 'week' => 1,
                                      'month'=> 1,
                                      'year' => 1 );
        
		$params = array( 'entity_id'               => $this->_contributionPageId,
                         'entity_table'            => 'civicrm_contribution_page',
                         'pledge_frequency_unit'   => $pledgeFrequencyUnit,
                         'max_reminders'           => 2,
                         'initial_reminder_day'    => 2,
                         'additional_reminder_day' => 1                          );
		
        $pledgeBlock = CRM_Pledge_BAO_PledgeBlock::create( $params );
        
        // use retrieve() method
        $retrieveParams = array( 'entity_id'               => $this->_contributionPageId,
                                 'entity_table'            => 'civicrm_contribution_page' );
        $default        = array( );
        $retrievePledgeBlock = CRM_Pledge_BAO_PledgeBlock::retrieve( $retrieveParams, $default);
        
        // use getPledgeBlock() method
        $getPledgeBlock = CRM_Pledge_BAO_PledgeBlock::getPledgeBlock( $this->_contributionPageId );
        
        // check on both retrieve and getPledgeBlock values
        foreach ( $params as $param => $value ) {
            $this->assertEquals( $value, $retrievePledgeBlock->$param );
            $this->assertEquals( $value, $getPledgeBlock[$param] );
        }
        
        // Also check for pledgeBlock id.
        $this->assertEquals( $pledgeBlock->id, $retrievePledgeBlock->id );
        $this->assertEquals( $pledgeBlock->id, $getPledgeBlock['id'] );
        
    }
    
    
}
