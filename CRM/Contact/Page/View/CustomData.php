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
 * Page for displaying custom data
 *
 */
class CRM_Contact_Page_View_CustomData extends CRM_Core_Page {
    /**
     * the id of the object being viewed (note/relationship etc)
     *
     * @int
     * @access protected
     */
    public $_groupId;

    /**
     * class constructor
     *
     * @return CRM_Contact_Page_View_CustomData
     */
    public function __construct( )
    {
        parent::__construct();
    }


    /**
     * add a few specific things to view contact
     *
     * @return void 
     * @access public 
     * 
     */ 
    function preProcess( ) 
    { 
        $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
        $this->assign( 'contactId', $this->_contactId );

        // check logged in url permission
        CRM_Contact_Page_View::checkUserPermission( $this );
        
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, false, 'browse');
        $this->assign( 'action', $this->_action);

        $this->_groupId = CRM_Utils_Request::retrieve( 'gid', 'Positive', $this, true ); 
        $this->assign( 'groupId', $this->_groupId );
    }

    /**
     * Run the page.
     *
     * This method is called after the page is created. It checks for the  
     * type of action and executes that action. 
     *
     * @access public
     * @param object $page - the view page which created this one 
     * @return none
     * @static
     *
     */
    function run( )
    {
        $this->preProcess( );

        //set the userContext stack
        $doneURL = 'civicrm/contact/view';
        $session = CRM_Core_Session::singleton();
        $session->pushUserContext( CRM_Utils_System::url( $doneURL, 'action=browse&selectedChild=custom_' . $this->_groupId ), false );
        
        // get permission detail view or edit
        $permUser = CRM_Core_Permission::getPermission();
        
        $editCustomData = ( CRM_Core_Permission::VIEW == $permUser ) ? 0 : 1;
        $this->assign('editCustomData', $editCustomData);
        
        //allow to edit own customdata CRM-5518
        $editOwnCustomData = false;
        if ( $session->get( 'userID' ) == $this->_contactId ) {
            $editOwnCustomData = true;
        }
        $this->assign( 'editOwnCustomData', $editOwnCustomData );
        
        if ( $this->_action == CRM_Core_Action::BROWSE ) {
            //Custom Groups Inline
            $entityType = CRM_Contact_BAO_Contact::getContactType( $this->_contactId );
            $entitySubType = CRM_Contact_BAO_Contact::getContactSubType( $this->_contactId );
            $groupTree =& CRM_Core_BAO_CustomGroup::getTree( $entityType, $this, $this->_contactId, 
                                                             $this->_groupId, $entitySubType );
            CRM_Core_BAO_CustomGroup::buildCustomDataView( $this, $groupTree );
        } else {
            
            $controller = new CRM_Core_Controller_Simple('CRM_Contact_Form_CustomData',
                                                          ts('Custom Data'),
                                                          $this->_action );
            $controller->setEmbedded(true);
           
            $controller->set('tableId'   , $this->_contactId );
            $controller->set('groupId'   , $this->_groupId);
            $controller->set('entityType', CRM_Contact_BAO_Contact::getContactType( $this->_contactId ) );
            $controller->set('entitySubType', CRM_Contact_BAO_Contact::getContactSubType( $this->_contactId, ',' ) );
            $controller->process();
            $controller->run();
        }
        return parent::run();
    }
}

