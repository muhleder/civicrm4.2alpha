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
 * This class is for building event(participation) block on user dashboard
 */
class CRM_Activity_Page_UserDashboard extends CRM_Contact_Page_View_UserDashBoard
{
    /**
     * Function to list participations for the UF user
     *
     * return null
     * @access public
     */
    function listActivities( )
    {

        $controller = new CRM_Core_Controller_Simple( 'CRM_Activity_Form_Search', ts('Activities') );
        $controller->setEmbedded( true );
        $controller->reset( );
        $controller->set( 'context', 'user' );
        $controller->set( 'cid'  , $this->_contactId );
        $controller->set( 'status'  , array(1 => 'on') );
        $controller->set( 'activity_role'  , 2 );
        $controller->set( 'activity_contact_name'  , 'd6' );
        $controller->set( 'force'  , 1 );
        $controller->process( );
        $controller->run( );
        
        return;

    }

    /**
     * This function is the main function that is called when the page
     * loads, it decides the which action has to be taken for the page.
     *
     * return null
     * @access public
     */
    function run( )
    {
        parent::preProcess( );
        $this->listActivities( );
    }

}


