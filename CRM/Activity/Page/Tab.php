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
 * Main page for viewing activities
 *
 */
class CRM_Activity_Page_Tab extends CRM_Core_Page 
{
    /**
     * Browse all activities for a particular contact
     *
     * @return none
     *
     * @access public
     */
    function browse( )
    {
        $this->assign( 'admin', false );
        $this->assign( 'context'   , 'activity');
        
        // also create the form element for the activity filter box
        $controller = new CRM_Core_Controller_Simple( 'CRM_Activity_Form_ActivityFilter',
                                                       ts('Activity Filter'), null );
        $controller->setEmbedded( true );
        $controller->run( );        
    }

    function edit( )
    {
        // used for ajax tabs
        $context = CRM_Utils_Request::retrieve( 'context', 'String', $this );
        $this->assign('context', $context );

        $this->_id = CRM_Utils_Request::retrieve('id', 'Integer', $this);
          
        $this->_caseId = CRM_Utils_Request::retrieve( 'caseid', 'Integer', $this );
      
        $activityTypeId = CRM_Utils_Request::retrieve('atype', 'Positive', $this );

        // Email and Create Letter activities use a different form class
        $emailTypeValue = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                          'Email',
                                                          'name' );
                                                          
        $letterTypeValue = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                          'Print PDF Letter',
                                                          'name' );


        switch ( $activityTypeId ) {
        case $emailTypeValue:
            $wrapper = new CRM_Utils_Wrapper( );
            $arguments = array( 'attachUpload' => 1 );
            return $wrapper->run( 'CRM_Contact_Form_Task_Email', ts('Email a Contact'),  $arguments );
            break;

        case $letterTypeValue:
            $wrapper = new CRM_Utils_Wrapper( );
            $arguments = array( 'attachUpload' => 1 );
            return $wrapper->run( 'CRM_Contact_Form_Task_PDF', ts('Create PDF Letter'),  $arguments );
            break;

        default:
            $controller = new CRM_Core_Controller_Simple( 'CRM_Activity_Form_Activity',
                                                          ts('Contact Activities'),
                                                          $this->_action,
                                                          false, false, false, true );
        }
       
        $controller->setEmbedded( true );

        $controller->set( 'contactId', $this->_contactId );
        $controller->set( 'atype'    , $activityTypeId );
        $controller->set( 'id'       , $this->_id );
        $controller->set( 'pid'      , $this->get( 'pid' ) );
        $controller->set( 'action'   , $this->_action );
        $controller->set( 'context'  , $context );

        $controller->process( );
        $controller->run( );
    }


    /**
     * Heart of the viewing process. The runner gets all the meta data for
     * the contact and calls the appropriate type of page to view.
     *
     * @return void
     * @access public
     *
     */
    function preProcess()
    {
        $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
        $this->assign( 'contactId', $this->_contactId );
        //FIX ME: need to fix this conflict
        $this->assign( 'contactID', $this->_contactId );

        // check logged in url permission
        CRM_Contact_Page_View::checkUserPermission( $this );
        
        // set page title
        CRM_Contact_Page_View::setTitle( $this->_contactId );
        
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, false, 'browse');
        $this->assign( 'action', $this->_action);

        // also create the form element for the activity links box
        $controller = new CRM_Core_Controller_Simple( 'CRM_Activity_Form_ActivityLinks',
                                                       ts('Activity Links'), null );
        $controller->setEmbedded( true );
        $controller->run( );
    }

    function delete( )
    {
        $controller = new CRM_Core_Controller_Simple('CRM_Activity_Form_Activity',
                                                      ts('Activity Record'),
                                                      $this->_action );
        $controller->set('id', $this->_id);
        $controller->setEmbedded( true );
        $controller->process( );
        $controller->run( );
    }

    /**
     * perform actions and display for activities.
     *
     * @return none
     *
     * @access public
     */
    function run( )
    {
        $context    = CRM_Utils_Request::retrieve('context', 'String', $this );
        $contactId  = CRM_Utils_Request::retrieve('cid', 'Positive', $this );
        $action     = CRM_Utils_Request::retrieve('action', 'String', $this );
        $this->_id  = CRM_Utils_Request::retrieve('id', 'Positive', $this );
        
        //do check for view/edit operation.
        if ( $this->_id &&
             in_array( $action, array( CRM_Core_Action::UPDATE, CRM_Core_Action::VIEW ) ) ) {
            if ( !CRM_Activity_BAO_Activity::checkPermission( $this->_id, $action ) ) {
                CRM_Core_Error::fatal( ts( 'You are not authorized to access this page.' ) );
            }
        }
        
        if ( $context == 'standalone' || ( ! $contactId && ( $action != CRM_Core_Action::DELETE ) && !$this->_id ) ) {
            $this->_action = CRM_Core_Action::ADD;
            $this->assign('action', $this->_action );
        } else {
            // we should call contact view, preprocess only for activity in contact summary
            $this->preProcess( );
        } 
        
        // route behaviour of contact/view/activity based on action defined
        if ( $this->_action & 
           ( CRM_Core_Action::UPDATE | CRM_Core_Action::ADD | CRM_Core_Action::VIEW ) ) {
            $this->edit( );
            $activityTypeId = CRM_Utils_Request::retrieve('atype', 'Positive', $this );
            
            // Email and Create Letter activities use a different form class
            $emailTypeValue = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                              'Email',
                                                              'name' );

            $letterTypeValue = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                               'Print PDF Letter',
                                                               'name' );
            
            if ( in_array( $activityTypeId, array( $emailTypeValue, $letterTypeValue ) ) ) {
                return;
            }
         } elseif ( $this->_action & ( CRM_Core_Action::DELETE | CRM_Core_Action::DETACH ) ) {
            $this->delete( );
        } else {
            $this->browse( );
        }

        return parent::run( );
    }
}

