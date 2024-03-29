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
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Campaign_Selector_Search extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
{
    /**
     * This defines two actions- View and Edit.
     *
     * @var array
     * @static
     */
    static $_links = null;
    
    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;
    
    /**
     * Properties of contact we're interested in displaying
     * @var array
     * @static
     */
    static $_properties = array( 'contact_id', 
                                 'sort_name', 
                                 'street_unit',  
                                 'street_name',
                                 'street_number',
                                 'street_address',
                                 'city',
                                 'postal_code',
                                 'state_province',
                                 'country',
                                 'email',
                                 'phone',
                                 'campaign_id',
                                 'survey_activity_id',
                                 'survey_activity_target_id',
                                 'survey_activity_target_contact_id',
                                 );
    
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
     * queryParams is the array returned by exportValues called on
     * the HTML_QuickForm_Controller for that page.
     *
     * @var array
     * @access protected
     */
    public $_queryParams;
    
    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_action;
    
    /** 
     * The additional clause that we restrict the search with 
     * 
     * @var string 
     */ 
    protected $_surveyClause = null;
    
    /** 
     * The query object
     * 
     * @var string 
     */ 
    protected $_query;
    
    /**
     * Class constructor
     *
     * @param array    $queryParams array of parameters for query
     * @param int      $action - action of search basic or advanced.
     * @param string   $surveyClause if the caller wants to further restrict the search.
     * @param boolean  $single are we dealing only with one contact?
     * @param int      $limit  how many voters do we want returned
     *
     * @return CRM_Contact_Selector
     * @access public
     */
    function __construct( &$queryParams,
                          $action = CRM_Core_Action::NONE,
                          $surveyClause = null,
                          $single = false,
                          $limit = null,
                          $context = 'search' ) 
    {
        // submitted form values
        $this->_queryParams =& $queryParams;
        
        $this->_single  = $single;
        $this->_limit   = $limit;
        $this->_context = $context;
        
        $this->_campaignClause = $surveyClause;
        $this->_campaignFromClause  = CRM_Utils_Array::value( 'fromClause',  $surveyClause );
        $this->_campaignWhereClause = CRM_Utils_Array::value( 'whereClause', $surveyClause );
        
        // type of selector
        $this->_action = $action;
        
        $this->_query = new CRM_Contact_BAO_Query( $this->_queryParams, 
                                                   null, null, false, false,
                                                   CRM_Contact_BAO_Query::MODE_CAMPAIGN,
                                                   true );
    }//end of constructor
    
    /**
     * This method returns the links that are given for each search row.
     * currently the links added for each row are 
     * 
     * - View
     * - Edit
     *
     * @return array
     * @access public
     *
     */
    static function &links( )
    {
        return self::$_links = array( );
    }
    
    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams( $action, &$params ) 
    {
        $params['csvString']    = null;
        $params['status']       = ts('Respondents') . ' %%StatusMessage%%';
        $params['rowCount']     = ( $this->_limit ) ? $this->_limit:CRM_Utils_Pager::ROWCOUNT;
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }
    
    
    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount( $action )
    {
        return $this->_query->searchQuery( 0, 0, null,
                                           true, false, 
                                           false, false, false, 
                                           $this->_campaignWhereClause, 
                                           null,
                                           $this->_campaignFromClause );
    }
    
    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $output   what should the result set include (web/email/csv)
     *
     * @return int   the total number of rows for this action
     */
    function &getRows( $action, $offset, $rowCount, $sort, $output = null ) 
    {
        $result = $this->_query->searchQuery( $offset, $rowCount, $sort,
                                              false, false, 
                                              false, false, 
                                              false, $this->_campaignWhereClause, 
                                              null,
                                              $this->_campaignFromClause );
        
        
        // process the result of the query
        $rows = array( );
        
        While ( $result->fetch( ) ) {
            $row = array( );
            // the columns we are interested in
            foreach (self::$_properties as $property) {
                if ( property_exists( $result, $property ) ) {
                    $row[$property] = $result->$property;   
                }      
            }
            $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->contact_id;
            $row['contact_type'] = CRM_Contact_BAO_Contact_Utils::getImage( $result->contact_type, false,  $result->contact_id );
            
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    /**
     * @return array   $qill which contains an array of strings
     * @access public
     **/
    public function getQILL( )
    {
        return $this->_query->qill( );
    }
    
    /** 
     * returns the column headers as an array of tuples: 
     * (name, sortName (key to the sort array)) 
     * 
     * @param string $action the action being performed 
     * @param enum   $output what should the result set include (web/email/csv) 
     * 
     * @return array the column headers that need to be displayed 
     * @access public 
     */ 
    public function &getColumnHeaders( $action = null, $output = null ) 
    {
        self::$_columnHeaders = array( );
        
        if ( ! $this->_single ) {
            $contactDetails = array(
                                    array( 'name'      => ts('Contact Name'), 
                                           'sort'      => 'sort_name', 
                                           'direction' => CRM_Utils_Sort::ASCENDING ),
                                    array( 'name' => ts('Street Number'),
                                           'sort' => 'street_number'),
                                    array( 'name' => ts('Street Name'),
                                           'sort' => 'street_name'),
                                    array( 'name' => ts('Street Address') ),
                                    array( 'name' => ts('City'),
                                           'sort' => 'city'),  
                                    array( 'name' => ts('Postal Code'),
                                           'sort' => 'postal_code' ),    
                                    array( 'name' => ts('State'),
                                           'sort' => 'state_province_name'),       
                                    array( 'name' => ts('Country') ),    
                                    array( 'name' => ts('Email') ),
                                    array( 'name' => ts('Phone') )   
                                    );
            self::$_columnHeaders = array_merge( $contactDetails, self::$_columnHeaders );
        }
        
        return self::$_columnHeaders;
    }
    
    function &getQuery( ) {
        return $this->_query;
    }
    
    /** 
     * name of export file. 
     * 
     * @param string $output type of output 
     * @return string name of the file 
     */ 
    function getExportFileName( $output = 'csv') { 
        return ts('CiviCRM Respondent Search'); 
    }
    
}//end of class


