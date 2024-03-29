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
 * This class provides the functionality to add contacts for
 * voter reservation.
 */
class CRM_Campaign_Form_Task_Reserve extends CRM_Campaign_Form_Task {

    /**
     * survet id`
     *
     * @var int
     */
    protected $_surveyId;
    
    /**
     * interviewer id
     *
     * @var int
     */
    protected $_interviewerId;

    /**
     * survey details
     *
     * @var object
     */
    protected $_surveyDetails;

    /**
     * number of voters
     *
     * @var int
     */
    protected $_numVoters;
   
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        parent::preProcess( );
        
        //get the survey id from user submitted values.
        $this->_surveyId      = $this->get( 'surveyId' );
        $this->_interviewerId = $this->get('interviewerId');
        if ( !$this->_surveyId ) {
            CRM_Core_Error::statusBounce( ts( "Could not find Survey Id.") );
        }
        if ( !$this->_interviewerId ) {
            CRM_Core_Error::statusBounce( ts( "Missing Interviewer contact." ) );
        }
        if ( !is_array( $this->_contactIds ) || empty( $this->_contactIds ) ) {
            CRM_Core_Error::statusBounce( ts( "Could not find contacts for reservation.") );
        }
        
        $params = array( 'id' => $this->_surveyId );
        CRM_Campaign_BAO_Survey::retrieve( $params, $this->_surveyDetails );
        
        //get the survey activities.
        $activityStatus = CRM_Core_PseudoConstant::activityStatus( 'name' );
        $statusIds = array( );
        foreach ( array( 'Scheduled' ) as $name ) {
            if ( $statusId = array_search( $name, $activityStatus ) ) {
                $statusIds[] = $statusId;
            }
        }
        
        // these are the activities count that are linked to the current 
        // interviewer and current survey and not the list of ALL survey activities
        $this->_numVoters = CRM_Campaign_BAO_Survey::getSurveyActivities( $this->_surveyId,
                                                                          $this->_interviewerId,
                                                                          $statusIds,
                                                                          null, 
                                                                          true );
        //validate the selected survey.
        $this->validateSurvey( );
        $this->assign( 'surveyTitle', $this->_surveyDetails['title'] );
        
        //append breadcrumb to survey dashboard.
        if ( CRM_Campaign_BAO_Campaign::accessCampaign( ) ) {
            $url = CRM_Utils_System::url( 'civicrm/campaign', 'reset=1&subPage=survey' );
            CRM_Utils_System::appendBreadCrumb( array( array( 'title' => ts('Survey(s)'), 'url' => $url ) ) );
        }
        
        //set the title.
        CRM_Utils_System::setTitle( ts( 'Reserve Respondents' ) );
    }
    
    function validateSurvey( ) 
    {
        $errorMsg = null;
        $maxVoters = CRM_Utils_Array::value('max_number_of_contacts', $this->_surveyDetails );
        if ( $maxVoters ) {
            if ( $maxVoters <= $this->_numVoters ) {
                $errorMsg = ts( 'The maximum number of contacts is already reserved for this interviewer.' );
            } else if ( count( $this->_contactIds ) > ( $maxVoters - $this->_numVoters ) ) {
                $errorMsg = ts( 'You can reserve a maximum of %1 contact(s) at a time for this survey.', 
                                array( 1 => $maxVoters - $this->_numVoters ) );
            }
        }

        $defaultNum = CRM_Utils_Array::value( 'default_number_of_contacts', $this->_surveyDetails );
        if ( !$errorMsg && $defaultNum && (count( $this->_contactIds ) > $defaultNum) ) {
            $errorMsg = ts( 'You can reserve a maximum of %1 contact(s) at a time for this survey.', 
                            array( 1 => $defaultNum ) );
        }

        if ( $errorMsg ) {
            CRM_Core_Error::statusBounce( $errorMsg );
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
        // allow to add contact to either new or existing group.
        $this->addElement( 'text', 'newGroupName', ts( 'Name for new group' ) );
        $this->addElement( 'text', 'newGroupDesc', ts( 'Description of new group' ) );
        $groups = CRM_Core_PseudoConstant::group( );
        $hasExistingGroups = false;
        if ( is_array( $groups ) && !empty( $groups ) ) {
            $hasExistingGroups = true;
            $this->addElement( 'select', 'groups', ts( 'Add respondent(s) to existing group(s)' ), 
                               $groups, array('multiple' => "multiple", 'size' => 5));
        }
        $this->assign( 'hasExistingGroups', $hasExistingGroups );
        
        $buttons = array( array ( 'type'      => 'done',
                                  'name'      => ts('Reserve'),
                                  'subName'   => 'reserve',
                                  'isDefault' => true  ) );
        
        if ( CRM_Core_Permission::check( 'manage campaign' ) ||
             CRM_Core_Permission::check( 'administer CiviCampaign' ) ||
             CRM_Core_Permission::check( 'interview campaign contacts' ) ) { 
            $buttons[] = array ( 'type'      => 'next',
                                 'name'      => ts('Reserve and Interview'),
                                 'subName'   => 'reserveToInterview' );
        }
        $buttons[] = array ( 'type'       => 'back',
                             'name'      => ts('Cancel') );
        
        $this->addButtons( $buttons );
        $this->addFormRule( array( 'CRM_Campaign_Form_Task_Reserve', 'formRule' ), $this );
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
    static function formRule( $fields, $files, $self ) 
    {
        $errors = array( );
        $invalidGroupName = false;
        if ( CRM_Utils_Array::value( 'newGroupName', $fields ) ) {
            $title = trim( $fields['newGroupName'] );
            $name  = CRM_Utils_String::titleToVar( $title );
            $query  = 'select count(*) from civicrm_group where name like %1 OR title like %2';
            $grpCnt = CRM_Core_DAO::singleValueQuery( $query, array( 1 => array( $name,  'String' ),
                                                                     2 => array( $title, 'String' ) ) );
            if ( $grpCnt ) {
                $invalidGroupName = true;
                $errors['newGroupName'] = ts( 'Group \'%1\' already exists.', array( 1 => $fields['newGroupName']));
            }
        }
        $self->assign( 'invalidGroupName', $invalidGroupName );
        
        return empty( $errors ) ? true : $errors;
    }
    
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess( ) 
    {        
        //add reservation.
        $countVoters    = 0;
        $maxVoters      = CRM_Utils_Array::value('max_number_of_contacts', $this->_surveyDetails);
        $activityStatus = CRM_Core_PseudoConstant::activityStatus( 'name' );
        $statusHeld     = array_search( 'Scheduled', $activityStatus );
        
        $reservedVoterIds = array( );
        foreach ( $this->_contactIds as $cid ) {
            $subject =  ts( '%1', array( 1 =>  $this->_surveyDetails['title'] ) ). ' - ' . ts( 'Respondent Reservation' );
            $session = CRM_Core_Session::singleton( );
            $activityParams = array( 'source_contact_id'   => $session->get( 'userID' ),
                                     'assignee_contact_id' => array( $this->_interviewerId ),
                                     'target_contact_id'   => array( $cid ),
                                     'source_record_id'    => $this->_surveyId,
                                     'activity_type_id'    => $this->_surveyDetails['activity_type_id'],
                                     'subject'             => $subject,
                                     'activity_date_time'  => date('YmdHis'),
                                     'status_id'           => $statusHeld,
                                     'skipRecentView'      => 1,
                                     'campaign_id'         => CRM_Utils_Array::value('campaign_id',$this->_surveyDetails)
                                     );
            $activity = CRM_Activity_BAO_Activity::create( $activityParams );
            if ( $activity->id ) {
                $countVoters++;
                $reservedVoterIds[$cid] = $cid; 
            }
            if ( $maxVoters && ( $maxVoters <= ( $this->_numVoters + $countVoters ) ) ) {
                break;
            }
        }
        
        $status = array( );
        if ( $countVoters > 0 ) {
            $status[] = ts('Reservation has been added for %1 Contact(s).', array( 1 => $countVoters ));
        }
        if ( count($this->_contactIds) > $countVoters ) {
            $status[] = ts( 'Reservation did not add for %1 Contact(s).', 
                            array( 1 => ( count($this->_contactIds) - $countVoters) ) );
        }
        
        //add reserved voters to groups.
        $groupAdditions = $this->_addRespondentToGroup( $reservedVoterIds );
        if ( !empty( $groupAdditions )  ) {
            $status[] = ts( '<br />Respondent(s) has been added to %1 group(s).', 
                            array( 1 => implode( ', ', $groupAdditions ) ) );
        }
        
        if ( !empty($status) ) {
            CRM_Core_Session::setStatus( implode('&nbsp;&nbsp;', $status) );
        }
        
        //get ready to jump to voter interview form.
        $buttonName = $this->controller->getButtonName( );
        if ( !empty( $reservedVoterIds ) && 
             $buttonName == '_qf_Reserve_next_reserveToInterview' ) {
            $this->controller->set( 'surveyId',           $this->_surveyId );
            $this->controller->set( 'contactIds',         $reservedVoterIds );
            $this->controller->set( 'interviewerId',      $this->_interviewerId );
            $this->controller->set( 'reserveToInterview', true );
        }
        
    }
    
    private function _addRespondentToGroup( $contactIds ) 
    {
        $groupAdditions = array( );
        if ( empty( $contactIds ) ) return $groupAdditions;
        
        $params       = $this->controller->exportValues( $this->_name );
        $groups       = CRM_Utils_Array::value( 'groups',       $params, array( ) );
        $newGroupName = CRM_Utils_Array::value( 'newGroupName', $params );
        $newGroupDesc = CRM_Utils_Array::value( 'newGroupDesc', $params );
        
        $newGroupId = null;
        //create new group. 
        if ( $newGroupName ) {
            $grpParams = array( 'title'         => $newGroupName,
                                'description'   => $newGroupDesc,
                                'is_active'     => true  );
            $group = CRM_Contact_BAO_Group::create( $grpParams );
            $groups[] = $newGroupId = $group->id;
        }
        
        //add the respondents to groups.
        if ( is_array( $groups ) ) {
            $existingGroups = CRM_Core_PseudoConstant::group( );
            foreach ( $groups as $groupId ) {
                $addCount = CRM_Contact_BAO_GroupContact::addContactsToGroup( $contactIds, $groupId );
                $totalCount = CRM_Utils_Array::value( 1, $addCount );
                if ( $groupId == $newGroupId ) {
                    $name = $newGroupName;
                    $new = true;
                } else {
                    $name = $existingGroups[$groupId];
                    $new = false;
                }
                if ( $totalCount ) {
                    $url = CRM_Utils_System::url( 'civicrm/group/search',
                                                  'reset=1&force=1&context=smog&gid=' . $groupId );
                    $groupAdditions[] =  '<a href="' . $url .'">'. $name. '</a>';
                }
            }
        }
        
        return $groupAdditions;
    }
    
}

