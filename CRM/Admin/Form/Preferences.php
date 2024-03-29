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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


/**
 * This class generates form components for Location Type
 * 
 */
class CRM_Admin_Form_Preferences extends CRM_Core_Form
{
    protected $_system    = false;
    protected $_contactID = null;
    protected $_action    = null;

    protected $_checkbox  = null;

    protected $_varNames  = null;

    protected $_config    = null;

    protected $_params    = null;

    function preProcess( ) {
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive',
                                                         $this, false );
        $this->_system    = CRM_Utils_Request::retrieve( 'system', 'Boolean',
                                                         $this, false, true );
        $this->_action    = CRM_Utils_Request::retrieve( 'action', 'String',
                                                         $this, false, 'update' );
        if ( isset($action) ) {
            $this->assign( 'action', $action );
        }

        $session = CRM_Core_Session::singleton( );

        $this->_config = new CRM_Core_DAO( );

        if ( $this->_system ) {
            if ( CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
                $this->_contactID = null;
            } else {
                CRM_Utils_System::fatal( 'You do not have permission to edit preferences' );
            }
            $this->_config->contact_id = null;
        } else {
            if ( ! $this->_contactID ) {
                $this->_contactID = $session->get( 'userID' );
                if ( ! $this->_contactID ) {
                    CRM_Utils_System::fatal( 'Could not retrieve contact id' );
                }
                $this->set( 'cid', $this->_contactID );
            }
            $this->_config->contact_id = $this->_contactID;
        }

        foreach ( $this->_varNames as $groupName => $settingNames ) {
            $values = CRM_Core_BAO_Setting::getItem( $groupName );
            foreach ( $values as $name => $value ) {
                $this->_config->$name = $value;
            }
        }
        $session->pushUserContext( CRM_Utils_System::url('civicrm/admin', 'reset=1') );
    }

    function setDefaultValues( ) {
        $defaults = array( );

        foreach ( $this->_varNames as $groupName => $settings ) {
            foreach ( $settings as $settingName => $settingDetails ) {
                $defaults[$settingName] = 
                    isset( $this->_config->$settingName ) ?
                    $this->_config->$settingName :
                    CRM_Utils_Array::value ( 'default', $settingDetails, null );
            }
        }

        return $defaults;
    }

    function cbsDefaultValues( &$defaults ) {

        foreach ( $this->_varNames as $groupName => $groupValues ) {
            foreach ( $groupValues as $settingName => $fieldValue ) {
                if ( $fieldValue['html_type'] == 'checkboxes' ) {
                    if ( isset( $this->_config->$settingName ) &&
                         $this->_config->$settingName ) {
                        $value = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                          substr( $this->_config->$settingName, 1, -1 ) );
                        if ( ! empty( $value ) ) {
                            $defaults[$settingName] = array( );
                            foreach ( $value as $n => $v ) {
                                $defaults[$settingName][$v] = 1;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        parent::buildQuickForm( );

        
        if ( ! empty( $this->_varNames ) ) {
            foreach ( $this->_varNames as $groupName => $groupValues ) {
                $formName = CRM_Utils_String::titleToVar( $groupName );
                $this->assign('formName', $formName);
                $fields = array();
                foreach ( $groupValues as $fieldName => $fieldValue ) {
                    $fields[$fieldName] = $fieldValue;

                    switch ( $fieldValue['html_type'] ) {
                    case 'text':
                        $this->addElement( 'text',
                                           $fieldName,
                                           $fieldValue['title'],
                                           array( 'maxlength' => 64,
                                                  'size'      => 32 ) );
                        break;

                    case 'textarea':
                        $this->addElement( 'textarea',
                                           $fieldName,
                                           $fieldValue['title'] );
                        break;

                    case 'checkbox':
                        $this->addElement( 'checkbox',
                                           $fieldName,
                                           $fieldValue['title'] );
                        break;

                    case 'checkboxes':
                        $options = array_flip( CRM_Core_OptionGroup::values( $fieldName, false, false, true ) );
                        $newOptions = array( );
                        foreach ( $options as $key => $val ) {
                            $newOptions[ $key ] = $val;
                        }
                        $this->addCheckBox( $fieldName,
                                            $fieldValue['title'],
                                            $newOptions,
                                            null, null, null, null,
                                            array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>' ) );
                        break;
                    }
                }
                
                $fields = CRM_Utils_Array::crmArraySortByField($fields, 'weight');
                $this->assign( 'fields', $fields);
            }
        }

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

        if ($this->_action == CRM_Core_Action::VIEW ) {
            $this->freeze( );
        }
       
    }


    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $config = CRM_Core_Config::singleton();
        if ( $this->_action == CRM_Core_Action::VIEW ) {
            return;
        }

        $this->_params = $this->controller->exportValues( $this->_name );
        
        $this->postProcessCommon( );
    }//end of function


    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcessCommon() 
    {
        foreach ( $this->_varNames as $groupName => $groupValues ) {
            foreach ( $groupValues as $settingName => $fieldValue ) {
                switch ( $fieldValue['html_type'] ) {
                case 'checkboxes':
                    if ( CRM_Utils_Array::value( $settingName, $this->_params ) &&
                         is_array( $this->_params[$settingName] ) ) {
                        $this->_config->$settingName = 
                            CRM_Core_DAO::VALUE_SEPARATOR .
                            implode( CRM_Core_DAO::VALUE_SEPARATOR,
                                     array_keys( $this->_params[$settingName] ) ) .
                            CRM_Core_DAO::VALUE_SEPARATOR;
                    } else {
                        $this->_config->$settingName = null;
                    }
                    break;

                case 'checkbox':
                    $this->_config->$settingName = CRM_Utils_Array::value( $settingName, $this->_params ) ? 1 : 0;
                    break;

                case 'text':
                case 'select':
                    $this->_config->$settingName = CRM_Utils_Array::value( $settingName, $this->_params );
                    break;

                case 'textarea':
                    $value = CRM_Utils_Array::value( $settingName, $this->_params );
                    if ( $value ) {
                        $value = trim( $value );
                        $value = str_replace(array("\r\n", "\r"), "\n", $value );
                    }
                    $this->_config->$settingName = $value;
                    break;
                }
            }
        }

        foreach ( $this->_varNames as $groupName => $groupValues ) {
            foreach ( $groupValues as $settingName => $fieldValue ) {
                $settingValue = isset( $this->_config->$settingName ) ? $this->_config->$settingName : null;
                CRM_Core_BAO_Setting::setItem( $settingValue,
                                               $groupName,
                                               $settingName );
            }
        }
    }//end of function

}