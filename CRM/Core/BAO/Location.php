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
 * This class handle creation of location block elements
 */
class CRM_Core_BAO_Location extends CRM_Core_DAO
{
    /**
     * Location block element array
     */
    static $blocks = array( 'phone', 'email', 'im', 'openid', 'address' );
    
    /**
     * Function to create various elements of location block
     *
     * @param array    $params       (reference ) an assoc array of name/value pairs
     * @param boolean  $fixAddress   true if you need to fix (format) address values
     *                               before inserting in db
     *
     * @return array   $location 
     * @access public
     * @static
     */
    static function create( &$params, $fixAddress = true, $entity = null ) 
    {
        $location = array( );
        if ( ! self::dataExists( $params ) ) {
            return $location;
        }
        
        // create location blocks.
        foreach ( self::$blocks as $block ) {
            if ( $block != 'address' ) {
                eval( '$location[$block] = CRM_Core_BAO_Block::create( $block, $params, $entity );');
            } else {
                $location[$block] = CRM_Core_BAO_Address::create( $params, $fixAddress, $entity );
            }
        }
        
        if ( $entity ) {
            // this is a special case for adding values in location block table
            $entityElements = array( 'entity_table' => $params['entity_table'],
                                     'entity_id'    => $params['entity_id']);
            
            $location['id'] = self::createLocBlock ( $location, $entityElements );
        } else {
            // make sure contact should have only one primary block, CRM-5051 
            self::checkPrimaryBlocks( CRM_Utils_Array::value( 'contact_id', $params ) );
        }
        
        return $location;
    }

    /**
     * Creates the entry in the civicrm_loc_block
     *
     */
    static function createLocBlock ( &$location, &$entityElements ) 
    {
        $locId = self::findExisting( $entityElements );
        $locBlock = array( );

        if ( $locId ) {
            $locBlock['id'] = $locId;
        }
        
        foreach( array( 'phone', 'email', 'im', 'address' )  as $loc ) {
            $locBlock["{$loc}_id"]   = CRM_Utils_Array::value(0, $location["$loc"]) ? $location["$loc"][0]->id : null;
            $locBlock["{$loc}_2_id"] = CRM_Utils_Array::value(1, $location["$loc"]) ? $location["$loc"][1]->id : null;
        }
        
        $countNull = 0;
        foreach( $locBlock as $key => $block) {
            if ( empty($locBlock[$key] ) ) {
                $locBlock[$key] = 'null';
                $countNull++;
            }
        }
        
        if ( count($locBlock) == $countNull ) {
            // implies nothing is set.
            return null;
        }

        $locBlockInfo = self::addLocBlock( $locBlock );
        return $locBlockInfo->id;
      
    }

    /**
     * takes an entity array and finds the existing location block 
     * @access public
     * @static
     */
    static function findExisting( $entityElements ) 
    {
        $eid = $entityElements['entity_id'];
        $etable = $entityElements['entity_table'];
        $query = "
SELECT e.loc_block_id as locId
FROM {$etable} e
WHERE e.id = %1";

        $params = array( 1 => array( $eid, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
         while ( $dao->fetch( ) ) {
             $locBlockId = $dao->locId;
         }
         return $locBlockId;
    }
    
    /**
     * takes an associative array and adds location block 
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return object       CRM_Core_BAO_locBlock object on success, null otherwise
     * @access public
     * @static
     */
    static function addLocBlock( &$params ) 
    {
        $locBlock = new CRM_Core_DAO_LocBlock();
        
        $locBlock->copyValues($params);

        return $locBlock->save( );
    }
     /**
     *  This function deletes the Location Block
     *
     * @param  int  $locBlockId    id of the Location Block
     *
     * @return void
     * @access public
     * @static
     */
    
    public static function deleteLocBlock( $locBlockId )
    {
        if ( !$locBlockId ) {
            return;
        }
        
        $locBlock     = new CRM_Core_DAO_LocBlock( );
        $locBlock->id = $locBlockId;
        
        $locBlock->find( true );
        
        //resolve conflict of having same ids for multiple blocks
        $store = array(
                       'IM_1'      => $locBlock->im_id,
                       'IM_2'      => $locBlock->im_2_id,
                       'Email_1'   => $locBlock->email_id,
                       'Email_2'   => $locBlock->email_2_id,
                       'Phone_1'   => $locBlock->phone_id,
                       'Phone_2'   => $locBlock->phone_2_id,
                       'Address_1' => $locBlock->address_id,
                       'Address_2' => $locBlock->address_2_id
                       );
        $locBlock->delete( );
        foreach ( $store as $daoName => $id ) {
            if ( $id ) {
                $daoName = substr( $daoName, 0, -2 );
                eval( '$dao = new CRM_Core_DAO_' . $daoName . '( );' );
                $dao->id = $id;
                $dao->find( true );
                $dao->delete( );
                $dao->free( );
            }
        }
        
    }
    
    /**
     * Check if there is data to create the object
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return boolean
     * @access public
     * @static
     */
    static function dataExists( &$params ) 
    {
        // return if no data present
        $dataExists = false;
        foreach ( self::$blocks as $block ) {
            if ( array_key_exists( $block, $params ) ) {
                $dataExists = true;  
                break;
            }
        }
        
        return $dataExists;
    }
    
    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params        input parameters to find object
     * @param array $values        output values of the object
     *
     * @return array   array of objects(CRM_Core_BAO_Location)
     * @access public
     * @static
     */
    static function &getValues( $entityBlock, $microformat = false ) 
    {  
        if ( empty( $entityBlock ) ) {
            return null;
        }

        $name_map = array(
            'im' => 'IM',
            'openid' => 'OpenID',
        );

        //get all the blocks for this contact
        foreach ( self::$blocks as $block ) {
            if (array_key_exists($block, $name_map)) {
                $name = $name_map[$block];
            } else {
                $name = ucfirst( $block );
            }
            eval( '$blocks[$block] = CRM_Core_BAO_' . $name . '::getValues( $entityBlock, $microformat );');
        }
        return $blocks;
    }

    /**
     * Delete all the block associated with the location
     *
     * @param  int  $contactId      contact id
     * @param  int  $locationTypeId id of the location to delete
     *
     * @return void
     * @access public
     * @static
     */
    static function deleteLocationBlocks( $contactId, $locationTypeId ) 
    {
        // ensure that contactId has a value
        if ( empty( $contactId ) ||
             ! CRM_Utils_Rule::positiveInteger( $contactId ) ) {
            CRM_Core_Error::fatal( );
        }
             
        if ( empty( $locationTypeId ) ||
             ! CRM_Utils_Rule::positiveInteger( $locationTypeId ) ) {
            // so we only delete the blocks which DO NOT have a location type Id
            // CRM-3581
            $locationTypeId = 'null';
        }

        static $blocks = array( 'Address', 'Phone', 'IM', 'OpenID', 'Email' );
        
        $params = array ( 'contact_id' => $contactId, 'location_type_id' => $locationTypeId );
        foreach ($blocks as $name) {
            CRM_Core_BAO_Block::blockDelete( $name, $params );
        }
    }
   
    /* Function to copy or update location block. 
     *
     * @param  int  $locBlockId  location block id.
     * @param  int  $updateLocBlockId update location block id
     * @return int  newly created/updated location block id.
     */
    static function copyLocBlock( $locBlockId, $updateLocBlockId = null ) 
    {
        //get the location info.
        $defaults = $updateValues = array( );
        $locBlock = array( 'id' => $locBlockId );
        CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_LocBlock', $locBlock, $defaults );
        
        if ( $updateLocBlockId ) {
            //get the location info for update.
            $copyLocationParams = array( 'id' => $updateLocBlockId );
            CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_LocBlock', $copyLocationParams, $updateValues );
            foreach ( $updateValues as $key => $value) {
                if ( $key != 'id' ) {
                    $copyLocationParams[$key] = 'null';
                }
            }
        }
        
        //copy all location blocks (email, phone, address, etc)
        foreach ( $defaults as $key => $value ) {
            if ( $key != 'id') {
                $tbl  = explode("_", $key);
                $name = ucfirst( $tbl[0] );
                $updateParams = null;
                if ( $updateId = CRM_Utils_Array::value( $key, $updateValues ) ) {
                    $updateParams = array( 'id' => $updateId );
                }
                
                $copy = CRM_Core_DAO::copyGeneric( 'CRM_Core_DAO_' . $name, array( 'id' => $value ), $updateParams );
                $copyLocationParams[$key] = $copy->id;
            }
        }
        
        $copyLocation =& CRM_Core_DAO::copyGeneric( 'CRM_Core_DAO_LocBlock', 
                                                    array( 'id' => $locBlock['id'] ), 
                                                    $copyLocationParams );
        return $copyLocation->id;
    }
    
    /**
     * If contact has data for any location block, make sure 
     * contact should have only one primary block, CRM-5051
     *
     * @param  int $contactId - contact id 
     *
     * @access public
     * @static
     */
    static function checkPrimaryBlocks( $contactId ) 
    {
        if ( !$contactId ) {
            return;
        }
        
        // get the loc block ids.
        $primaryLocBlockIds = CRM_Contact_BAO_Contact::getLocBlockIds( $contactId, array( 'is_primary' => 1 ) );
        $nonPrimaryBlockIds = CRM_Contact_BAO_Contact::getLocBlockIds( $contactId, array( 'is_primary' => 0 ) );
        
        foreach ( array( 'Email', 'IM', 'Phone', 'Address', 'OpenID' ) as $block ) {
            $name = strtolower( $block );
            if ( array_key_exists( $name, $primaryLocBlockIds ) && 
                 !CRM_Utils_System::isNull( $primaryLocBlockIds[$name] ) ) {
                if ( count( $primaryLocBlockIds[$name] ) > 1 ) {
                    // keep only single block as primary.
                    $primaryId = array_pop( $primaryLocBlockIds[$name] );
                    $resetIds  = "(" . implode( ',', $primaryLocBlockIds[$name] ) . ")";
                    // reset all primary except one.
                    CRM_Core_DAO::executeQuery( "UPDATE civicrm_$name SET is_primary = 0 WHERE id IN $resetIds" );
                }
            } else if ( array_key_exists( $name,  $nonPrimaryBlockIds ) && 
                        !CRM_Utils_System::isNull( $nonPrimaryBlockIds[$name] ) ) {
                // data exists and no primary block - make one primary.
                CRM_Core_DAO::setFieldValue( "CRM_Core_DAO_" . $block, 
                                             array_pop( $nonPrimaryBlockIds[$name] ), 'is_primary', 1 );
            }
        }
    }
    
}


