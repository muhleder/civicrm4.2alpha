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

class CRM_Contact_Form_Location
{  
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     */ 
    function preProcess( &$form ) 
    {  
        $form->_addBlockName  = CRM_Utils_Request::retrieve('block', 'String',   CRM_Core_DAO::$_nullObject );
        $additionalblockCount = CRM_Utils_Request::retrieve('count', 'Positive', CRM_Core_DAO::$_nullObject );
        
        $form->assign( 'addBlock', false );
        if ( $form->_addBlockName && $additionalblockCount ) {
            $form->assign( 'addBlock', true );
            $form->assign( 'blockName', $form->_addBlockName );
            $form->assign( 'blockId',  $additionalblockCount );
            $form->set( $form->_addBlockName.'_Block_Count', $additionalblockCount );
        }
        
        $className = CRM_Utils_System::getClassName( $form );
        if ( in_array( $className, array( 'CRM_Event_Form_ManageEvent_Location', 'CRM_Contact_Form_Domain' ) ) ) {
            $form->_blocks = array( 'Address' => ts( 'Address' ), 
                                    'Email'   => ts( 'Email'   ), 
                                    'Phone'   => ts( 'Phone'   ) );
        }
        
        $form->assign( 'blocks',    $form->_blocks );
        $form->assign( 'className', $className );
        
        // get address sequence.
        if ( !$addressSequence = $form->get( 'addressSequence' ) ) {
            $addressSequence = CRM_Core_BAO_Address::addressSequence( );
            $form->set( 'addressSequence', $addressSequence );
        }
        $form->assign( 'addressSequence', $addressSequence  );
    }
    
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    static function buildQuickForm ( &$form )
    { 
        // required for subsequent AJAX requests.
        $ajaxRequestBlocks   = array( );
        $generateAjaxRequest = 0;
        
        //build 1 instance of all blocks, without using ajax ...
        foreach ( $form->_blocks as $blockName => $label ) {
            require_once(str_replace('_', DIRECTORY_SEPARATOR, 'CRM_Contact_Form_Edit_' . $blockName ) . '.php');
            $name = strtolower($blockName);
            
            $instances = array( 1 );
            if ( CRM_Utils_Array::value( $name, $_POST ) && is_array( $_POST[$name] ) ) {
                $instances = array_keys( $_POST[$name] );
            } else if ( CRM_Utils_Array::value( $name, $form->_values ) && is_array( $form->_values[$name] ) ) {
                $instances = array_keys( $form->_values[$name] );
            }
            
            foreach ( $instances as $instance ) {
                if ( $instance == 1 ) {
                    $form->assign( 'addBlock', false );
                    $form->assign( 'blockId',  $instance );
                } else {
                    //we are going to build other block instances w/ AJAX
                    $generateAjaxRequest++;
                    $ajaxRequestBlocks[$blockName][$instance] = true;
                }
                
                $form->set( $blockName.'_Block_Count', $instance );
                eval( 'CRM_Contact_Form_Edit_' . $blockName . '::buildQuickForm( $form );' );
            }
        }
        
        //assign to generate AJAX request for building extra blocks.
        $form->assign( 'generateAjaxRequest', $generateAjaxRequest );
        $form->assign( 'ajaxRequestBlocks',   $ajaxRequestBlocks   ); 
    }
}


