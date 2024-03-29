<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */


class CRM_Report_Form_Contribute_PCP extends CRM_Report_Form {

    function __construct( ) {
        $this->_columns = 
            array( 
                  'civicrm_contact' =>
                  array( 'dao'      => 'CRM_Contact_DAO_Contact',
                         'fields'   =>
                         array( 'sort_name' => 
                                array( 'title'   => ts( 'Supporter' ), 
                                       'required'=> true ,
                                       'default' => true ), 
                                'id' => 
                                array( 'required'   => true ,
                                       'no_display' => true ) ),
                         'filters' =>             
                         array( 'sort_name' => 
                                array( 'title'      => ts( 'Supporter Name' ),
                                       'type'       => CRM_Utils_Type::T_STRING,
                                       'operator'   => 'like' ),
                                'id'        => 
                                array( 'title'      => ts( 'Contact ID' ),
                                       'no_display' => true ), ),
                         'grouping' => 'pcp-fields',
                         ),
                  'civicrm_contribution_page' =>
                  array( 'dao'          => 'CRM_Contribute_DAO_ContributionPage',
                         'fields'       =>
                         array( 'page_title' => 
                                array( 'title'   => ts( 'Contribution Page Title' ), 
                                       'name'    => 'title', 
                                       'default' => true ), ),
                         'filters'      => 
                         array( 'page_title'  => 
                                array( 'title' => ts( 'Contribution Page Title' ),
                                       'name'  => 'title',
                                       'type'  => CRM_Utils_Type::T_STRING ) ),
                         'grouping'     => 'pcp-fields',
                         ),
                  
                  'civicrm_pcp' =>
                  array( 'dao'    => 'CRM_PCP_DAO_PCP',
                         'fields' =>
                         array( 'title'        => 
                                array( 'title'   => ts( 'Pesonal Campaign Title' ),  
                                       'default' => true ), 
                                'goal_amount'  => 
                                array( 'title'   => ts( 'Goal Amount' ), 
                                       'type'    => CRM_Utils_Type::T_MONEY,
                                       'default' => true ), ),
                         'filters' =>
                          array( 'title' =>
                                 array( 'title' => ts( 'Personal Campaign Title' ),
                                        'type'  => CRM_Utils_Type::T_STRING ), ),
                         'grouping'      => 'pcp-fields',
                         ),
                  
                  'civicrm_contribution_soft' =>
                   array( 'dao'    => 'CRM_Contribute_DAO_ContributionSoft',
                          'fields' =>
                          array( 'amount_1' => 
                                 array( 'title'      => ts( 'Committed Amount' ),
                                        'name'       => 'amount',
                                        'type'       => CRM_Utils_Type::T_MONEY,
                                        'default'    => true,
                                        'statistics' => 
                                        array( 'sum'  => ts( 'Committed Amount' ), ), ),
                                 'amount_2' =>
                                 array( 'title'      => ts( 'Amount Received' ),
                                        'name'       => 'amount',
                                        'type'       => CRM_Utils_Type::T_MONEY,
                                        'default'    => true,
                                        // nice trick with dbAlias
                                        'dbAlias'    => 'SUM(IF( contribution_civireport.contribution_status_id > 1, 0, contribution_soft_civireport.amount))', ),
                                 'soft_id'  => 
                                 array( 'title'      => ts( 'Number of Donors' ),
                                        'name'       => 'id',
                                        'default'    => true,
                                        'statistics' => 
                                        array( 'count'  => ts( 'Number of Donors' ), ), ), ),
                          'filters' =>
                          array( 'amount_2' =>
                                 array( 'title' => ts( 'Amount Received' ),
                                        'type'  => CRM_Utils_Type::T_MONEY,
                                        'dbAlias' => 'SUM(IF( contribution_civireport.contribution_status_id > 1, 0, contribution_soft_civireport.amount))', ), ),
                          'grouping'  => 'pcp-fields',
                          ),
                  
                  'civicrm_contribution' =>
                  array( 'dao'    => 'CRM_Contribute_DAO_Contribution',
                         'fields' =>
                         array(
                               'contribution_id' => 
                               array( 'name'       => 'id',
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                               'receive_date' => 
                               array( 'title'      => ts( 'Most Recent Donation' ), 
                                      'default'    => true,
                                      'statistics' => 
                                      array( 'max'  => ts( 'Most Recent Donation' ), ), ),
                               ),
                         'grouping' => 'pcp-fields',
                         ),
                   );
        
        parent::__construct( );
    }
    
    function from( ) {
        $this->_from = "
FROM civicrm_pcp {$this->_aliases['civicrm_pcp']}

LEFT JOIN civicrm_contribution_soft {$this->_aliases['civicrm_contribution_soft']} 
          ON {$this->_aliases['civicrm_pcp']}.id = 
             {$this->_aliases['civicrm_contribution_soft']}.pcp_id

LEFT JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']} 
          ON {$this->_aliases['civicrm_contribution_soft']}.contribution_id = 
             {$this->_aliases['civicrm_contribution']}.id

LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} 
          ON {$this->_aliases['civicrm_pcp']}.contact_id = 
             {$this->_aliases['civicrm_contact']}.id 

LEFT JOIN civicrm_contribution_page {$this->_aliases['civicrm_contribution_page']}
          ON {$this->_aliases['civicrm_pcp']}.page_id = 
             {$this->_aliases['civicrm_contribution_page']}.id";
    }
    
    function groupBy( ) {
        $this->_groupBy = "GROUP BY {$this->_aliases['civicrm_pcp']}.id";
    }
    
    function orderBy( ) {
        $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_contact']}.sort_name ";
    }
    
    function where( ) {
        $whereClauses = $havingClauses = array( );
        
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    
                    if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        $clause   = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        
                        if ( $op ) {
                            $clause = $this->whereClause( $field,
                                                          $op,
                                                          CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                          CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                          CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }
                    
                    if ( ! empty( $clause ) ) {
                        if ( $tableName == 'civicrm_contribution_soft' &&
                             $fieldName == 'amount_2' ) {
                            $havingClauses[] =$clause;
                        } else {
                            $whereClauses[] = $clause;
                        }
                    }
                }
            }
        }
        if ( empty( $whereClauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
           
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $whereClauses );
        }
        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        }   
        $this->_having = "";
        if ( !empty( $havingClauses ) ) {
            // use this clause to construct group by clause.
            $this->_having = "HAVING " . implode( ' AND ', $havingClauses );
        }
    }
    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        $checkList  =  array();
        foreach ( $rows as $rowNum => $row ) {
            if ( !empty($this->_noRepeats) && $this->_outputMode != 'csv' ) {
                // not repeat contact sort names if it matches with the one 
                // in previous row
                $repeatFound = false;
               
                foreach ( $row as $colName => $colVal ) {
                    if ( CRM_Utils_Array::value( $colName, $checkList ) && 
                         is_array( $checkList[$colName] ) && 
                         in_array( $colVal, $checkList[$colName] ) ) {
                        $rows[$rowNum][$colName] = "";
                        $repeatFound = true;
                    }
                    if ( in_array( $colName, $this->_noRepeats ) ) {
                        $checkList[$colName][] = $colVal;
                    }
                }
            }
            
            if ( array_key_exists( 'civicrm_contact_sort_name', $row ) && 
                 $rows[$rowNum]['civicrm_contact_sort_name'] && 
                 array_key_exists( 'civicrm_contact_id', $row ) ) {
                $url = CRM_Utils_System::url( "civicrm/contact/view"  , 
                                              'reset=1&cid=' . $row['civicrm_contact_id'],
                                              $this->_absoluteUrl );
                $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
                $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts( "View Contact Summary for this Contact." );
                $entryFound = true;
            }
            
            if ( !$entryFound ) {
                break;
            }
        }
    }
}
