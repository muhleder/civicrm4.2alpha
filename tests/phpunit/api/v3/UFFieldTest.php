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
require_once 'api/v3/UFGroup.php';
require_once 'api/v3/UFField.php';

/**
 * Test class for UFGroup API - civicrm_uf_*
 * @todo Split UFGroup and UFJoin tests
 *
 *  @package   CiviCRM
 */
class api_v3_UFFieldTest extends CiviUnitTestCase
{
    // ids from the uf_group_test.xml fixture
    protected $_ufGroupId = 11;
    protected $_ufFieldId;
    protected $_contactId = 69;
    protected $_apiversion; 
    protected $_params;
    protected $_entity;
    protected function setUp()
    {
        parent::setUp();
        $this->_apiversion = 3;
        $op = new PHPUnit_Extensions_Database_Operation_Insert;
        $op->execute(
            $this->_dbconn,
            new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(dirname(__FILE__) . '/dataset/uf_group_test.xml')
        );

        // FIXME: something NULLs $GLOBALS['_HTML_QuickForm_registered_rules'] when the tests are ran all together
        $GLOBALS['_HTML_QuickForm_registered_rules'] = array(
            'required'      => array('html_quickform_rule_required', 'HTML/QuickForm/Rule/Required.php'),
            'maxlength'     => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
            'minlength'     => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
            'rangelength'   => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
            'email'         => array('html_quickform_rule_email',    'HTML/QuickForm/Rule/Email.php'),
            'regex'         => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
            'lettersonly'   => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
            'alphanumeric'  => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
            'numeric'       => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
            'nopunctuation' => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
            'nonzero'       => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
            'callback'      => array('html_quickform_rule_callback', 'HTML/QuickForm/Rule/Callback.php'),
            'compare'       => array('html_quickform_rule_compare',  'HTML/QuickForm/Rule/Compare.php')
        );
        // FIXME: …ditto for $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'] = array(
            'group'         =>array('HTML/QuickForm/group.php','HTML_QuickForm_group'),
            'hidden'        =>array('HTML/QuickForm/hidden.php','HTML_QuickForm_hidden'),
            'reset'         =>array('HTML/QuickForm/reset.php','HTML_QuickForm_reset'),
            'checkbox'      =>array('HTML/QuickForm/checkbox.php','HTML_QuickForm_checkbox'),
            'file'          =>array('HTML/QuickForm/file.php','HTML_QuickForm_file'),
            'image'         =>array('HTML/QuickForm/image.php','HTML_QuickForm_image'),
            'password'      =>array('HTML/QuickForm/password.php','HTML_QuickForm_password'),
            'radio'         =>array('HTML/QuickForm/radio.php','HTML_QuickForm_radio'),
            'button'        =>array('HTML/QuickForm/button.php','HTML_QuickForm_button'),
            'submit'        =>array('HTML/QuickForm/submit.php','HTML_QuickForm_submit'),
            'select'        =>array('HTML/QuickForm/select.php','HTML_QuickForm_select'),
            'hiddenselect'  =>array('HTML/QuickForm/hiddenselect.php','HTML_QuickForm_hiddenselect'),
            'text'          =>array('HTML/QuickForm/text.php','HTML_QuickForm_text'),
            'textarea'      =>array('HTML/QuickForm/textarea.php','HTML_QuickForm_textarea'),
            'fckeditor'     =>array('HTML/QuickForm/fckeditor.php','HTML_QuickForm_FCKEditor'),
            'tinymce'       =>array('HTML/QuickForm/tinymce.php','HTML_QuickForm_TinyMCE'),
            'dojoeditor'    =>array('HTML/QuickForm/dojoeditor.php','HTML_QuickForm_dojoeditor'),
            'link'          =>array('HTML/QuickForm/link.php','HTML_QuickForm_link'),
            'advcheckbox'   =>array('HTML/QuickForm/advcheckbox.php','HTML_QuickForm_advcheckbox'),
            'date'          =>array('HTML/QuickForm/date.php','HTML_QuickForm_date'),
            'static'        =>array('HTML/QuickForm/static.php','HTML_QuickForm_static'),
            'header'        =>array('HTML/QuickForm/header.php', 'HTML_QuickForm_header'),
            'html'          =>array('HTML/QuickForm/html.php', 'HTML_QuickForm_html'),
            'hierselect'    =>array('HTML/QuickForm/hierselect.php', 'HTML_QuickForm_hierselect'),
            'autocomplete'  =>array('HTML/QuickForm/autocomplete.php', 'HTML_QuickForm_autocomplete'),
            'xbutton'       =>array('HTML/QuickForm/xbutton.php','HTML_QuickForm_xbutton'),
            'advmultiselect'=>array('HTML/QuickForm/advmultiselect.php','HTML_QuickForm_advmultiselect'),
        );
        
        $this->_params =  array(
            'field_name'       => 'country',
            'field_type'       => 'Contact',
            'visibility'       => 'Public Pages and Listings',
            'weight'           => 1,
            'label'            => 'Test Country',
            'is_searchable'    => 1,
            'is_active'        => 1,
            'version'					 => $this->_apiversion,
            'uf_group_id'				 =>$this->_ufGroupId, 
        );
       $this->_entity = 'uf_field';
    }


    function tearDown() {

        //  Truncate the tables
        $op = new PHPUnit_Extensions_Database_Operation_Truncate( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                             dirname(__FILE__) . '/../../CiviTest/truncate-ufgroup.xml') );
    }

    /**
     * create / updating field
     */
    public function testCreateUFField()
    {
        $params = array(
            'field_name'       => 'country',
            'field_type'       => 'Contact',
            'visibility'       => 'Public Pages and Listings',
            'weight'           => 1,
            'label'            => 'Test Country',
            'is_searchable'    => 1,
            'is_active'        => 1,
            'version'					 => $this->_apiversion,
            'uf_group_id'				 =>$this->_ufGroupId, 
        );
        $ufField          = civicrm_api('uf_field', 'create', $params);
        $this->documentMe($params,$ufField ,__FUNCTION__,__FILE__); 
        unset ($params['version']);
        unset ($params[ 'uf_group_id']);
        $this->_ufFieldId = $ufField['id'];
        $this->assertEquals(0, $ufField['is_error'], " in line " . __LINE__ );
        foreach ($params as $key => $value) {
           $this->assertEquals($ufField['values'][$ufField['id']][$key], $params[$key]);
        }


    }

    function testCreateUFFieldWithEmptyParams()
    {
        $params = array();
        $result = civicrm_api('uf_field', 'create',   $params );
        $this->assertEquals($result['is_error'], 1);
    }

    function testCreateUFFieldWithWrongParams()
    {
        $result = civicrm_api('uf_field', 'create',  array('field_name' => 'test field'));
        $this->assertEquals($result['is_error'], 1);
        $result = civicrm_api('uf_field', 'create',  'a string');
        $this->assertEquals($result['is_error'], 1);
        $result = civicrm_api('uf_field', 'create',  array('label' => 'name-less field'));
        $this->assertEquals($result['is_error'], 1);
    }

    /**
     * deleting field
     */
    public function testDeleteUFField()
    {
        $params = array(
            'field_name'       => 'country',
            'field_type'       => 'Contact',
            'visibility'       => 'Public Pages and Listings',
            'weight'           => 1,
            'location_type_id' => 1,
            'label'            => 'Test Country',
            'is_searchable'    => 1,
            'is_active'        => 1,
            'version'					 => $this->_apiversion,
            'uf_group_id'				 => $this->_ufGroupId,
        );

        $ufField          = civicrm_api('uf_field', 'create', $params);
        $this->assertEquals($ufField['is_error'], 0,'in line' . __LINE__);
        $this->_ufFieldId = $ufField['id'];
        $params = array('version'	 => $this->_apiversion,
                        'field_id'  => $ufField['id']);
        $result = civicrm_api('uf_field', 'delete', $params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);        
        $this->assertAPISuccess($result, 0,'in line' . __LINE__);
    }
    
    public function testGetUFFieldSuccess(){

        civicrm_api($this->_entity,'create', $this->_params);
        $params = array('version' => 3);
        $result = civicrm_api($this->_entity,'get',$params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);  
        $this->assertEquals($result['is_error'], 0,'in line' . __LINE__);
        $values = $result['values'][$result['id']];
        foreach($this->_params as $key => $value){
          if($key == 'version')continue;
          $this->assertEquals($value, $values[$key],'in line' . __LINE__);        
          
        }  
        civicrm_api($this->_entity,'delete',$values);
    }
}