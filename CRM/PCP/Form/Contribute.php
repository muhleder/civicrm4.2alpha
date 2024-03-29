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
 * This class generates form components for Tell A Friend
 * 
 */
class CRM_PCP_Form_Contribute extends CRM_Contribute_Form_ContributionPage
{

    /**
     * the type of pcp component.
     *
     * @var int
     * @protected
     */
    public $_component = 'contribute';


    public function preProcess()  
    {  
        parent::preProcess();  
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    public function setDefaultValues( ) 
    {
     //   $title = CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_ContributionPage', $this->_id, 'title' );
    //    CRM_Utils_System::setTitle(ts('Personal Campaign Page Settings (%1)', array(1 => $title)));
        
        $defaults = array();
        
        if ( isset($this->_id ) ) {
            $params = array( 'entity_id' => $this->_id, 'entity_table' => 'civicrm_contribution_page' );
            CRM_Core_DAO::commonRetrieve( 'CRM_PCP_DAO_PCPBlock', $params, $defaults );
            $defaults['pcp_active'] = CRM_Utils_Array::value( 'is_active', $defaults );
            // Assign contribution page ID to pageId for referencing in PCP.hlp - since $id is overwritten there. dgg
            $this->assign('pageId',$this->_id);
        }

        if ( ! CRM_Utils_Array::value( 'id', $defaults ) ) {
            $defaults['target_entity_type']    = 'contribute';
            $defaults['is_approval_needed']    = 1;
            $defaults['is_tellfriend_enabled'] = 1;
            $defaults['tellfriend_limit']      = 5;
            $defaults['link_text']             = ts('Create your own fundraising page');
            
            if ( $ccReceipt = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'cc_receipt' ) ) {
                $defaults['notify_email']  = $ccReceipt;
            }
        } 
        return $defaults;
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
       CRM_PCP_BAO_PCP::buildPCPForm( $this );
       
       $this->addElement( 'checkbox', 'pcp_active', ts('Enable Personal Campaign Pages? (for this contribution page)'), null, array('onclick' => "return showHideByValue('pcp_active',true,'pcpFields','table-row','radio',false);" ) );
       
       parent::buildQuickForm( );
       $this->addFormRule(array('CRM_PCP_Form_Contribute', 'formRule') , $this );
    }
    
    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    public static function formRule( $params, $files, $self ) 
    { 
        $errors = array( );
        if ( CRM_Utils_Array::value( 'is_active', $params ) ) {
            
            if ( CRM_Utils_Array::value( 'is_tellfriend_enabled', $params ) && 
                 ( CRM_Utils_Array::value( 'tellfriend_limit', $params ) <= 0 ) ) {
                $errors['tellfriend_limit'] = ts('if Tell Friend is enable, Maximum recipients limit should be greater than zero.');
            }
            if ( ! CRM_Utils_Array::value( 'supporter_profile_id', $params ) ) {
                $errors['supporter_profile_id'] = ts('Supporter profile is a required field.');
            } else {
                if ( CRM_PCP_BAO_PCP::checkEmailProfile( $params['supporter_profile_id'] ) ){
                    $errors['supporter_profile_id'] = ts('Profile is not configured with Email address.');
                }
            }
            
            if ( $emails = CRM_Utils_Array::value( 'notify_email', $params ) ) {
                $emailArray = explode(',' ,$emails );
                foreach( $emailArray as $email ) {
                    if ( $email && !CRM_Utils_Rule::email( trim( $email ) ) ) {
                        $errors['notify_email'] = ts( 'A valid Notify Email address must be specified' );
                    }
                }    
            }
        }
        return empty($errors) ? true : $errors;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {      
      // get the submitted form values.
      $params = $this->controller->exportValues( $this->_name );
  
      // Source
      $params['entity_table'] = 'civicrm_contribution_page';
      $params['entity_id'] = $this->_id;

      // Target
      $params['target_entity_type'] = CRM_Utils_Array::value( 'target_entity_type', $params, 'contribute' );
      $params['target_entity_id'] = $this->_id;

      $dao = new CRM_PCP_DAO_PCPBlock();
      $dao->entity_table = $params['entity_table'];
      $dao->entity_id = $this->_id; 
      $dao->find(true);
      $params['id'] = $dao->id;
      $params['is_active']             =  CRM_Utils_Array::value( 'pcp_active', $params, false );
      $params['is_approval_needed']    =  CRM_Utils_Array::value( 'is_approval_needed', $params, false );
      $params['is_tellfriend_enabled'] =  CRM_Utils_Array::value( 'is_tellfriend_enabled', $params, false );

      $dao = CRM_PCP_BAO_PCP::add( $params );

      parent::endPostProcess( );
    }

    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) {
        return ts( 'Enable Personal Campaign Pages' );
    }
}

