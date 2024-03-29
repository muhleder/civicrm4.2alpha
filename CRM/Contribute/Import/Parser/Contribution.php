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
 * class to parse contribution csv files
 */
class CRM_Contribute_Import_Parser_Contribution extends CRM_Contribute_Import_Parser 
{

    protected $_mapperKeys;

    private $_contactIdIndex;
    private $_totalAmountIndex;
    private $_contributionTypeIndex;

    protected $_mapperSoftCredit;
    //protected $_mapperPhoneType;
    /**
     * Array of succesfully imported contribution id's
     *
     * @array
     */
    protected $_newContributions;

    /**
     * class constructor
     */
    function __construct( &$mapperKeys, $mapperSoftCredit = null, $mapperPhoneType = null ) 
    {
        parent::__construct();
        $this->_mapperKeys =& $mapperKeys;
        $this->_mapperSoftCredit =& $mapperSoftCredit;
    }

    /**
     * the initializer code, called before the processing
     *
     * @return void
     * @access public
     */
    function init( ) 
    {
        $fields = CRM_Contribute_BAO_Contribution::importableFields( $this->_contactType , false );
        
        $fields = array_merge( $fields,
                               array( 'soft_credit' => array( 'title'         => ts('Soft Credit'),
                                                              'softCredit'    => true,
                                                              'headerPattern' => '/Soft Credit/i' ) ) );
        
        // add pledge fields only if its is enabled
        if ( CRM_Core_Permission::access( 'CiviPledge' ) ) {
            $pledgeFields = array( 'pledge_payment' => array( 'title' => ts('Pledge Payment'),
                                                              'headerPattern' => '/Pledge Payment/i' ),
                                   'pledge_id'      => array( 'title' => ts('Pledge ID'),
                                                              'headerPattern' => '/Pledge ID/i')
                                 );
            
            $fields = array_merge( $fields, $pledgeFields );
        }
        foreach ($fields as $name => $field) {
            $field['type']          = CRM_Utils_Array::value( 'type', $field, CRM_Utils_Type::T_INT );
            $field['dataPattern']   = CRM_Utils_Array::value( 'dataPattern', $field, '//' );
            $field['headerPattern'] = CRM_Utils_Array::value( 'headerPattern', $field, '//' );
            $this->addField( $name, $field['title'], $field['type'], $field['headerPattern'], $field['dataPattern'] );
        }

        $this->_newContributions = array();
       
        $this->setActiveFields( $this->_mapperKeys );
        $this->setActiveFieldSoftCredit( $this->_mapperSoftCredit ); 

        // FIXME: we should do this in one place together with Form/MapField.php
        $this->_contactIdIndex        = -1;
        $this->_totalAmountIndex      = -1;
        $this->_contributionTypeIndex = -1;
        
        $index = 0;
        foreach ( $this->_mapperKeys as $key ) {
            switch ($key) {
            case 'contribution_contact_id':
                $this->_contactIdIndex        = $index;
                break;
            case 'total_amount':
                $this->_totalAmountIndex      = $index;
                break;
            case 'contribution_type':
                $this->_contributionTypeIndex = $index;
                break;
            }
            $index++;
        }
    }

    /**
     * handle the values in mapField mode
     *
     * @param array $values the array of values belonging to this line
     *
     * @return boolean
     * @access public
     */
    function mapField( &$values ) 
    {
        return CRM_Contribute_Import_Parser::VALID;
    }


    /**
     * handle the values in preview mode
     *
     * @param array $values the array of values belonging to this line
     *
     * @return boolean      the result of this processing
     * @access public
     */
    function preview( &$values ) 
    {
        return $this->summary($values);
    }

    /**
     * handle the values in summary mode
     *
     * @param array $values the array of values belonging to this line
     *
     * @return boolean      the result of this processing
     * @access public
     */
    function summary( &$values ) 
    {
        $erroneousField = null;
        $response = $this->setActiveFieldValues( $values, $erroneousField );
        
        $params =& $this->getActiveFieldParams( );
        $errorMessage = null;
        
        //for date-Formats
        $session = CRM_Core_Session::singleton();
        $dateType = $session->get("dateTypes");
        foreach ($params as $key => $val) {
            if( $val ) {
                switch( $key ) {
                case  'receive_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Receive Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Receive Date', $errorMessage);
                    }
                    break;
                case  'cancel_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Cancel Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Cancel Date', $errorMessage);
                    }
                    break;
                case  'receipt_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Receipt date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Receipt date', $errorMessage);
                    }
                    break;
                case  'thankyou_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Thankyou Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Thankyou Date', $errorMessage);
                    }
                    break;
                    
                }
            }
        }
        //date-Format part ends

        $params['contact_type'] =  'Contribution';
        
        //checking error in custom data
        CRM_Import_Parser_Contact::isErrorInCustomData($params, $errorMessage);

        if ( $errorMessage ) {
            $tempMsg = "Invalid value for field(s) : $errorMessage";
            array_unshift($values, $tempMsg);
            $errorMessage = null;
            return CRM_Contribute_Import_Parser::ERROR;
        }

        return CRM_Contribute_Import_Parser::VALID;
    }

    /**
     * handle the values in import mode
     *
     * @param int $onDuplicate the code for what action to take on duplicates
     * @param array $values the array of values belonging to this line
     *
     * @return boolean      the result of this processing
     * @access public
     */
    function import( $onDuplicate, &$values ) 
    {
        // first make sure this is a valid line
        $response = $this->summary( $values );
        if ( $response != CRM_Contribute_Import_Parser::VALID ) {
            return $response;
        }

        $params =& $this->getActiveFieldParams( );            
                
        $formatted = array( 'version' => 3 );

        // don't add to recent items, CRM-4399
        $formatted['skipRecentView'] = true;
        
        //for date-Formats
        $session = CRM_Core_Session::singleton();
        $dateType = $session->get("dateTypes");
     
        $customFields = CRM_Core_BAO_CustomField::getFields( CRM_Utils_Array::value( 'contact_type',$params ) );
        
        foreach ($params as $key => $val) {
            if( $val ) {
                switch( $key ) {
                case  'receive_date': 
                    CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key );
                    break;
                case  'cancel_date': 
                    CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key );
                    break;
                case  'receipt_date': 
                    CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key );
                    break;
                case  'thankyou_date': 
                    CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key );
                    break;
                case  'pledge_payment':
                    $params[$key] = CRM_Utils_String::strtobool( $val );
                    break;
                }
                if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID( $key ) ) {
                    if ( $customFields[$customFieldID]['data_type'] == 'Date' ) {
                        CRM_Import_Parser_Contact::formatCustomDate( $params, $formatted, $dateType, $key );
                        unset( $params[$key] );
                    } else if ( $customFields[$customFieldID]['data_type'] == 'Boolean' ) {
                        $params[$key] = CRM_Utils_String::strtoboolstr( $val );
                    }
                }
            }
        }
        //date-Format part ends

        static $indieFields = null;
        if ($indieFields == null) {
            $tempIndieFields = CRM_Contribute_DAO_Contribution::import();
            $indieFields = $tempIndieFields;
        }
        
        $paramValues = array();            
        foreach ($params as $key => $field) {
            if ($field == null || $field === '') {
                continue;
            }
            $paramValues[$key] = $field;
        }
        
        //import contribution record according to select contact type
        if ( $onDuplicate == CRM_Contribute_Import_Parser::DUPLICATE_SKIP && 
             ( CRM_Utils_Array::value( 'contribution_contact_id', $paramValues ) || 
               CRM_Utils_Array::value( 'external_identifier', $paramValues ) ) ) {
            $paramValues['contact_type'] = $this->_contactType;
        } else if( $onDuplicate == CRM_Contribute_Import_Parser::DUPLICATE_UPDATE && 
                   ( $paramValues['contribution_id'] || $values['trxn_id'] || $paramValues['invoice_id'] ) ) {
            $paramValues['contact_type'] = $this->_contactType;
        } else if ( !empty( $params['soft_credit'] ) ) {
            $paramValues['contact_type'] = $this->_contactType;
        } else if ( CRM_Utils_Array::value( 'pledge_payment', $paramValues ) ) {
            $paramValues['contact_type'] = $this->_contactType;
        }
        
        //need to pass $onDuplicate to check import mode.
        if ( CRM_Utils_Array::value( 'pledge_payment', $paramValues ) ) {
            $paramValues['onDuplicate'] = $onDuplicate;
        }
        require_once 'api/v3/DeprecatedUtils.php';
        $formatError = _civicrm_api3_deprecated_formatted_param( $paramValues, $formatted, true);
        
        if ( $formatError ) {
            array_unshift($values, $formatError['error_message']);
            if ( CRM_Utils_Array::value( 'error_data', $formatError ) == 'soft_credit' ) {
                return CRM_Contribute_Import_Parser::SOFT_CREDIT_ERROR;
            } else if ( CRM_Utils_Array::value( 'error_data', $formatError ) == 'pledge_payment' ) {
                return CRM_Contribute_Import_Parser::PLEDGE_PAYMENT_ERROR;
            }
            return CRM_Contribute_Import_Parser::ERROR;
        }

        if ( $onDuplicate != CRM_Contribute_Import_Parser::DUPLICATE_UPDATE ) {
            $formatted['custom'] = CRM_Core_BAO_CustomField::postProcess( $formatted,
                                                                          CRM_Core_DAO::$_nullObject,
                                                                          null,
                                                                          'Contribution' );
        } else {
            //fix for CRM-2219 - Update Contribution
            // onDuplicate == CRM_Contribute_Import_Parser::DUPLICATE_UPDATE
            if ( $paramValues['invoice_id'] || $paramValues['trxn_id'] || $paramValues['contribution_id'] ) {
                $dupeIds = array(
                                 'id'         => CRM_Utils_Array::value('contribution_id', $paramValues ),
                                 'trxn_id'    => CRM_Utils_Array::value('trxn_id',         $paramValues ),
                                 'invoice_id' => CRM_Utils_Array::value('invoice_id',      $paramValues )
                                 );
                
                $ids['contribution'] = CRM_Contribute_BAO_Contribution::checkDuplicateIds( $dupeIds );                 
                if ( $ids['contribution'] ) {     
                    $formatted['id'] = $ids['contribution'];
                    $formatted['custom'] = CRM_Core_BAO_CustomField::postProcess( $formatted,
                                                                                  CRM_Core_DAO::$_nullObject,
                                                                                  $formatted['id'],
                                                                                  'Contribution' );
                    //process note
                    if ( $paramValues['note'] ) {
                        $noteID = array();
                        $contactID = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_Contribution', $ids['contribution'], 'contact_id' );                       
                        $daoNote = new CRM_Core_BAO_Note();
                        $daoNote->entity_table = 'civicrm_contribution';
                        $daoNote->entity_id    = $ids['contribution'];
                        if ( $daoNote->find(true) ) {
                            $noteID['id'] = $daoNote->id;                            
                        }
                                               
                        $noteParams = array(
                                            'entity_table' => 'civicrm_contribution', 
                                            'note'         => $paramValues['note'], 
                                            'entity_id'    => $ids['contribution'],
                                            'contact_id'   => $contactID
                                            );
                        CRM_Core_BAO_Note::add($noteParams, $noteID);
                        unset($formatted['note']);
                    }
                    
                    //need to check existing soft credit contribution, CRM-3968
                    if ( CRM_Utils_Array::value( 'soft_credit_to', $formatted ) ) {
                        $dupeSoftCredit = array( 'contact_id'      => $formatted['soft_credit_to'], 
                                                 'contribution_id' => $ids['contribution'] );
                        $existingSoftCredit = CRM_Contribute_BAO_Contribution::getSoftContribution( $dupeSoftCredit );
                        if ( CRM_Utils_Array::value( 'soft_credit_id', $existingSoftCredit ) ) {
                            $formatted['softID'] = $existingSoftCredit['soft_credit_id'];
                        }
                    }
                    
                    $newContribution = CRM_Contribute_BAO_Contribution::create( $formatted , $ids );
                    $this->_newContributions[] = $newContribution->id;                    
                    
                    //return soft valid since we need to show how soft credits were added
                    if ( CRM_Utils_Array::value( 'soft_credit_to', $formatted ) ) {
                        return CRM_Contribute_Import_Parser::SOFT_CREDIT;
                    }
                    
                    // process pledge payment assoc w/ the contribution
                    return self::processPledgePayments( $formatted );
                    
                    return CRM_Contribute_Import_Parser::VALID;
                } else {
                    $labels = array(
                                     'id'         => 'Contribution ID',
                                     'trxn_id'    => 'Transaction ID',
                                     'invoice_id' => 'Invoice ID'
                                     );              
                    foreach ( $dupeIds as $k=>$v ) {
                        if( $v ) {
                            $errorMsg[]  = "$labels[$k] $v";                
                        } 
                    }
                    $errorMsg = implode( ' AND ', $errorMsg );
                    array_unshift($values,"Matching Contribution record not found for ".$errorMsg.". Row was skipped.");
                    return CRM_Contribute_Import_Parser::ERROR; 
                }               
            }
        }
        
        if ( $this->_contactIdIndex < 0 ) {
            // set the contact type if its not set
            if ( !isset( $paramValues['contact_type'] ) ) {    
                $paramValues['contact_type'] = $this->_contactType;
            }
            
            $paramValues['version'] = 3;
            //retrieve contact id using contact dedupe rule
            require_once 'api/v3/DeprecatedUtils.php';
            $error = _civicrm_api3_deprecated_check_contact_dedupe( $paramValues );
            
            if ( CRM_Core_Error::isAPIError( $error, CRM_Core_ERROR::DUPLICATE_CONTACT ) ) {
                $matchedIDs = explode(',',$error['error_message']['params'][0]);        
                if (count( $matchedIDs) >1) {
                    array_unshift($values,"Multiple matching contact records detected for this row. The contribution was not imported");
                    return CRM_Contribute_Import_Parser::ERROR;
                } else {
                    $cid = $matchedIDs[0];
                    $formatted['contact_id'] = $cid;
                    
                    $newContribution = civicrm_api( 'contribution','create', $formatted );
                    if ( civicrm_error( $newContribution ) ) { 
                        if ( is_array( $newContribution['error_message'] ) ) {
                            array_unshift($values, $newContribution['error_message']['message']);
                            if ( $newContribution['error_message']['params'][0] ) {
                                return CRM_Contribute_Import_Parser::DUPLICATE;
                            }
                        } else {
                            array_unshift($values, $newContribution['error_message']);
                            return CRM_Contribute_Import_Parser::ERROR;
                        }
                    }
                    
                    $this->_newContributions[] = $newContribution['id'];
                    $formatted['contribution_id'] = $newContribution['id'];
                                      
                    //return soft valid since we need to show how soft credits were added
                    if ( CRM_Utils_Array::value( 'soft_credit_to', $formatted ) ) {
                        return CRM_Contribute_Import_Parser::SOFT_CREDIT;
                    }
                    
                    // process pledge payment assoc w/ the contribution
                    return self::processPledgePayments( $formatted );
                    
                    return CRM_Contribute_Import_Parser::VALID;
                }
            } else {
                // Using new Dedupe rule.
                $ruleParams = array(
                                    'contact_type' => $this->_contactType,
                                    'level' => 'Strict'
                                    );
                $fieldsArray = CRM_Dedupe_BAO_Rule::dedupeRuleFields($ruleParams);
                
                foreach ( $fieldsArray as $value ) {
                    if(array_key_exists(trim($value),$params)) {
                        $paramValue = $params[trim($value)];
                        if (is_array($paramValue)) {
                            $disp .= $params[trim($value)][0][trim($value)]." ";  
                        } else {
                            $disp .= $params[trim($value)]." ";
                        }
                    }
                } 
                
                if ( CRM_Utils_Array::value('external_identifier',$params ) ) {
                    if ( $disp ) {
                        $disp .= "AND {$params['external_identifier']}";
                    } else {
                        $disp = $params['external_identifier']; 
                    }
                }
                
                array_unshift($values,"No matching Contact found for (".$disp.")");
                return CRM_Contribute_Import_Parser::ERROR;
            }
            
        } else {
            if ( CRM_Utils_Array::value( 'external_identifier', $paramValues ) ) { 
                $checkCid = new CRM_Contact_DAO_Contact();
                $checkCid->external_identifier = $paramValues['external_identifier'];
                $checkCid->find(true);
                if ($checkCid->id != $formatted['contact_id']) {
                    array_unshift($values, "Mismatch of External identifier :" . $paramValues['external_identifier'] . " and Contact Id:" . $formatted['contact_id']);
                    return CRM_Contribute_Import_Parser::ERROR;
                }
            }
            $newContribution = civicrm_api( 'contribution','create', $formatted );
            if ( civicrm_error( $newContribution ) ) { 
                if ( is_array( $newContribution['error_message'] ) ) {
                    array_unshift($values, $newContribution['error_message']['message']);
                    if ( $newContribution['error_message']['params'][0] ) {
                        return CRM_Contribute_Import_Parser::DUPLICATE;
                    }
                } else {
                    array_unshift($values, $newContribution['error_message']);
                    return CRM_Contribute_Import_Parser::ERROR;
                }
            }
            
            $this->_newContributions[] = $newContribution['id'];
            $formatted['contribution_id'] = $newContribution['id'];
                    
            //return soft valid since we need to show how soft credits were added
            if ( CRM_Utils_Array::value( 'soft_credit_to', $formatted ) ) {
                return CRM_Contribute_Import_Parser::SOFT_CREDIT;
            }
            
            // process pledge payment assoc w/ the contribution
            return self::processPledgePayments( $formatted );
            
            return CRM_Contribute_Import_Parser::VALID;
        }
    }
        
    /**
     *  Function to process pledge payments
     */
    function processPledgePayments( &$formatted )
    {
        if ( CRM_Utils_Array::value( 'pledge_payment_id', $formatted ) &&
             CRM_Utils_Array::value( 'pledge_id', $formatted ) ) {
            //get completed status
            $completeStatusID = CRM_Core_OptionGroup::getValue( 'contribution_status', 'Completed', 'name' );
           
            //need to update payment record to map contribution_id
            CRM_Core_DAO::setFieldValue( 'CRM_Pledge_DAO_PledgePayment', $formatted['pledge_payment_id'], 
                                         'contribution_id', $formatted['contribution_id'] );
            
            CRM_Pledge_BAO_PledgePayment::updatePledgePaymentStatus( $formatted['pledge_id'], 
                                                               array( $formatted['pledge_payment_id'] ),  
                                                               $completeStatusID,
                                                               null,
                                                               $formatted['total_amount'] );
            
            return CRM_Contribute_Import_Parser::PLEDGE_PAYMENT;
        }
    }

    /**
     * Get the array of succesfully imported contribution id's
     *
     * @return array
     * @access public
     */
    function &getImportedContributions() 
    {
        return $this->_newContributions;
    }
   
    /**
     * the initializer code, called before the processing
     *
     * @return void
     * @access public
     */
    function fini( ) 
    {
    }

}


