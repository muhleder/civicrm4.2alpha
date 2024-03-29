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
 * CiviCRM Dashboard
 *
 */
class CRM_Contact_Page_DashBoard extends CRM_Core_Page
{
        
    /**
     * Run dashboard
     *
     * @return none
     * @access public
     */
    function run( )
    {
        $resetCache = CRM_Utils_Request::retrieve( 'resetCache', 'Positive', CRM_Core_DAO::$_nullObject );
        
        if ( $resetCache ) {
            CRM_Core_BAO_Dashboard::resetDashletCache( );
        }
        
        CRM_Utils_System::setTitle( ts('CiviCRM Home') );
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get('userID');                
        
        // call hook to get html from other modules
        $contentPlacement = CRM_Utils_Hook::DASHBOARD_BELOW;  // ignored but needed to prevent warnings
        $html = CRM_Utils_Hook::dashboard( $contactID, $contentPlacement );
        if ( is_array( $html ) ) {
            $this->assign_by_ref( 'hookContent', $html );
            $this->assign( 'hookContentPlacement', $contentPlacement );
        }
        
        //check that default FROM email address, owner (domain) organization name and default mailbox are configured.
        $fromEmailOK = true;
        $ownerOrgOK = true;
        $defaultMailboxOK = true;
        
        // Don't put up notices if user doesn't have administer CiviCRM permission
        if ( CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
            $destination = CRM_Utils_System::url( 'civicrm/dashboard',
                                                  'reset=1',
                                                  false, null, false );

            $destination = urlencode( $destination );
 
            list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( true );

            if ( !$domainEmailAddress || $domainEmailAddress == 'info@FIXME.ORG') {
                $fixEmailUrl = CRM_Utils_System::url("civicrm/admin/domain", "action=update&reset=1&civicrmDestination={$destination}");
                $this->assign( 'fixEmailUrl', $fixEmailUrl );
                $fromEmailOK = false;
            }

            $domain = CRM_Core_BAO_Domain::getDomain();
            $domainName = $domain->name;
            if ( !$domainName || $domainName == 'Default Domain Name' ) {
                $fixOrgUrl = CRM_Utils_System::url("civicrm/admin/domain", "action=update&reset=1&civicrmDestination={$destination}");
                $this->assign( 'fixOrgUrl', $fixOrgUrl );
                $ownerOrgOK = false;            
            }

            $config = CRM_Core_Config::singleton( );
            if ( in_array( 'CiviMail', $config->enableComponents ) &&
                 CRM_Core_BAO_MailSettings::defaultDomain() == "FIXME.ORG" ) {
                $fixDefaultMailbox = CRM_Utils_System::url('civicrm/admin/mailSettings', "reset=1&civicrmDestination={$destination}");
                $this->assign( 'fixDefaultMailbox', $fixDefaultMailbox );
                $defaultMailboxOK = false;
            }
            
        }

        $this->assign( 'fromEmailOK', $fromEmailOK );
        $this->assign( 'ownerOrgOK', $ownerOrgOK );
        $this->assign( 'defaultMailboxOK', $defaultMailboxOK );
        
        return parent::run( );
    }
}
