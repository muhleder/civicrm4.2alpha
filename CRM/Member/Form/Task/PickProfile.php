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
 * This class provides the functionality for batch profile update for membership
 */
class CRM_Member_Form_Task_PickProfile extends CRM_Member_Form_Task {

    /**
     * the title of the group
     *
     * @var string
     */
    protected $_title;

    /**
     * maximum members that should be allowed to update
     *
     */
    protected $_maxMembers = 100;


    /**
     * variable to store redirect path
     *
     */
    protected $_userContext;


    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        /*
         * initialize the task and row fields
         */
        parent::preProcess( );
        $session = CRM_Core_Session::singleton();
        $this->_userContext = $session->readUserContext( );

        CRM_Utils_System::setTitle( ts('Batch Profile Update for Membership') );
    
        $validate = false;
        //validations
        if ( count($this->_memberIds) > $this->_maxMembers) {
            CRM_Core_Session::setStatus("The maximum number of members you can select for Batch Update is {$this->_maxMembers}. You have selected ". count($this->_memberIds). ". Please select fewer members from your search results and try again." );
            $validate = true;
        }
        
        if ($validate) { // than redirect
            CRM_Utils_System::redirect( $this->_userContext );
        }
    }
  
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        $types    = array( 'Membership' );
        $profiles = CRM_Core_BAO_UFGroup::getProfiles( $types, true );

        if (  empty( $profiles ) ) {
            CRM_Core_Session::setStatus("You will need to create a Profile containing the {$types[0]} fields you want to edit before you can use Batch Update via Profile. Navigate to Administer Civicrm >> CiviCRM Profile to configure a Profile. Consult the online Administrator documentation for more information." );
            CRM_Utils_System::redirect( $this->_userContext );
        }

        $ufGroupElement = $this->add('select', 'uf_group_id', ts('Select Profile'), 
                                     array( '' => ts('- select profile -')) + $profiles, true);
        $this->addDefaultButtons( ts( 'Continue >>' ) );
    }

    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        $this->addFormRule( array( 'CRM_Member_Form_Task_PickProfile', 'formRule' ) );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $fields ) 
    {
        return true;
    }    

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params = $this->exportValues( );

        $this->set( 'ufGroupId', $params['uf_group_id'] );

	// also reset the batch page so it gets new values from the db
	$this->controller->resetPage( 'Batch' );
    }//end of function
}

