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
 * This class provides the functionality to sms a group of
 * contacts. 
 */
class CRM_Contact_Form_Task_SMS extends CRM_Contact_Form_Task {

    /**
     * Are we operating in "single mode", i.e. sending sms to one
     * specific contact?
     *
     * @var boolean
     */
    public $_single = false;

    /**
     * all the existing templates in the system
     *
     * @var array
     */
    public $_templates = null;
    
    function preProcess( ) {

        $this->_context = CRM_Utils_Request::retrieve( 'context', 'String', $this );

        $cid = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, false );
        if ( $cid ) {
            CRM_Contact_Page_View::setTitle( $cid );
        }
        
        CRM_Contact_Form_Task_SMSCommon::preProcessProvider( $this );
        
        if ( !$cid && $this->_context != 'standalone' ) {
            parent::preProcess( );
        }
        
        $this->assign( 'single', $this->_single );
        if ( CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
            $this->assign( 'isAdmin', 1 );
        }
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    public function buildQuickForm()
    {
        //enable form element
        $this->assign( 'suppressForm', false );
        $this->assign( 'SMSTask', true );
        CRM_Contact_Form_Task_SMSCommon::buildQuickForm( $this );
        
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        CRM_Contact_Form_Task_SMSCommon::postProcess( $this );
    }
}