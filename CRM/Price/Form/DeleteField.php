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
 * This class is to build the form for Deleting Group
 */
class CRM_Price_Form_DeleteField extends CRM_Core_Form {

    /**
     * the field id
     *
     * @var int
     */
    protected $_fid;

    /**
     * The title of the group being deleted
     *
     * @var string
     */
    protected $_title;

    /**
     * set up variables to build the form
     *
     * @param null
     * @return void
     * @acess protected
     */
    function preProcess( ) 
    {
        $this->_fid    = $this->get( 'fid' );
        
        $this->_title  = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Field',
                                                      $this->_fid,
                                                      'label', 'id' );
        
        $this->assign( 'title' , $this->_title );
        
        CRM_Utils_System::setTitle( ts('Confirm Price Field Delete') );
    }
    
    /**
     * Function to actually build the form
     *
     * @param null
     * 
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Delete Price Field'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
    }
    
    /**
     * Process the form when submitted
     *
     * @param null
     * 
     * @return void
     * @access public
     */
    public function postProcess( ) 
    {
        
        if (CRM_Price_BAO_Field::deleteField( $this->_fid ) ) {
            CRM_Core_Session::setStatus( ts('The Price Field \'%1\' has been deleted.', array(1 => $this->_title ) ) );
        }
    }
}

