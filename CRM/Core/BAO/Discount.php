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


class CRM_Core_BAO_Discount extends CRM_Core_DAO_Discount 
{

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }


    /**
     * Function to delete the discount
     *
     * @param int $id   discount id
     *
     * @return boolean
     * @access public
     * @static
     *
     */
    static function del ( $id ) 
    {
        // delete all discount records with the selected discounted id
        $discount = new CRM_Core_DAO_Discount( );
        $discount->id = $id;
        if ( $discount->delete( ) ) {
            return true;
        }
        return false;
    }

    /**
     *
     * The function extracts all the params it needs to create a
     * discount object. the params array contains additional unused name/value
     * pairs
     * 
     * @param array  $params         (reference) an assoc array of name/value pairs
     * 
     * @return object    CRM_Core_DAO_Discount object on success, otherwise null
     * @access public
     * @static
     */
    static function add( &$params ) 
    {
        $discount = new CRM_Core_DAO_Discount( );
        $discount->copyValues( $params );
        $discount->save( );
        return $discount;
    }
    
    /**
     * Determine whether the given table/id 
     * has discount associated with it
     *
     * @param  integer  $entityId      entity id to be searched 
     * @param  string   $entityTable   entity table to be searched 
     * @return array    $optionGroupIDs option group Ids associated with discount
     *
     */
    static function getOptionGroup( $entityId, $entityTable ) 
    {
        $optionGroupIDs = array();
        $dao = new CRM_Core_DAO_Discount( );
        $dao->entity_id    = $entityId;
        $dao->entity_table = $entityTable;
        $dao->find( );
        while ( $dao->fetch( ) ) {
            $optionGroupIDs[$dao->id] = $dao->option_group_id;
        }
        return $optionGroupIDs;
    }

    /**
     * Determine in which discount set the registration date falls
     *
     * @param  integer  $entityId      entity id to be searched 
     * @param  string   $entityTable   entity table to be searched 
     *
     * @return integer  $dao->id       discount id of the set which matches
     *                                 the date criteria
     */
    static function findSet( $entityID, $entityTable ) 
    {
        if ( empty( $entityID ) ||
             empty( $entityTable ) ) {
            // adding this here, to trap errors if values are not sent
            CRM_Core_Error::fatal( );
            return null;
        }
        
        $dao = new CRM_Core_DAO_Discount( );
        $dao->entity_id    = $entityID;
        $dao->entity_table = $entityTable;
        $dao->find( );

        while ( $dao->fetch( ) ) {
            $endDate = $dao->end_date;
            // if end date is not we consider current date as end date
            if ( !$endDate ) {
                $endDate = date( 'Ymd' );
            }
            $falls = CRM_Utils_Date::getRange( $dao->start_date, $endDate );
            if ( $falls == true ) {
                return $dao->id;
            }
        }
        return false;
    }
}


