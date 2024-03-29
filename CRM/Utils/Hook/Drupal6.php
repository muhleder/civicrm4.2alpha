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


class CRM_Utils_Hook_Drupal6 extends CRM_Utils_Hook {

    function invoke( $numParams,
                     &$arg1, &$arg2, &$arg3, &$arg4, &$arg5,
                     $fnSuffix ) {
        $result = array( );

        // copied from user_module_invoke
        if (function_exists('module_list')) {
            foreach ( module_list() as $module) { 
                $fnName = "{$module}_{$fnSuffix}";
                if ( function_exists( $fnName ) ) {
                    if ( $numParams == 1 ) {
                        $fResult = $fnName( $arg1 );
                    } else if ( $numParams == 2 ) {
                        $fResult = $fnName( $arg1, $arg2 );
                    } else if ( $numParams == 3 ) {
                        $fResult = $fnName( $arg1, $arg2, $arg3 );
                    } else if ( $numParams == 4 ) {
                        $fResult = $fnName( $arg1, $arg2, $arg3, $arg4 );
                    } else if ( $numParams == 5 ) {
                        $fResult = $fnName( $arg1, $arg2, $arg3, $arg4, $arg5 );
                    }
                    if ( is_array( $fResult ) ) {
                        $result = array_merge( $result, $fResult );
                    }
                }
            }
        }
        return empty( $result ) ? true : $result;
   }

}