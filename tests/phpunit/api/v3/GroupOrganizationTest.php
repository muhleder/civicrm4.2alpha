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
require_once 'api/v3/GroupOrganization.php';

/**
 * Test class for GroupOrganization API - civicrm_group_organization_*
 *
 *  @package   CiviCRM
 */
class api_v3_GroupOrganizationTest extends CiviUnitTestCase
{
    protected $_apiversion;
    function get_info( )
    {
        return array(
                     'name'        => 'Group Organization',
                     'description' => 'Test all Group Organization API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->_apiversion =3;
        parent::setUp();
        $this->_groupID = $this->groupCreate(null);

        $this->_orgID   = $this->organizationCreate(null );

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
        //  Truncate the tables
        $op = new PHPUnit_Extensions_Database_Operation_Truncate( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                             dirname(__FILE__) . '/../../CiviTest/truncate-ufgroup.xml') );
    }

    ///////////////// civicrm_group_organization_get methods

    /**
     * Test civicrm_group_organization_get with valid params.
     */
    public function testGroupOrganizationGet()
    {

        $params = array( 'organization_id' => $this->_orgID,
                         'group_id'        => $this->_groupID,
                         'version'			 => $this->_apiversion,
                         );
        $result = civicrm_api('group_organization', 'create', $params );
        $paramsGet = array( 'organization_id' => $result['id'] ,
                            'version'			 => $this->_apiversion, );       
        $result    = civicrm_api('group_organization', 'get',$paramsGet);
        $this->documentMe($paramsGet,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0);
    }

     /**
     * Test civicrm_group_organization_get with group_id.
     */
    public function testGroupOrganizationGetWithGroupId()
    {

        $params = array( 'organization_id' => $this->_orgID,
                         'group_id'        => $this->_groupID,
                         'version'			 => $this->_apiversion,
                         );
        $result =& civicrm_api('group_organization', 'create', $params );

        $paramsGet = array( 'organization_id' => $result['result']['organization_id']  );
        
        $result    = civicrm_api('group_organization', 'get',$params);
        $this->assertEquals( $result['is_error'], 0);
    } 
  
    /**
     * Test civicrm_group_organization_get with empty params.
     */
    public function testGroupOrganizationGetWithEmptyParams()
    {
        $params = array( 'version'   => $this->_apiversion,  );
        $result =& civicrm_api('group_organization', 'get',$params);

        $this->assertEquals( $result['is_error'], 0 );
   }

    /**
     * Test civicrm_group_organization_get with wrong params.
     */
    public function testGroupOrganizationGetWithWrongParams()
    {
        $params = 'groupOrg';
        $result =& civicrm_api('group_organization', 'get',$params);

        $this->assertEquals( $result['is_error'], 1);
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }

    /**
     * Test civicrm_group_organization_get invalid keys.
     */
    public function testGroupOrganizationGetWithInvalidKeys()
    {
        $params = array( 'invalid_key' => 1,
         									'version'   => $this->_apiversion,  );
        $result =& civicrm_api('group_organization', 'get',$params);

        $this->assertEquals( $result['is_error'], 0);
   }

    ///////////////// civicrm_group_organization_create methods

    /**
     * check with valid params
     */
    public function testGroupOrganizationCreate()
    {
        $params = array( 'organization_id' => $this->_orgID,
                         'group_id'        => $this->_groupID,
                         'version'         => $this->_apiversion,
                         );
        $result =& civicrm_api('group_organization', 'create',$params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0);
    }    

    /**
     * check with empty params array
     */
    public function testGroupOrganizationCreateWithEmptyParams()
    {
        $params = array( 'version'   => $this->_apiversion,  );
        $result =& civicrm_api('group_organization', 'create',$params);

        $this->assertEquals( $result['is_error'], 1);
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: group_id, organization_id' );
    }

    /**
     * check with invalid params
     */
    public function testGroupOrganizationCreateParamsNotArray()
    {
        $params = 'group_org';
        $result =& civicrm_api('group_organization', 'create', $params );

        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }
    
    /**
     * check with invalid params keys
     */
    public function testGroupOrganizationCreateWithInvalidKeys()
    {
        $params = array( 'invalid_key' => 1,
                         'version'   => $this->_apiversion,  );
        $result =& civicrm_api('group_organization', 'create', $params );

        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: group_id, organization_id' );
    }

    ///////////////// civicrm_group_organization_remove methods

    /**
     *  Test civicrm_group_organization_remove with params not an array.
     */
    public function testGroupOrganizationDeleteParamsNotArray()
    {
        $params = 'delete';
        $result =& civicrm_api('group_organization', 'delete',$params);
        
        $this->assertEquals( $result['is_error'], 1);
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );

    }


    /**
     * Test civicrm_group_organization_remove with empty params.
     */
    public function testGroupOrganizationDeleteWithEmptyParams()
    {
        $params = array( 'version'   => $this->_apiversion, );
        $result =& civicrm_api('group_organization', 'delete',$params);

        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: id' );
    }

    /**
     *  Test civicrm_group_organization_remove with valid params.
     */
    public function testGroupOrganizationDelete()
    {   
        $paramsC = array( 'organization_id' => $this->_orgID,
                         'group_id'        => $this->_groupID,
                         'version'			 => $this->_apiversion,
                         );
        $result =& civicrm_api('group_organization', 'create', $paramsC );

        $params = array( 'id' => $result['id'],
                                'version'			 => $this->_apiversion,  );
        $result =& civicrm_api('group_organization', 'delete', $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0, 'in line '  . __LINE__);

    }

    /**
     *  Test civicrm_group_organization_remove with invalid params key.
     */
    public function testGroupOrganizationDeleteWithInvalidKey()
    {   
        $paramsDelete = array( 'invalid_key' => 1,
                                'version'   => $this->_apiversion, );
        $result =& civicrm_api('group_organization', 'delete', $paramsDelete );
 
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: id' );

    }

}
?>
