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
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
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
 * Files required
 */

/**
 * This file is for activity search
 */
class CRM_Activity_Form_Search extends CRM_Core_Form
{
    /** 
     * Are we forced to run a search 
     * 
     * @var int 
     * @access protected 
     */ 
    protected $_force; 

    /** 
     * name of search button 
     * 
     * @var string 
     * @access protected 
     */ 
    protected $_searchButtonName;

    /** 
     * name of print button 
     * 
     * @var string 
     * @access protected 
     */ 
    protected $_printButtonName; 
 
    /** 
     * name of action button 
     * 
     * @var string 
     * @access protected 
     */ 
    protected $_actionButtonName;

    /** 
     * form values that we will be using 
     * 
     * @var array 
     * @access protected 
     */ 
    public $_formValues; 

    /**
     * the params that are sent to the query
     * 
     * @var array 
     * @access protected 
     */ 
    protected $_queryParams;
    
    /** 
     * have we already done this search 
     * 
     * @access protected 
     * @var boolean 
     */ 
    protected $_done; 

    /**
     * are we restricting ourselves to a single contact
     *
     * @access protected  
     * @var boolean  
     */  
    protected $_single = false;

    /** 
     * are we restricting ourselves to a single contact 
     * 
     * @access protected   
     * @var boolean   
     */   
    protected $_limit = null;

    /** 
     * what context are we being invoked from 
     *    
     * @access protected      
     * @var string 
     */      
    protected $_context = null; 
    
    /** 
     * prefix for the controller
     * 
     */
    protected $_prefix = "activity_";

    protected $_defaults;

    /**
     * the saved search ID retrieved from the GET vars
     *
     * @var int
     * @access protected
     */
    protected $_ssID;

    /** 
     * processing needed for buildForm and later 
     * 
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    { 
        $this->set( 'searchFormName', 'Search' );

        /** 
         * set the button names 
         */   
        
        $this->_searchButtonName = $this->getButtonName( 'refresh' ); 
        $this->_printButtonName  = $this->getButtonName( 'next', 'print' ); 
        $this->_actionButtonName = $this->getButtonName( 'next', 'action' ); 
        
        $this->_done = false;
        $this->defaults = array( );
        
        /* 
         * we allow the controller to set force/reset externally, useful when we are being 
         * driven by the wizard framework 
         */ 
        $this->_reset   = CRM_Utils_Request::retrieve( 'reset', 'Boolean', CRM_Core_DAO::$_nullObject ); 
        $this->_force   = CRM_Utils_Request::retrieve( 'force', 'Boolean', $this, false ); 
        $this->_limit   = CRM_Utils_Request::retrieve( 'limit', 'Positive', $this );
        $this->_context = CRM_Utils_Request::retrieve( 'context', 'String', $this, false, 'search' );
        
        $this->assign( "context", $this->_context );

        // get user submitted values  
        // get it from controller only if form has been submitted, else preProcess has set this  
        if ( ! empty( $_POST ) && !$this->controller->isModal( ) ) { 
            $this->_formValues = $this->controller->exportValues( $this->_name );  
        } else {
            $this->_formValues = $this->get( 'formValues' ); 
        } 

        if ( empty( $this->_formValues ) ) {
            if ( isset( $this->_ssID ) ) {
                $this->_formValues = CRM_Contact_BAO_SavedSearch::getFormValues( $this->_ssID );
            }
        }

        if ( $this->_force ) { 
            $this->postProcess( );
            $this->set( 'force', 0 );
        }
        
        $sortID = null; 
        if ( $this->get( CRM_Utils_Sort::SORT_ID ) ) { 
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID ), 
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) ); 
        } 
        
        $this->_queryParams = CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
        $selector = new CRM_Activity_Selector_Search( $this->_queryParams,
                                                      $this->_action,
                                                      null,
                                                      $this->_single,
                                                      $this->_limit,
                                                      $this->_context );
        $prefix = null;
        if ( $this->_context == 'user' ) {
            $prefix = $this->_prefix;
        }

        $this->assign( "{$prefix}limit", $this->_limit );
        $this->assign( "{$prefix}single", $this->_single );

        $controller = new CRM_Core_Selector_Controller( $selector ,  
                                                        $this->get( CRM_Utils_Pager::PAGE_ID ),  
                                                        $sortID,  
                                                        CRM_Core_Action::VIEW, 
                                                        $this, 
                                                        CRM_Core_Selector_Controller::TRANSFER,
                                                        $prefix );
        $controller->setEmbedded( true ); 
        $controller->moveFromSessionToTemplate(); 
        
        $this->assign( 'summary', $this->get( 'summary' ) );
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        $this->addElement('text', 'sort_name', ts('With (name or email)'), CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'sort_name') );
        
        CRM_Activity_BAO_Query::buildSearchForm( $this );
       
        /* 
         * add form checkboxes for each row. This is needed out here to conform to QF protocol 
         * of all elements being declared in builQuickForm 
         */ 
        $rows = $this->get( 'rows' );
        if ( is_array( $rows ) ) {
            if ( !$this->_single ) {
                $this->addElement( 'checkbox', 'toggleSelect', null, null, 
                					array( 'onclick' => "toggleTaskAction( true ); return toggleCheckboxVals('mark_x_',this);" ) ); 
                foreach ($rows as $row) { 
                    $this->addElement( 'checkbox', $row['checkbox'], 
                                       null, null, 
                                       array( 'onclick' => "toggleTaskAction( true ); return checkSelectedBox('" . $row['checkbox'] . "');" )
                                       ); 
                }
            }
            
            $total = $cancel = 0;
            
            $permission = CRM_Core_Permission::getPermission( );
            
            $tasks = array( '' => ts('- actions -') ) + CRM_Activity_Task::permissionedTaskTitles( $permission );
            
            $this->add('select', 'task'   , ts('Actions:') . ' '    , $tasks    ); 
            $this->add('submit', $this->_actionButtonName, ts('Go'), 
                       array( 'class'   => 'form-submit',
                              'id'      => 'Go',
                              'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 0);" ) );
             
            $this->add('submit', $this->_printButtonName, ts('Print'), 
                       array( 'class' => 'form-submit', 
                              'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 1);" ) );
            
            // need to perform tasks on all or selected items ? using radio_ts(task selection) for it 
            $this->addElement('radio', 'radio_ts', null, '', 'ts_sel', array( 'checked' => 'checked') ); 
            $this->addElement('radio', 'radio_ts', null, '', 'ts_all', 
            array( 'onchange' => $this->getName().".toggleSelect.checked = false; toggleCheckboxVals('mark_x_',this); toggleTaskAction( true );" ) );
        }

        // add buttons 
        $this->addButtons( array( 
                                 array ( 'type'      => 'refresh', 
                                         'name'      => ts('Search') , 
                                         'isDefault' => true ) 
                                  )    );
    }
   
 
    /**
     * The post processing of the form gets done here.
     *
     * Key things done during post processing are
     *      - check for reset or next request. if present, skip post procesing.
     *      - now check if user requested running a saved search, if so, then
     *        the form values associated with the saved search are used for searching.
     *      - if user has done a submit with new values the regular post submissing is 
     *        done.
     * The processing consists of using a Selector / Controller framework for getting the
     * search results.
     *
     * @param
     *
     * @return void 
     * @access public
     */
    function postProcess( ) 
    {
        if ( $this->_done ) {
            return;
        }
        
        $this->_done = true;
        
        if ( ! empty( $_POST ) ) { 
            $this->_formValues = $this->controller->exportValues($this->_name);
        }
        
        $this->fixFormValues( );
        
        if ( isset( $this->_ssID ) && empty( $_POST ) ) {
            // if we are editing / running a saved search and the form has not been posted
            $this->_formValues = CRM_Contact_BAO_SavedSearch::getFormValues( $this->_ssID );
        }
        if ( CRM_Utils_Array::value( 'activity_survey_id', $this->_formValues ) ) {
            // if the user has choosen a survey but not any activity type, we force the activity type
            $sid = CRM_Utils_Array::value( 'activity_survey_id', $this->_formValues ) ;
            $activity_type_id = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', $sid, 'activity_type_id' );

            $this->_formValues['activity_type_id'][ $activity_type_id ] = 1;
        }

        if ( ! CRM_Utils_Array::value( 'activity_test', $this->_formValues ) ) {
            $this->_formValues["activity_test"] = 0;
        }
        if ( ! CRM_Utils_Array::value( 'activity_contact_name', $this->_formValues ) && ! CRM_Utils_Array::value( 'contact_id', $this->_formValues ) ) {
            $this->_formValues['activity_role'] = null;
        }
        CRM_Core_BAO_CustomValue::fixFieldValueOfTypeMemo( $this->_formValues );
        
        $this->_queryParams = CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
        
        $this->set( 'formValues' , $this->_formValues  );
        $this->set( 'queryParams', $this->_queryParams );
        
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->_actionButtonName || $buttonName == $this->_printButtonName ) { 
            // check actionName and if next, then do not repeat a search, since we are going to the next page    
            // hack, make sure we reset the task values 
            $stateMachine =& $this->controller->getStateMachine( );
            $formName     =  $stateMachine->getTaskFormName( ); 
            $this->controller->resetPage( $formName ); 
            return; 
        }
        
        $sortID = null; 
        if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) { 
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ), 
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) ); 
        }
        
        $this->_queryParams = CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
        
        $selector = new CRM_Activity_Selector_Search( $this->_queryParams,
                                                      $this->_action,
                                                      null,
                                                      $this->_single,
                                                      $this->_limit,
                                                      $this->_context );
        $selector->setKey( $this->controller->_key );
        
        $prefix = null;
        if ( $this->_context == 'basic' || $this->_context == 'user' ) {
            $prefix = $this->_prefix;
        }
        
        $controller = new CRM_Core_Selector_Controller( $selector , 
                                                        $this->get( CRM_Utils_Pager::PAGE_ID ), 
                                                        $sortID, 
                                                        CRM_Core_Action::VIEW,
                                                        $this,
                                                        CRM_Core_Selector_Controller::SESSION,
                                                        $prefix);
        $controller->setEmbedded( true );
        $query =& $selector->getQuery( );
        
        if ( $this->_context == 'user' ) {
            $query->setSkipPermission( true );
        }
        $controller->run(); 
    }
    
    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @return None
     * @access public
     * @see valid_date
     */
    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Activity_Form_Search', 'formRule' ) );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     * @param array $errors list of errors to be posted back to the form
     *
     * @return void
     * @static
     * @access public
     */
    static function formRule( $fields )
    {
        $errors = array( );
        
        if ( !empty($errors) ) {
            return $errors;
        } 
        
        return true;
    }
    
    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues( ) 
        {
            $defaults = array();
            $defaults = $this->_formValues;
            return $defaults;
        }
    
    
    function fixFormValues( )
    {
        if ( ! $this->_force ) {
            return;
        }
        $status = CRM_Utils_Request::retrieve( 'status', 'String', $this );
        if ( $status ) {
            $this->_formValues['activity_status'] = $status;
            $this->_defaults  ['activity_status'] = $status;
        }

        $survey = CRM_Utils_Request::retrieve( 'survey', 'Positive',
                                              CRM_Core_DAO::$_nullObject );
        if ( $survey ) {
            $this->_formValues['activity_survey_id'] = $survey;
        }
        $cid = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
        
        if ( $cid ) {
            $cid = CRM_Utils_Type::escape( $cid, 'Integer' );
            if ( $cid > 0 ) {
                $this->_formValues['contact_id'] = $cid;

                $activity_role = CRM_Utils_Request::retrieve( 'activity_role', 'Positive', $this );

                if ( $activity_role ) {
                    $this->_formValues['activity_role'] = $activity_role;
                } else {
                    list( $display, $image ) = CRM_Contact_BAO_Contact::getDisplayAndImage( $cid );
                    $this->_defaults['sort_name'] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $cid, 'sort_name' );
                }
                // also assign individual mode to the template
                $this->_single = true;
            }
        }
    }
    
    function getFormValues( ) 
    {
        return null;
    }

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts( 'Find Activities' );
    }
}
