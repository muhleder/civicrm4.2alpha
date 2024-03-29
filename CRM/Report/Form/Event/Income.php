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


class CRM_Report_Form_Event_Income extends CRM_Report_Form {

    const  
        ROW_COUNT_LIMIT = 2;

    protected $_summary = null;
    
    protected $_add2groupSupported = false;
    
    function __construct( ) {

        $this->_columns = 
            array( 
                  'civicrm_event' =>
                  array( 'dao'     => 'CRM_Event_DAO_Event',
                         'filters' => 
                         array( 'id' => 
                                array( 'title'         => ts( 'Event Title' ),
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'type'          => CRM_Utils_Type::T_INT,
                                       'options'       => CRM_Event_PseudoConstant::event( null, null,
                                                              "is_template IS NULL OR is_template = 0" ) ), 
                                ),
                         ),
                  );
        
        parent::__construct( );
    }
    
    function preProcess( ) {
        $this->_csvSupported = false;
        parent::preProcess( );
    }
    
    function buildEventReport( $eventIDs ) { 
        
        $this->assign( 'events', $eventIDs );
        
        $eventID = implode(',', $eventIDs );

        $participantStatus  = CRM_Event_PseudoConstant::participantStatus( null, "is_counted = 1" );
        $participantRole    = CRM_Event_PseudoConstant::participantRole( );
        $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument();

        $rows = $eventSummary = $roleRows = $statusRows = $instrumentRows = $count = array( );

        $optionGroupDAO = new CRM_Core_DAO_OptionGroup();
        $optionGroupDAO->name = 'event_type';
        $optionGroupId = null;
        if ($optionGroupDAO->find(true) ) {
            $optionGroupId = $optionGroupDAO->id;
        }
        //show the income of active participant status (Counted = filter = 1)
        $activeParticipantStatusIDArray = $activeParticipantStatusLabelArray = array();
        foreach ( $participantStatus as $id => $label ) {
            $activeParticipantStatusIDArray[]     = $id;
            $activeParticipantStatusLabelArray[]  = $label;
        }
        $activeParticipantStatus      = implode(',', $activeParticipantStatusIDArray );
        $activeparticipnatStutusLabel = implode(', ', $activeParticipantStatusLabelArray );
        $activeParticipantClause = " AND civicrm_participant.status_id IN ( $activeParticipantStatus ) ";

        $sql = "
            SELECT  civicrm_event.id                    as event_id,
                    civicrm_event.title                 as event_title,
                    civicrm_event.max_participants      as max_participants, 
                    civicrm_event.start_date            as start_date,
                    civicrm_event.end_date              as end_date, 
                    civicrm_option_value.label          as event_type, 
                    SUM(civicrm_participant.fee_amount) as total,
                    COUNT(civicrm_participant.id)       as participant

            FROM       civicrm_event
            LEFT JOIN  civicrm_option_value 
                   ON  ( civicrm_event.event_type_id = civicrm_option_value.value AND
                         civicrm_option_value.option_group_id = {$optionGroupId} )
            LEFT JOIN  civicrm_participant ON ( civicrm_event.id = civicrm_participant.event_id 
                       {$activeParticipantClause} AND civicrm_participant.is_test  = 0 )

            WHERE      civicrm_event.id IN( {$eventID}) 
                      
            GROUP BY   civicrm_event.id
            ";
        $eventDAO  = CRM_Core_DAO::executeQuery( $sql );

        while ( $eventDAO->fetch( ) ) {
            $eventSummary[$eventDAO->event_id]['Title']                   = $eventDAO->event_title;
            $eventSummary[$eventDAO->event_id]['Max Participants']        = $eventDAO->max_participants;
            $eventSummary[$eventDAO->event_id]['Start Date']              = CRM_Utils_Date::customFormat( $eventDAO->start_date );
            $eventSummary[$eventDAO->event_id]['End Date']                = CRM_Utils_Date::customFormat( $eventDAO->end_date );
            $eventSummary[$eventDAO->event_id]['Event Type']              = $eventDAO->event_type;
            $eventSummary[$eventDAO->event_id]['Event Income']            = CRM_Utils_Money::format( $eventDAO->total);
            $eventSummary[$eventDAO->event_id]['Registered Participant']  = "{$eventDAO->participant} ({$activeparticipnatStutusLabel})";
        }
        $this->assign_by_ref( 'summary', $eventSummary );

        //Total Participant Registerd for the Event
        $pariticipantCount = "
            SELECT COUNT(civicrm_participant.id ) as count, civicrm_participant.event_id as event_id

            FROM     civicrm_participant

            WHERE    civicrm_participant.event_id IN( {$eventID}) AND 
                     civicrm_participant.is_test  = 0 
                     {$activeParticipantClause}
            GROUP BY civicrm_participant.event_id
             ";
        
        $counteDAO  = CRM_Core_DAO::executeQuery( $pariticipantCount );
        while ( $counteDAO->fetch( ) ) {
            $count[$counteDAO->event_id] = $counteDAO->count;
        }

        //Count the Participant by Role ID for Event
        $role = "
            SELECT civicrm_participant.role_id         as ROLEID, 
                   COUNT( civicrm_participant.id )     as participant, 
                   SUM(civicrm_participant.fee_amount) as amount,
                   civicrm_participant.event_id        as event_id

            FROM     civicrm_participant

            WHERE    civicrm_participant.event_id IN ( {$eventID}) AND
                     civicrm_participant.is_test  = 0 
                     {$activeParticipantClause}
            GROUP BY civicrm_participant.role_id, civicrm_participant.event_id
            ";

        $roleDAO  = CRM_Core_DAO::executeQuery( $role );
       
        while ( $roleDAO->fetch( ) ) {
            // fix for multiple role, CRM-6507
            $roles = explode( CRM_Core_DAO::VALUE_SEPARATOR, $roleDAO->ROLEID );
            foreach( $roles as $roleId ) {
                if ( !isset($roleRows[$roleDAO->event_id][$participantRole[$roleId]] ) ) {
                    $roleRows[$roleDAO->event_id][$participantRole[$roleId]]['total']  = 0;
                    $roleRows[$roleDAO->event_id][$participantRole[$roleId]]['round']  = 0;
                    $roleRows[$roleDAO->event_id][$participantRole[$roleId]]['amount'] = 0;
                }
                $roleRows[$roleDAO->event_id][$participantRole[$roleId]]['total'] += $roleDAO->participant;
                $roleRows[$roleDAO->event_id][$participantRole[$roleId]]['amount'] += $roleDAO->amount;
            }
        }

        foreach( $roleRows as $eventId => $roleInfo ) {
            foreach( $participantRole as $roleName ) {
                if ( isset($roleInfo[$roleName]) ) {
                    $roleRows[$eventId][$roleName]['round'] =  round( ( $roleRows[$eventId][$roleName]['total'] / $count[$eventId] ) * 100, 2 );
                }
            }
        }
        
        $rows['Role'] = $roleRows;

        //Count the Participant by status ID for Event
        $status = "
            SELECT civicrm_participant.status_id       as STATUSID, 
                   COUNT( civicrm_participant.id )     as participant, 
                   SUM(civicrm_participant.fee_amount) as amount,
                   civicrm_participant.event_id        as event_id

            FROM     civicrm_participant

            WHERE    civicrm_participant.event_id IN ({$eventID}) AND
                     civicrm_participant.is_test  = 0 
                     {$activeParticipantClause}
            GROUP BY civicrm_participant.status_id, civicrm_participant.event_id
            ";

        $statusDAO = CRM_Core_DAO::executeQuery( $status );
      
        while ( $statusDAO->fetch( ) ) {
            $statusRows[$statusDAO->event_id][$participantStatus[$statusDAO->STATUSID]]['total'] = $statusDAO->participant;
            $statusRows[$statusDAO->event_id][$participantStatus[$statusDAO->STATUSID]]['round'] = 
                round( ( $statusDAO->participant / $count[$statusDAO->event_id] ) * 100, 2 );
            $statusRows[$statusDAO->event_id][$participantStatus[$statusDAO->STATUSID]]['amount'] = $statusDAO->amount;
        }

        $rows['Status'] = $statusRows;

        //Count the Participant by payment instrument ID for Event
        //e.g. Credit Card, Check,Cash etc
        $paymentInstrument = "
            SELECT c.payment_instrument_id               as INSTRUMENT, 
                   COUNT( c.id )                         as participant, 
                   SUM(civicrm_participant.fee_amount)   as amount,
                   civicrm_participant.event_id          as event_id

            FROM      civicrm_participant
            LEFT JOIN civicrm_participant_payment pp ON(pp.participant_id = civicrm_participant.id )
            LEFT JOIN civicrm_contribution c ON ( pp.contribution_id = c.id)

            WHERE     civicrm_participant.event_id IN ( {$eventID}) AND
                      civicrm_participant.is_test  = 0
                      {$activeParticipantClause}
            GROUP BY  c.payment_instrument_id, civicrm_participant.event_id
            ";

        $instrumentDAO = CRM_Core_DAO::executeQuery( $paymentInstrument );
       
        while ( $instrumentDAO->fetch( ) ) {
            //allow only if instrument is present in contribution table
            if ( $instrumentDAO->INSTRUMENT ) {
                $instrumentRows[$instrumentDAO->event_id][$paymentInstruments[$instrumentDAO->INSTRUMENT]]['total'] = 
                    $instrumentDAO->participant;
                $instrumentRows[$instrumentDAO->event_id][$paymentInstruments[$instrumentDAO->INSTRUMENT]]['round'] = 
                    round(($instrumentDAO->participant / $count[$instrumentDAO->event_id] ) * 100, 2 );
                $instrumentRows[$instrumentDAO->event_id][$paymentInstruments[$instrumentDAO->INSTRUMENT]]['amount'] = $instrumentDAO->amount;
            }
        }
        $rows['Payment Method'] = $instrumentRows;
        
        $this->assign_by_ref( 'rows', $rows );
        if ( !$this->_setVariable ) {
            $this->_params['id_value'] = null;
        }
        $this->assign( 'statistics',  $this->statistics( $eventIDs ) );
    }

    function statistics( &$eventIDs ) {
        $statistics = array();
        $count      = count($eventIDs);
        $this->countStat( $statistics, $count );
        if ( $this->_setVariable ) {
            $this->filterStat( $statistics );
        }
        
        return $statistics;
    }

    function limit( $rowCount = self::ROW_COUNT_LIMIT ) {
        parent::limit( $rowCount );
        
        //modify limit
        $pageId = $this->get( CRM_Utils_Pager::PAGE_ID );
        
        //if pageId is greator than last page then display last page.
        if ( (( $pageId * self::ROW_COUNT_LIMIT )- 1) > $this->_rowsFound ) {
            $pageId = ceil( (float)$this->_rowsFound / (float)self::ROW_COUNT_LIMIT );
            $this->set( CRM_Utils_Pager::PAGE_ID, $pageId );
        }
        $this->_limit  = ( $pageId - 1 ) * self::ROW_COUNT_LIMIT;
    }

    function setPager( ) {
        $params = array( 'total'        => $this->_rowsFound,
                         'rowCount'     => self::ROW_COUNT_LIMIT,
                         'status'       => ts( 'Records %%StatusMessage%%' ),
                         'buttonBottom' => 'PagerBottomButton',
                         'buttonTop'    => 'PagerTopButton',
                         'pageID'       => $this->get( CRM_Utils_Pager::PAGE_ID ) );
        
        $pager = new CRM_Utils_Pager( $params );
        $this->assign_by_ref( 'pager', $pager );
    }
    

    function postProcess( ) {
        $this->beginPostProcess( );
        $this->_setVariable = true;
        if ( empty( $this->_params['id_value'][0] ) ) {
            $this->_params['id_value'] = array();
            $this->_setVariable = false;
            $events = CRM_Event_PseudoConstant::event( null, null,
                                                       "is_template IS NULL OR is_template = 0" );
            if ( empty($events) ) {
                return false;
            }
            foreach ( $events as $key => $dnt) {
                $this->_params['id_value'][] = $key;
            }
        }
        $this->_rowsFound = count($this->_params['id_value']);

        //set pager and limit if output mode is html
        if ( $this->_outputMode == 'html' ) {
            $this->limit( );
            $this->setPager( );
            
            $showEvents = array( );
            $count      = 0;
            $numRows    = $this->_limit; 
            
            while ( $count <  self::ROW_COUNT_LIMIT ) {
                if ( !isset( $this->_params['id_value'][$numRows] ) ) {
                    break;
                }
                
                $showEvents[] = $this->_params['id_value'][$numRows];
                $count++;
                $numRows++;
            }
            
            $this->buildEventReport( $showEvents );
        } else {
            $this->buildEventReport( $this->_params['id_value'] );
        }    
        
        parent::endPostProcess( );
    }   
}