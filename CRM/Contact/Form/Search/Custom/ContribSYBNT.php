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
    
    
class CRM_Contact_Form_Search_Custom_ContribSYBNT
implements CRM_Contact_Form_Search_Interface {
        
    protected $_formValues;
        
    function __construct( &$formValues ) {     
        $this->_formValues = $formValues;
            
        $this->_columns = array( ts('Contact Id')   => 'contact_id'  ,
                                 ts('Name'      )   => 'display_name',
                                 ts('Donation Count') => 'donation_count',
                                 ts('Donation Amount') => 'donation_amount' );

        $this->_amounts = array( 'min_amount_1' => ts( 'Min Amount One' ),
                                 'max_amount_1' => ts( 'Max Amount One' ),
                                 'min_amount_2' => ts( 'Min Amount Two' ),
                                 'max_amount_2' => ts( 'Max Amount Two' ),
                                 'exclude_min_amount' => ts( 'Exclusion Min Amount' ),
                                 'exclude_max_amount' => ts( 'Exclusion Max Amount' ),
                                 );

        $this->_dates = array( 'start_date_1' => ts( 'Start Date One' ),
                               'end_date_1'   => ts( 'End Date One' ),
                               'start_date_2' => ts( 'Start Date Two' ),
                               'end_date_2'   => ts( 'End Date Two' ),
                               'exclude_start_date' => ts( 'Exclusion Start Date' ),
                               'exclude_end_date'   => ts( 'Exclusion End Date' ),
                               );

        $this->_checkboxes = array( 'is_first_amount' => ts( 'First Donation?' ) );

        foreach ( $this->_amounts as $name => $title ) {
            $this->{$name} = CRM_Utils_Array::value( $name, $this->_formValues );
        }

        foreach ( $this->_checkboxes as $name => $title ) {
            $this->{$name} = CRM_Utils_Array::value( $name, $this->_formValues, false );
        }

        foreach ( $this->_dates as $name => $title ) {
            if ( CRM_Utils_Array::value( $name, $this->_formValues ) ) {
                $this->{$name} = CRM_Utils_Date::processDate( $this->_formValues[$name] );
            } 
        }

    }
        
    function buildForm( &$form ) {
        
        foreach ( $this->_amounts as $name => $title ) {
            $form->add( 'text',
                        $name,
                        $title );
        }
            
        foreach ( $this->_dates as $name => $title ) {
            $form->addDate( $name, $title, false, array('formatType' => 'custom') );
        }

        foreach ( $this->_checkboxes as $name => $title ) {
            $form->add( 'checkbox',
                        $name,
                        $title );
        }
        
        $this->setTitle('Contributions made in Year X and not Year Y');
        // @TODO: Decide on better names for "Exclusion"
        // @TODO: Add rule to ensure that exclusion dates are not in the inclusion range
    }
        
    function count( ) {
        $sql = $this->all( );
            
        $dao = CRM_Core_DAO::executeQuery( $sql );
        return $dao->N;
    }
        
    function contactIDs( $offset = 0, $rowcount = 0, $sort = null) { 
        return $this->all( $offset, $rowcount, $sort, false, true );
    }
        
    function all( $offset = 0, $rowcount = 0, $sort = null,
                  $includeContactIDs = false ) {

        $where = $this->where( );
        if ( ! empty( $where ) ) {
            $where = " AND $where";
        }
            
        $having = $this->having( );
        if ( $having ) {
            $having = " HAVING $having ";
        }

        $from = $this->from( );

        $select = $this->select( );

        $sql = "
SELECT     DISTINCT contact.id as contact_id,
           contact.display_name as display_name,
           $select
FROM       civicrm_contact AS contact
LEFT JOIN  civicrm_contribution contrib_1 ON contrib_1.contact_id = contact.id
           $from
WHERE      contrib_1.contact_id = contact.id
AND        contrib_1.is_test = 0 
           $where
GROUP BY   contact.id
           $having
ORDER BY   donation_amount desc
";
 
        // CRM_Core_Error::debug('sql',$sql); exit();
        return $sql;
    }

    function select( ) {
        if ( $this->start_date_2 || $this->end_date_2 ) {
            return "
sum(contrib_1.total_amount) + sum(contrib_2.total_amount) AS donation_amount,
count(contrib_1.id) + count(contrib_1.id) AS donation_count
";
        } else {
            return "
sum(contrib_1.total_amount) AS donation_amount,
count(contrib_1.id) AS donation_count
";
        }
    }

    function from( ) {
        $from = null;
        if ( $this->start_date_2 || $this->end_date_2 ) {
            $from .= " LEFT JOIN civicrm_contribution contrib_2 ON contrib_2.contact_id = contact.id ";
        }

        if ( $this->exclude_start_date ||
             $this->exclude_end_date ||
             $this->is_first_amount ) {
            $from .= " LEFT JOIN XG_CustomSearch_SYBNT xg ON xg.contact_id = contact.id ";
        }

        return $from;

    }
        
    function where( $includeContactIDs = false ) {
        $clauses = array( );
            
        if ( $this->start_date_1 ) {
            $clauses[] = "contrib_1.receive_date >= {$this->start_date_1}";
        }

        if ( $this->end_date_1 ) {
            $clauses[] = "contrib_1.receive_date <= {$this->end_date_1}";
        }

        if ( $this->start_date_2       ||
             $this->end_date_2 ) {
            $clauses[] = "contrib_2.is_test = 0";
            
            if ( $this->start_date_2 ) {
                $clauses[] = "contrib_2.receive_date >= {$this->start_date_2}";
            }
            
            if ( $this->end_date_2 ) {
                $clauses[] = "contrib_2.receive_date <= {$this->end_date_2}";
            }
        }
            
        if ( $this->exclude_start_date ||
             $this->exclude_end_date   ||
             $this->is_first_amount ) {

            // first create temp table to store contact ids
            $sql = "DROP TEMPORARY TABLE IF EXISTS XG_CustomSearch_SYBNT";
            CRM_Core_DAO::executeQuery( $sql );

            $sql = "CREATE TEMPORARY TABLE XG_CustomSearch_SYBNT ( contact_id int primary key) ENGINE=HEAP";
            CRM_Core_DAO::executeQuery( $sql );

            $excludeClauses = array( );
            if ( $this->exclude_start_date ) {
                $excludeClauses[] = "c.receive_date >= {$this->exclude_start_date}";
            }
            
            if ( $this->exclude_end_date ) {
                $excludeClauses[] = "c.receive_date <= {$this->exclude_end_date}";
            }
            
            $excludeClause = null;
            if ( $excludeClauses ) {
                $excludeClause  = ' AND ' . implode( ' AND ', $excludeClauses );
            }
            
            $having = array( );
            if ( $this->exclude_min_amount ) {
                $having[] = "sum(c.total_amount) >= {$this->exclude_min_amount}";
            }
            
            if ( $this->exclude_max_amount ) {
                $having[] = "sum(c.total_amount) <= {$this->exclude_max_amount}";
            }
            
            $havingClause = null;
            if ( ! empty( $having ) ) {
                $havingClause = "HAVING " . implode( ' AND ', $having );
            }

            if ( $excludeClause || $havingClause ) {
                // Run subquery
                $query = "
REPLACE   INTO XG_CustomSearch_SYBNT
SELECT   DISTINCT contact_id AS contact_id
FROM     civicrm_contribution c
WHERE    c.is_test = 0
         $excludeClause
GROUP BY c.contact_id
         $havingClause
";
                
                $dao = CRM_Core_DAO::executeQuery( $query );
            }

            // now ensure we dont consider donors that are not first time
            if ( $this->is_first_amount ) {
                $query = "
REPLACE  INTO XG_CustomSearch_SYBNT
SELECT   DISTINCT contact_id AS contact_id
FROM     civicrm_contribution c
WHERE    c.is_test = 0
AND      c.receive_date < {$this->start_date_1}
";
                $dao = CRM_Core_DAO::executeQuery( $query );
            }

            $clauses[] = " xg.contact_id IS NULL ";
        }
            
        return implode( ' AND ', $clauses );
    }
        
    function having( $includeContactIDs = false ) {
        $clauses = array( );
        $min = CRM_Utils_Array::value( 'min_amount', $this->_formValues );
        if ( $min ) {
            $clauses[] = "sum(contrib_1.total_amount) >= $min";
        }
            
        $max = CRM_Utils_Array::value( 'max_amount', $this->_formValues );
        if ( $max ) {
            $clauses[] = "sum(contrib_1.total_amount) <= $max";
        }
            
        return implode( ' AND ', $clauses );
    }
        
    function &columns( ) {
        return $this->_columns;
    }
    
    function templateFile( ) {
        return 'CRM/Contact/Form/Search/Custom/ContribSYBNT.tpl';
    }

    function summary( ) {
        return null;
    }
    
    function setTitle( $title ) {
        if ( $title ) {
            CRM_Utils_System::setTitle( $title );
        } else {
            CRM_Utils_System::setTitle(ts('Search'));
        }
    } 
}
    

