
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


class CRM_SMS_Controller_Send extends CRM_Core_Controller {

    /**
     * class constructor
     */
    function __construct( $title = null, $action = CRM_Core_Action::NONE, $modal = true ) {
        parent::__construct( $title, $modal, null, false, true );

        $mailingID = CRM_Utils_Request::retrieve('mid', 'String', $this, false, null );

        // also get the text and html file
        $txtFile  = CRM_Utils_Request::retrieve( 'txtFile', 'String',
                                                 CRM_Core_DAO::$_nullObject, false, null );
        $htmlFile = CRM_Utils_Request::retrieve( 'htmlFile', 'String',
                                                 CRM_Core_DAO::$_nullObject, false, null );

        $config = CRM_Core_Config::singleton( );
        if ( $txtFile &&
             file_exists( $config->uploadDir . $txtFile ) ) {
            $this->set( 'textFilePath', $config->uploadDir . $txtFile );
        }

        if ( $htmlFile &&
             file_exists( $config->uploadDir . $htmlFile ) ) {
            $this->set( 'htmlFilePath', $config->uploadDir . $htmlFile );
        }

        $this->_stateMachine = new CRM_SMS_StateMachine_Send( $this, $action, $mailingID);

        // create and instantiate the pages
        $this->addPages( $this->_stateMachine, $action );

        // add all the actions
        $uploadNames =
            array_merge( array( 'textFile', 'htmlFile' ),
                         CRM_Core_BAO_File::uploadNames( ) );

        $config = CRM_Core_Config::singleton( );
        $this->addActions( $config->uploadDir,
                           $uploadNames );
    }

}