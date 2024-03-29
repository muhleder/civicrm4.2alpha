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
 * This class provides the functionality to save a search
 * Saved Searches are used for saving frequently used queries
 */
class CRM_Contact_Form_Task_SaveSearch extends CRM_Contact_Form_Task {
    /**
     * saved search id if any
     *
     * @var int
     */
    protected $_id;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess()
    {
        $this->_id   = null;

        // get the submitted values of the search form
        // we'll need to get fv from either search or adv search in the future
        if ( $this->_action == CRM_Core_Action::ADVANCED ) {
            $values = $this->controller->exportValues( 'Advanced' );
        } else if ( $this->_action == CRM_Core_Action::PROFILE ) {
            $values = $this->controller->exportValues( 'Builder' );
        } else if ( $this->_action == CRM_Core_Action::COPY ) {
            $values = $this->controller->exportValues( 'Custom' );            
        } else {
            $values = $this->controller->exportValues( 'Basic' );
        }

        $this->_task = CRM_Utils_Array::value( 'task', $values );
        $crmContactTaskTasks = CRM_Contact_Task::taskTitles();
        $this->assign( 'taskName', CRM_Utils_Array::value( $this->_task , $crmContactTaskTasks ) );
    }

    /**
     * Build the form - it consists of
     *    - displaying the QILL (query in local language)
     *    - displaying elements for saving the search
     *
     * @access public
     * @return void
     */
    function buildQuickForm()
    {
        // get the qill 
        $query = new CRM_Contact_BAO_Query( $this->get( 'queryParams' ) );
        $qill = $query->qill( );

        // need to save qill for the smarty template
        $this->assign('qill', $qill);
        
        // the name and description are actually stored with the group and not the saved search
        $this->add('text', 'title', ts('Name'),
                   CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Group', 'title'), true);
            

        $this->addElement('textarea', 'description', ts('Description'),
                          CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Group', 'description'));

        $groupTypes = CRM_Core_OptionGroup::values( 'group_type', true );
        unset( $groupTypes['Access Control'] );
        if ( ! CRM_Core_Permission::access( 'CiviMail' ) ) {
            $isWorkFlowEnabled = CRM_Mailing_Info::workflowEnabled( );
            if ( $isWorkFlowEnabled && 
                 !CRM_Core_Permission::check( 'create mailings' ) &&
                 !CRM_Core_Permission::check( 'schedule mailings' ) &&
                 !CRM_Core_Permission::check( 'approve mailings' )
                 ) {
                unset( $groupTypes['Mailing List'] );
            }
        }

        if ( ! empty( $groupTypes ) ) {
            $this->addCheckBox( 'group_type',
                                ts( 'Group Type' ),
                                $groupTypes,
                                null, null, null, null, '&nbsp;&nbsp;&nbsp;' );
        }
        
        // get the group id for the saved search
        $groupID = null;
        if ( isset( $this->_id ) ) { 
            $groupID = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group',
                                                    $this->_id,
                                                    'id',
                                                    'saved_search_id' );
            $this->addDefaultButtons( ts('Update Smart Group') );
        } else {
            $this->addDefaultButtons( ts('Save Smart Group') );
        }
        $this->addRule( 'title', ts('Name already exists in Database.'),
                        'objectExists', array( 'CRM_Contact_DAO_Group', $groupID, 'title' ) );

    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return void
     */
    public function postProcess()
    {
        // saved search form values
        // get form values of all the forms in this controller
        $formValues = $this->controller->exportValues( );

        $isAdvanced      = $this->get('isAdvanced');
        $isSearchBuilder = $this->get('isSearchBuilder');

        // add mapping record only for search builder saved search
        $mappingId = null;
        if ( $isAdvanced == '2' && $isSearchBuilder == '1' ) {
            //save the mapping for search builder

            if ( !$this->_id ) {
                //save record in mapping table
                $mappingParams = array('mapping_type' => 'Search Builder');
                $temp      = array();
                $mapping   = CRM_Core_BAO_Mapping::add($mappingParams, $temp) ;
                $mappingId = $mapping->id;                 
            } else {
                //get the mapping id from saved search
                
                $savedSearch     = new CRM_Contact_BAO_SavedSearch();
                $savedSearch->id = $this->_id;
                $savedSearch->find(true);
                $mappingId = $savedSearch->mapping_id; 
            }
            
            //save mapping fields
            CRM_Core_BAO_Mapping::saveMappingFields($formValues, $mappingId);
        }

        //save the search
        $savedSearch                   = new CRM_Contact_BAO_SavedSearch();
        $savedSearch->id               =  $this->_id;
        $savedSearch->form_values      =  serialize($this->get( 'formValues' ));
        $savedSearch->mapping_id       =  $mappingId;
        $savedSearch->search_custom_id =  $this->get( 'customSearchID' );
        $savedSearch->save( );
        $this->set('ssID', $savedSearch->id);
        CRM_Core_Session::setStatus( ts('Your smart group has been saved as \'%1\'.', array(1 => $formValues['title'])) );

        // also create a group that is associated with this saved search only if new saved search
        $params = array( );
        $params['title'      ]     = $formValues['title'];
        $params['description']     = $formValues['description'];
        if ( isset( $formValues['group_type'] ) &&
             is_array( $formValues['group_type'] ) ) {
            $params['group_type'] =
                CRM_Core_DAO::VALUE_SEPARATOR . 
                implode( CRM_Core_DAO::VALUE_SEPARATOR,
                         array_keys( $formValues['group_type'] ) ) .
                CRM_Core_DAO::VALUE_SEPARATOR;
        } else {
            $params['group_type'] = '';
        }
        $params['visibility' ]     = 'User and User Admin Only';
        $params['saved_search_id'] = $savedSearch->id;
        $params['is_active']       = 1;
        
        if ( $this->_id ) {
            $params['id'] = CRM_Contact_BAO_SavedSearch::getName( $this->_id, 'id' );
        }

        $group = CRM_Contact_BAO_Group::create( $params );

        // CRM-9464
        $this->_id = $savedSearch->id;
    }
}


