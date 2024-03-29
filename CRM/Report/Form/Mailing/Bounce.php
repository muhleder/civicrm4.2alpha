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


class CRM_Report_Form_Mailing_Bounce extends CRM_Report_Form {

    protected $_summary      = null;

    protected $_emailField   = false;
    
    protected $_phoneField   = false;
    
	// just a toggle we use to build the from
	protected $_mailingidField = false;
	
    protected $_customGroupExtends = array( 'Contact', 'Individual', 'Household', 'Organization' );
    
    protected $_charts  = array( ''         => 'Tabular',
                                 'barChart' => 'Bar Chart',
                                 'pieChart' => 'Pie Chart'
                                 );

    function __construct( ) {
        $this->_columns = array(); 
		
		$this->_columns['civicrm_contact'] = array(
			'dao' => 'CRM_Contact_DAO_Contact',
			'fields' => array(
				'id' => array( 
					'title' => ts('Contact ID'),
					'required'  => true, 
				), 						
                'sort_name' => 
                array(
                      'title' => ts('Contact Name'),
                      'required' => true,
                      ),
			),
			'filters' => array( 
				'sort_name' => array( 
					'title' => ts( 'Contact Name' )
				),
				'source'  => array( 
					'title'=> ts( 'Contact Source' ),
					'type'=> CRM_Utils_Type::T_STRING ),
					'id'=> array( 
						'title'=> ts( 'Contact ID' ),
						'no_display' => true ,
				), 
			),
            'order_bys'  =>
            array( 'sort_name' =>
                   array( 'title' => ts( 'Contact Name'), 'default' => true, 'default_order' => 'ASC') ),
                                     
			'grouping'  => 'contact-fields',		
		);
		
		$this->_columns['civicrm_mailing'] = array(
			'dao' => 'CRM_Mailing_DAO_Mailing',
			'fields' => array( 'mailing_name' => array(
                                                       'name' => 'name',
                                                       'title' => ts('Mailing'),
                                                       'default' => true
                                                       ),
                               'mailing_name_alias' => array(
                                                       'name' => 'name',
                                                       'required' => true,
                                                       'no_display' => true ),
                               
			),
			'filters' => array(
				'mailing_id' => array(
					'name' => 'id',
					'title' => ts('Mailing'),
					'operatorType' => CRM_Report_Form::OP_MULTISELECT,
					'type'=> CRM_Utils_Type::T_INT,
					'options' => CRM_Mailing_BAO_Mailing::getMailingsList(),
					'operator' => 'like',
				),                              
			),
            'order_bys'  =>
            array( 'mailing_name' =>
                   array( 'name' => 'name',
                          'title' => ts( 'Mailing' ) ) ),
            'grouping' => 'mailing-fields' 
		);
		
		$this->_columns['civicrm_mailing_event_bounce'] = array(
			'dao' => 'CRM_Mailing_DAO_Mailing',
			'fields' => array(
				'bounce_reason' => array(
					'title' => ts('Bounce Reason'),
				),
			),
            'order_bys'  =>
            array( 'bounce_reason' =>
                   array( 'title' => ts( 'Bounce Reason') ) ),
            'grouping' => 'mailing-fields' 
		);
		
		$this->_columns['civicrm_mailing_bounce_type'] = array(
			'dao' => 'CRM_Mailing_DAO_BounceType',
			'fields' => array(
				'bounce_name' => array(
					'name' => 'name',
					'title' => ts('Bounce Type'),
				),
			),
			'filters' => array(
				'bounce_type_name' => array(
					'name' => 'name',
					'title' => ts('Bounce Type'),
					'operatorType' => CRM_Report_Form::OP_SELECT,
					'type'=> CRM_Utils_Type::T_STRING,
					'options' => self::bounce_type(),
					'operator' => 'like',							
				),
			),
            'order_bys'  =>
            array( 'bounce_name' =>
                   array( 'name' => 'name',
                          'title' => ts( 'Bounce Type') ) ),
            'grouping' => 'mailing-fields' 
		);
							  
		$this->_columns['civicrm_email']  = array( 
			'dao'=> 'CRM_Core_DAO_Email',
			'fields'=> array( 
				'email' => array( 
					 'title' => ts( 'Email' ),
					 'no_repeat'  => true,
					 'required' => true,
				),
			),
            'order_bys'  =>
            array( 'email' =>
                   array( 'title' => ts( 'Email'), 'default_order' => 'ASC') ),

			'grouping'  => 'contact-fields', 
		);
		
        $this->_columns['civicrm_phone'] = array( 
                                                 'dao' => 'CRM_Core_DAO_Phone',
                                                 'fields' => array( 'phone' => null),
                                                 'grouping'  => 'contact-fields',
                                                 );

		$this->_columns['civicrm_group'] = array( 
			'dao'    => 'CRM_Contact_DAO_Group',
			'alias'  => 'cgroup',
			'filters' => array( 
				'gid' => array( 
					'name'    => 'group_id',
					'title'   => ts( 'Group' ),
					'operatorType' => CRM_Report_Form::OP_MULTISELECT,
					'group'   => true,
					'options' => CRM_Core_PseudoConstant::group( ), 
				), 
			), 
		);

        $this->_tagFilter = true;
        parent::__construct( );
    }
    
    function preProcess( ) {
        $this->assign( 'chartSupported', true );
        parent::preProcess( );
    }
    
    function select( ) {
        $select = array( );
        $this->_columnHeaders = array();

        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        if ( $tableName == 'civicrm_email' ) {
                            $this->_emailField = true;
                        } else if ( $tableName == 'civicrm_phone' ) {
                            $this->_phoneField = true;
                        }
					
                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = CRM_Utils_Array::value( 'no_display', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value( 'title', $field );
                    }
                }
            }
        }

        
        if ( CRM_Utils_Array::value('charts', $this->_params) ) {
            $select[] = "COUNT({$this->_aliases['civicrm_mailing_event_bounce']}.id) as civicrm_mailing_bounce_count";
            $this->_columnHeaders["civicrm_mailing_bounce_count"]['title'] = ts('Bounce Count'); 
        }

        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }

    static function formRule( $fields, $files, $self ) {  
        $errors = $grouping = array( );
        return $errors;
    }

    function from( ) {
        $this->_from = "
        FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}";
            // LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} 
                   // ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND 
                      // {$this->_aliases['civicrm_address']}.is_primary = 1 ) ";
        
        $this->_from .= "
				INNER JOIN civicrm_mailing_event_queue
					ON civicrm_mailing_event_queue.contact_id = {$this->_aliases['civicrm_contact']}.id
				INNER JOIN civicrm_email {$this->_aliases['civicrm_email']}
					ON civicrm_mailing_event_queue.email_id = {$this->_aliases['civicrm_email']}.id
				INNER JOIN civicrm_mailing_event_bounce {$this->_aliases['civicrm_mailing_event_bounce']}
					ON {$this->_aliases['civicrm_mailing_event_bounce']}.event_queue_id = civicrm_mailing_event_queue.id
				LEFT JOIN civicrm_mailing_bounce_type {$this->_aliases['civicrm_mailing_bounce_type']}
					ON {$this->_aliases['civicrm_mailing_event_bounce']}.bounce_type_id = {$this->_aliases['civicrm_mailing_bounce_type']}.id
				INNER JOIN civicrm_mailing_job
					ON civicrm_mailing_event_queue.job_id = civicrm_mailing_job.id
				INNER JOIN civicrm_mailing {$this->_aliases['civicrm_mailing']}
					ON civicrm_mailing_job.mailing_id = {$this->_aliases['civicrm_mailing']}.id
			";
	   	
        if ( $this->_phoneField ) {
            $this->_from .= "
            LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']} 
                   ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND 
                      {$this->_aliases['civicrm_phone']}.is_primary = 1 ";
        }
    }
    
    function where( ) {
        $clauses = array( );
        //to avoid the sms listings
        $this->_where = "WHERE {$this->_aliases['civicrm_mailing']}.sms_provider_id IS NULL";
    }
    
    function groupBy( ) {
        if ( CRM_Utils_Array::value('charts', $this->_params) ) {
            $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_mailing']}.id";
        } else {
            $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_mailing_event_bounce']}.id";
        }
    }

    function postProcess( ) {
        $this->beginPostProcess( );

        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );

        $sql  = $this->buildQuery( true );
		             
        $rows = $graphRows = array();
        $this->buildRows ( $sql, $rows );
        
        $this->formatDisplay( $rows );
        $this->doTemplateAssignment( $rows );
        $this->endPostProcess( $rows );	
    }

    function buildChart( &$rows ) {
        if ( empty($rows) ) {
            return;
        }

        $chartInfo  = array( 'legend'      => ts('Mail Bounce Report'),
                             'xname'       => ts('Mailing'),
                             'yname'       => ts('Bounce'),
                             'xLabelAngle' => 20,
                             'tip'         => ts('Mail Bounce: %1', array(1 => '#val#')),
                             );
        foreach( $rows as $row ) {
            $chartInfo['values'][$row['civicrm_mailing_mailing_name_alias']] = $row['civicrm_mailing_bounce_count']; 
        }
        
        // build the chart.
        CRM_Utils_OpenFlashChart::buildChart( $chartInfo, $this->_params['charts'] );
        $this->assign( 'chartType', $this->_params['charts'] ); 
    }

	function bounce_type() {
		
		$data = array('' => '--Please Select--');
		
		$bounce_type = new CRM_Mailing_DAO_BounceType();
		$query = "SELECT name FROM civicrm_mailing_bounce_type";
		$bounce_type->query($query);
		
		while($bounce_type->fetch()) {
			$data[$bounce_type->name] = $bounce_type->name;
		}
		
		return $data;
	}

    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        foreach ( $rows as $rowNum => $row ) {
            // make count columns point to detail report
 	 	 	// convert display name to links
 	 	 	if ( array_key_exists('civicrm_contact_sort_name', $row) &&
                 array_key_exists('civicrm_contact_id', $row) ) {
                $url = CRM_Utils_System::url( 'civicrm/contact/view',
                                              'reset=1&cid=' . $row['civicrm_contact_id'] );
                $rows[$rowNum]['civicrm_contact_sort_name_link' ] = $url;
                $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact details for this contact.");
                $entryFound = true;
            }
            
            // skip looking further in rows, if first row itself doesn't
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
    }
}
