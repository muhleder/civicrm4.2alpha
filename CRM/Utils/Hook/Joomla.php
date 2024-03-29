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
 * @package CiviCRM_Hook
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id: $
 *
 */


class CRM_Utils_Hook_Joomla extends CRM_Utils_Hook {

    function invoke( $numParams,
                     &$arg1, &$arg2, &$arg3, &$arg4, &$arg5,
                     $fnSuffix ) {
       // ensure that we are running in a joomla context
       // we've not yet figured out how to bootstrap joomla, so we should
       // not execute hooks if joomla is not loaded
       if ( defined( '_JEXEC' ) ) {
           //Invoke the Joomla plugin system to observe to civicrm events.
           JPluginHelper::importPlugin('civicrm');
           
           $app = JFactory::getApplication();
           // for cli usage
           if ( get_class($app) == 'JException' ) {
               $app = JCli::getInstance( );
           }

           $result = $app->triggerEvent($fnSuffix,array(&$arg1, &$arg2, &$arg3, &$arg4, &$arg5));
           if ( ! empty( $result ) ) {
               // collapse result returned from hooks
               // CRM-9XXX
               $finalResult = array( );
               foreach ( $result as $res ) {
                   if ( ! is_array( $res ) ) {
                       $res = array( $res );
                   }
                   $finalResult = array_merge( $finalResult, $res );
               }
               $result = $finalResult;
           }
           return $result;
       }
   }
}
