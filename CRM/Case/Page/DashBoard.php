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
 * This page is for Case Dashboard
 */
class CRM_Case_Page_DashBoard extends CRM_Core_Page 
{
    /** 
     * Heart of the viewing process. The runner gets all the meta data for 
     * the contact and calls the appropriate type of page to view. 
     * 
     * @return void 
     * @access public 
     * 
     */ 
    function preProcess( ) 
    {
        //check for civicase access.
        if ( !CRM_Case_BAO_Case::accessCiviCase( ) ) {
            CRM_Core_Error::fatal( ts( 'You are not authorized to access this page.' ) );
        }
        
        //validate case configuration.
        $configured = CRM_Case_BAO_Case::isCaseConfigured( );
        $this->assign( 'notConfigured',       !$configured['configured'] );
        $this->assign( 'allowToAddNewCase',   $configured['allowToAddNewCase'] );
        if ( !$configured['configured'] ) {
            return;
        }
        
        $session = CRM_Core_Session::singleton();
        $allCases = CRM_Utils_Request::retrieve( 'all', 'Positive', $session );
        
        CRM_Utils_System::setTitle( ts('CiviCase Dashboard') );
        
        $userID  = $session->get('userID');
        
        //validate access for all cases.
        if ( $allCases && !CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            $allCases = false;
            CRM_Core_Session::setStatus( ts( 'You are not authorized to access all cases and activities.' ) );
        }
        if ( ! $allCases ) {
            $this->assign('myCases', true );
        } else {
            $this->assign('myCases', false );
        }
        
        $this->assign('newClient', false );
        if ( CRM_Core_Permission::check( 'add contacts' ) && 
             CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            $this->assign('newClient', true );
        }
        $summary  = CRM_Case_BAO_Case::getCasesSummary( $allCases, $userID );
        $upcoming = CRM_Case_BAO_Case::getCases( $allCases, $userID, 'upcoming');
        $recent   = CRM_Case_BAO_Case::getCases( $allCases, $userID, 'recent');
        
        $this->assign('casesSummary',  $summary);
        if( !empty( $upcoming ) ) {
            $this->assign('upcomingCases', $upcoming);
        }
        if( !empty( $recent ) ) {
            $this->assign('recentCases',   $recent);
        }
    }
    
    /** 
     * This function is the main function that is called when the page loads, 
     * it decides the which action has to be taken for the page. 
     *                                                          
     * return null        
     * @access public 
     */                                                          
    function run( ) 
    {
        $this->preProcess( );
        
        return parent::run( );
    }
}