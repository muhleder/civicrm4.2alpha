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
 * This class generates form components for Error Handling and Debugging
 * 
 */
class CRM_Admin_Form_Setting_UpdateConfigBackend extends CRM_Admin_Form_Setting
{  
    protected $_oldBaseDir;
    protected $_oldBaseURL;
    protected $_oldSiteName;
  
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        CRM_Utils_System::setTitle(ts('Settings - Cleanup Caches and Update Paths'));

        list( $this->_oldBaseURL,
              $this->_oldBaseDir,
              $this->_oldSiteName ) = CRM_Core_BAO_ConfigSetting::getConfigSettings( );

        $this->assign( 'oldBaseURL', $this->_oldBaseURL );
        $this->assign( 'oldBaseDir', $this->_oldBaseDir );
        $this->assign( 'oldSiteName', $this->_oldSiteName );

        $this->addElement( 'submit', $this->getButtonName('next', 'cleanup'), 'Cleanup Caches', array( 'class' => 'form-submit' , 'id' => 'cleanup-cache') );
        
        $this->add( 'text', 'newBaseURL', ts( 'New Base URL' ), null, true );
        $this->add( 'text', 'newBaseDir', ts( 'New Base Directory' ), null, true );
        if ( $this->_oldSiteName ) {
            $this->add( 'text', 'newSiteName', ts( 'New Site Name' ), null, true );
        }
        $this->addFormRule( array( 'CRM_Admin_Form_Setting_UpdateConfigBackend', 'formRule' ) );

        parent::buildQuickForm();     
    }

    function setDefaultValues( ) 
    {
        if ( ! $this->_defaults ) {
            parent::setDefaultValues( );

            $config = CRM_Core_Config::singleton( );
            list( $this->_defaults['newBaseURL'],
                  $this->_defaults['newBaseDir'],
                  $this->_defaults['newSiteName'] ) = CRM_Core_BAO_ConfigSetting::getBestGuessSettings( );
        }

        return $this->_defaults;
    }

    static function formRule( $fields) {
        $tmpDir = trim( $fields['newBaseDir'] );

        $errors = array( );
        if ( ! is_writeable( $tmpDir ) ) {
            $errors['newBaseDir'] = ts( '%1 directory does not exist or cannot be written by webserver',
                                        array( 1 => $tmpDir ) );
        }
        return $errors;
    }

    function postProcess( ) {
        if ( CRM_Utils_Array::value( '_qf_UpdateConfigBackend_next_cleanup', $_POST ) ) {
            
            $config = CRM_Core_Config::singleton( );
            
            // cleanup templates_c directory
            $config->cleanup( 1 , false );
            
            // clear db caching
            $config->clearDBCache( );
            parent::rebuildMenu( );
            
            CRM_Core_Session::setStatus( ts('Cache has been cleared and menu has been rebuilt successfully.') );
            return CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/admin/setting/updateConfigBackend', 'reset=1') );
        }
     
        // redirect to admin page after saving
        $session = CRM_Core_Session::singleton();
        $session->pushUserContext( CRM_Utils_System::url( 'civicrm/admin') );

        $params = $this->controller->exportValues( $this->_name );

        //CRM-5679
        foreach ( $params as $name => &$val ) {
            if ( $val && in_array( $name, array( 'newBaseURL', 'newBaseDir', 'newSiteName' ) ) ) {
                $val = CRM_Utils_File::addTrailingSlash( $val );
            }
        }

        $from = array( $this->_oldBaseURL, $this->_oldBaseDir );
        $to   = array( trim( $params['newBaseURL'] ),
                       trim( $params['newBaseDir'] ) );
        if ( $this->_oldSiteName &&
             $params['newSiteName'] ) {
            $from[] = $this->_oldSiteName;
            $to[]   = $params['newSiteName'];
        }

        $newValues = str_replace( $from,
                                  $to,
                                  $this->_defaults );

        parent::commonProcess( $newValues );

        parent::rebuildMenu( );
    }

}


