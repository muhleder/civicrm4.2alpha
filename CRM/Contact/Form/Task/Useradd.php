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
 * This class generates form components generic to useradd
 *
 */
class CRM_Contact_Form_Task_Useradd extends CRM_Core_Form
{
    /**
     * The contact id, used when adding user
     *
     * @var int
     */
    protected $_contactId;

    /**
     * contact.display_name of contact for whom we are adding user
     *
     * @var int
     * @public
     */
    public $_displayName;

    /**
     * primary email of contact for whom we are adding user
     *
     * @var int
     * @public
     */
    public $_email;

    function preProcess( ) {
        $defaults = array( );
        
        $params   = array( );
        $defaults = array( );
        $ids      = array( );
        
        $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
        $params['id'] = $params['contact_id'] = $this->_contactId;
        $contact = CRM_Contact_BAO_Contact::retrieve( $params, $defaults, $ids );    
        $this->_displayName = $contact->display_name;
        $this->_email = $contact->email;
        CRM_Utils_System::setTitle( ts('Create User Record for %1', array( 1 => $this->_displayName ) ) );
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) {
        $defaults = array( );
        $defaults['contactID'] = $this->_contactId;
        $defaults['name'] = $this->_displayName;
        if ( ! empty( $this->_email ) ) {
            $defaults['email'] = $this->_email[1]['email'];
        }

        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {       
        $element = $this->add( 'text', 'name' , ts('Full Name') , array('class' => 'huge') );
        $element->freeze( );
        $this->add( 'text', 'cms_name' , ts('Username') ,  array('class' => 'huge') );
        $this->addRule('cms_name','Username is required','required' );
        $this->addRule('cms_name','Enter a valid username','minlength', 2);
        $this->add('password', 'cms_pass' , ts('Password') ,  array('class' => 'huge'));
        $this->add('password', 'cms_confirm_pass' , ts('Confirm Password') ,  array('class' => 'huge') );
        $this->addRule('cms_pass','Password is required','required' );
        $this->addRule(array('cms_pass','cms_confirm_pass'), 'ERROR: Password mismatch', 'compare');
        $element = $this->add('text', 'email' , ts('Email:') ,  array('class' => 'huge') );
        $element->freeze();
        $this->add('hidden', 'contactID');

        //add a rule to check username uniqueness
        $this->addFormRule( array( 'CRM_Contact_Form_Task_Useradd', 'usernameRule' ) );

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Add'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        $this->setDefaults( $this->setDefaultValues() );
    }
  
    /**
     *
     * @access public
     * @return None
     */
    public function postProcess( )
    {
        // store the submitted values in an array
        $params = $this->exportValues();

        CRM_Core_BAO_CMSUser::create($params, 'email');
        CRM_Core_Session::setStatus( ts('User has been added.'));
    
    }//end of function

    public function usernameRule( $params ) {
      $config   = CRM_Core_Config::singleton();
      $errors = array();
      $check_params = array( 'name' => $params['cms_name']);
      $config->userSystem->checkUserNameEmailExists( $check_params , $errors );

      return empty($errors) ? true : $errors;

    }
}
