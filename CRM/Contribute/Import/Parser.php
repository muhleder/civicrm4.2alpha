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



abstract class CRM_Contribute_Import_Parser 
{

    const
        MAX_ERRORS       = 250,
        MAX_WARNINGS     = 25,
        VALID            =  1,
        WARNING          =  2,
        ERROR            =  3,
        CONFLICT         =  4,
        STOP             =  5,
        DUPLICATE        =  6,
        MULTIPLE_DUPE    =  7,
        NO_MATCH         =  8,
        SOFT_CREDIT      =  9,
        SOFT_CREDIT_ERROR = 10,
        PLEDGE_PAYMENT    = 11,
        PLEDGE_PAYMENT_ERROR = 12;

    /**
     * various parser modes
     */
    const
        MODE_MAPFIELD = 1,
        MODE_PREVIEW  = 2,
        MODE_SUMMARY  = 4,
        MODE_IMPORT   = 8;

    /**
     * codes for duplicate record handling
     */
    const
        DUPLICATE_SKIP = 1,
        DUPLICATE_REPLACE = 2,
        DUPLICATE_UPDATE = 4,
        DUPLICATE_FILL = 8,
        DUPLICATE_NOCHECK = 16;

    /**
     * various Contact types
     */
    const
        CONTACT_INDIVIDUAL     = 1,
        CONTACT_HOUSEHOLD      = 2,
        CONTACT_ORGANIZATION   = 4;

    protected $_fileName;

    /**#@+
     * @access protected
     * @var integer
     */

    /**
     * imported file size
     */
    protected $_fileSize;

    /**
     * seperator being used
     */
    protected $_seperator;

    /**
     * total number of lines in file
     */
    protected $_lineCount;

    /**
     * total number of non empty lines
     */
    protected $_totalCount;

    /**
     * running total number of valid lines
     */
    protected $_validCount;

    /**
     * running total number of invalid rows
     */
    protected $_invalidRowCount;
    
    /**
     * running total number of valid soft credit rows
     */
    protected $_validSoftCreditRowCount;

    /**
     * running total number of invalid soft credit rows
     */
    protected $_invalidSoftCreditRowCount;

    /**
     * running total number of valid pledge payment rows
     */
    protected $_validPledgePaymentRowCount;

    /**
     * running total number of invalid pledge payment rows
     */
    protected $_invalidPledgePaymentRowCount;

    /**
     * maximum number of invalid rows to store
     */
    protected $_maxErrorCount;

    /**
     * array of error lines, bounded by MAX_ERROR
     */
    protected $_errors;
    
    /**
     * array of pledge payment error lines, bounded by MAX_ERROR
     */
    protected $_pledgePaymentErrors;

    /**
     * array of pledge payment error lines, bounded by MAX_ERROR
     */
    protected $_softCreditErrors;
    
    /**
     * total number of conflict lines
     */
    protected $_conflictCount;

    /**
     * array of conflict lines
     */
    protected $_conflicts;

    /**
     * total number of duplicate (from database) lines
     */
    protected $_duplicateCount;

    /**
     * array of duplicate lines
     */
    protected $_duplicates;

    /**
     * running total number of warnings
     */
    protected $_warningCount;

    /**
     * maximum number of warnings to store
     */
    protected $_maxWarningCount = self::MAX_WARNINGS;

    /**
     * array of warning lines, bounded by MAX_WARNING
     */
    protected $_warnings;

    /**
     * array of all the fields that could potentially be part
     * of this import process
     * @var array
     */
    protected $_fields;

    /**
     * array of the fields that are actually part of the import process
     * the position in the array also dictates their position in the import
     * file
     * @var array
     */
    protected $_activeFields;

    /**
     * cache the count of active fields
     *
     * @var int
     */
    protected $_activeFieldCount;

    /**
     * maximum number of non-empty/comment lines to process
     *
     * @var int
     */
    protected $_maxLinesToProcess;

    /**
     * cache of preview rows
     *
     * @var array
     */
    protected $_rows;


    /**
     * filename of error data
     *
     * @var string
     */
    protected $_errorFileName;

    /**
     * filename of pledge payment error data
     *
     * @var string
     */
    protected $_pledgePaymentErrorsFileName;

    /**
     * filename of soft credit error data
     *
     * @var string
     */
    protected $_softCreditErrorsFileName;
    
    /**
     * filename of conflict data
     *
     * @var string
     */
    protected $_conflictFileName;


    /**
     * filename of duplicate data
     *
     * @var string
     */
    protected $_duplicateFileName;

    /**
     * whether the file has a column header or not
     *
     * @var boolean
     */
    protected $_haveColumnHeader;

     /**
     * contact type
     *
     * @var int
     */

    public $_contactType;


    function __construct() {
        $this->_maxLinesToProcess = 0;
        $this->_maxErrorCount = self::MAX_ERRORS;
    }

    abstract function init();

    function run( $fileName,
                  $seperator = ',',
                  &$mapper,
                  $skipColumnHeader = false,
                  $mode = self::MODE_PREVIEW,
                  $contactType = self::CONTACT_INDIVIDUAL,
                  $onDuplicate = self::DUPLICATE_SKIP ) {
        if ( ! is_array( $fileName ) ) {
            CRM_Core_Error::fatal( );
        }
        $fileName = $fileName['name'];

        switch ($contactType) {
        case self::CONTACT_INDIVIDUAL :
            $this->_contactType = 'Individual';
            break;
        case self::CONTACT_HOUSEHOLD :
            $this->_contactType = 'Household';
            break;
        case self::CONTACT_ORGANIZATION :
            $this->_contactType = 'Organization';
        }

        $this->init();

        $this->_haveColumnHeader = $skipColumnHeader;
      
        $this->_seperator = $seperator;

        $fd = fopen( $fileName, "r" );
        if ( ! $fd ) {
            return false;
        }

        $this->_lineCount       = $this->_warningCount = $this->_validSoftCreditRowCount   = $this->_validPledgePaymentRowCount = 0;
        $this->_invalidRowCount = $this->_validCount   = $this->_invalidSoftCreditRowCount = $this->_invalidPledgePaymentRowCount = 0;
        $this->_totalCount = $this->_conflictCount = 0;
    
        $this->_errors   = array();
        $this->_warnings = array();
        $this->_conflicts = array();
        $this->_pledgePaymentErrors = array();
        $this->_softCreditErrors = array();

        $this->_fileSize = number_format( filesize( $fileName ) / 1024.0, 2 );
        
        if ( $mode == self::MODE_MAPFIELD ) {
            $this->_rows = array( );
        } else {
            $this->_activeFieldCount = count( $this->_activeFields );
        }

        while ( ! feof( $fd ) ) {
            $this->_lineCount++;

            $values = fgetcsv( $fd, 8192, $seperator );
            if ( ! $values ) {
                continue;
            }

            self::encloseScrub($values);

            // skip column header if we're not in mapfield mode
            if ( $mode != self::MODE_MAPFIELD && $skipColumnHeader ) {
                    $skipColumnHeader = false;
                    continue;
            }

            /* trim whitespace around the values */
            $empty = true;
            foreach ($values as $k => $v) {
                $values[$k] = trim($v, " \t\r\n");
            }

            if ( CRM_Utils_System::isNull( $values ) ) {
                continue;
            }

            $this->_totalCount++;
            
            if ( $mode == self::MODE_MAPFIELD ) {
                $returnCode = $this->mapField( $values );
            } else if ( $mode == self::MODE_PREVIEW ) {
                $returnCode = $this->preview( $values );
            } else if ( $mode == self::MODE_SUMMARY ) {
                $returnCode = $this->summary( $values );
            } else if ( $mode == self::MODE_IMPORT ) {
                $returnCode = $this->import( $onDuplicate, $values );
            } else {
                $returnCode = self::ERROR;
            }

            // note that a line could be valid but still produce a warning
            if ( $returnCode == self::VALID ) {
                $this->_validCount++;
                if ( $mode == self::MODE_MAPFIELD ) {
                    $this->_rows[]           = $values;
                    $this->_activeFieldCount = max( $this->_activeFieldCount, count( $values ) );
                }
            }

            if ( $returnCode == self::SOFT_CREDIT ) {
                $this->_validSoftCreditRowCount++;
                $this->_validCount++;
                if ( $mode == self::MODE_MAPFIELD ) {
                    $this->_rows[]           = $values;
                    $this->_activeFieldCount = max( $this->_activeFieldCount, count( $values ) );
                }
            }
    
            if ( $returnCode == self::PLEDGE_PAYMENT ) {
                $this->_validPledgePaymentRowCount++;
                $this->_validCount++;
                if ( $mode == self::MODE_MAPFIELD ) {
                    $this->_rows[]           = $values;
                    $this->_activeFieldCount = max( $this->_activeFieldCount, count( $values ) );
                }
            }
            
            if ( $returnCode == self::WARNING ) {
                $this->_warningCount++;
                if ( $this->_warningCount < $this->_maxWarningCount ) {
                    $this->_warningCount[] = $line;
                }
            } 

            if ( $returnCode == self::ERROR ) {
                $this->_invalidRowCount++;
                if ( $this->_invalidRowCount < $this->_maxErrorCount ) {
                    $recordNumber = $this->_lineCount;
                    if ($this->_haveColumnHeader) $recordNumber--;
                    array_unshift($values, $recordNumber);
                    $this->_errors[] = $values;
                }
            } 
            
            if ( $returnCode == self::PLEDGE_PAYMENT_ERROR ) {
                $this->_invalidPledgePaymentRowCount++;
                if ( $this->_invalidPledgePaymentRowCount < $this->_maxErrorCount ) {
                    $recordNumber = $this->_lineCount;
                    if ($this->_haveColumnHeader) $recordNumber--;
                    array_unshift($values, $recordNumber);
                    $this->_pledgePaymentErrors[] = $values;
                }
            } 
            
            if ( $returnCode == self::SOFT_CREDIT_ERROR ) {
                $this->_invalidSoftCreditRowCount++;
                if ( $this->_invalidSoftCreditRowCount < $this->_maxErrorCount ) {
                    $recordNumber = $this->_lineCount;
                    if ($this->_haveColumnHeader) $recordNumber--;
                    array_unshift($values, $recordNumber);
                    $this->_softCreditErrors[] = $values;
                }
            }
            
            if ( $returnCode == self::CONFLICT ) {
                $this->_conflictCount++;
                $recordNumber = $this->_lineCount;
                if ($this->_haveColumnHeader) $recordNumber--;
                array_unshift($values, $recordNumber);
                $this->_conflicts[] = $values;
            } 
            
            if ( $returnCode == self::DUPLICATE ) {
                if ( $returnCode == self::MULTIPLE_DUPE ) {
                    /* TODO: multi-dupes should be counted apart from singles
                     * on non-skip action */
                }
                $this->_duplicateCount++;
                $recordNumber = $this->_lineCount;
                if ($this->_haveColumnHeader) $recordNumber--;
                array_unshift($values, $recordNumber);
                $this->_duplicates[] = $values;
                if ($onDuplicate != self::DUPLICATE_SKIP) {
                    $this->_validCount++;
                }
            }

            // we give the derived class a way of aborting the process
            // note that the return code could be multiple code or'ed together
            if ( $returnCode == self::STOP ) {
                break;
            }

            // if we are done processing the maxNumber of lines, break
            if ( $this->_maxLinesToProcess > 0 && $this->_validCount >= $this->_maxLinesToProcess ) {
                break;
            }
        }

        fclose( $fd );
        
        if ($mode == self::MODE_PREVIEW || $mode == self::MODE_IMPORT) {
            $customHeaders = $mapper;
            
            $customfields = CRM_Core_BAO_CustomField::getFields('Contribution');
            foreach ($customHeaders as $key => $value) {
                if ($id = CRM_Core_BAO_CustomField::getKeyID($value)) {
                    $customHeaders[$key] = $customfields[$id][0];
                }
            }
            if ($this->_invalidRowCount) {
                // removed view url for invlaid contacts
                $headers = array_merge( array(  ts('Line Number'),
                                                ts('Reason')), 
                                        $customHeaders);
                $this->_errorFileName = self::errorFileName( self::ERROR );
                self::exportCSV($this->_errorFileName, $headers, $this->_errors);
            }
            
            if ( $this->_invalidPledgePaymentRowCount ) {
                // removed view url for invlaid contacts
                $headers = array_merge( array(  ts('Line Number'),
                                                ts('Reason')), 
                                        $customHeaders);
                $this->_pledgePaymentErrorsFileName = self::errorFileName( self::PLEDGE_PAYMENT_ERROR );
                self::exportCSV( $this->_pledgePaymentErrorsFileName, $headers, $this->_pledgePaymentErrors );
            }
            
            if ( $this->_invalidSoftCreditRowCount ) {
                // removed view url for invlaid contacts
                $headers = array_merge( array(  ts('Line Number'),
                                                ts('Reason')), 
                                        $customHeaders);
                $this->_softCreditErrorsFileName = self::errorFileName( self::SOFT_CREDIT_ERROR );
                self::exportCSV( $this->_softCreditErrorsFileName, $headers, $this->_softCreditErrors );
            }
            
            if ($this->_conflictCount) {
                $headers = array_merge( array(  ts('Line Number'),
                                                ts('Reason')), 
                                        $customHeaders);
                $this->_conflictFileName = self::errorFileName( self::CONFLICT );
                self::exportCSV($this->_conflictFileName, $headers, $this->_conflicts);
            }
            if ($this->_duplicateCount) {
                $headers = array_merge( array(  ts('Line Number'), 
                                                ts('View Contribution URL')),
                                        $customHeaders);
                
                $this->_duplicateFileName = self::errorFileName( self::DUPLICATE );
                self::exportCSV($this->_duplicateFileName, $headers, $this->_duplicates);
            }
        }
        //echo "$this->_totalCount,$this->_invalidRowCount,$this->_conflictCount,$this->_duplicateCount";
        return $this->fini();
    }

    abstract function mapField( &$values );
    abstract function preview( &$values );
    abstract function summary( &$values );
    abstract function import ( $onDuplicate, &$values );

    abstract function fini();

    /**
     * Given a list of the importable field keys that the user has selected
     * set the active fields array to this list
     *
     * @param array mapped array of values
     *
     * @return void
     * @access public
     */
    function setActiveFields( $fieldKeys ) {
        $this->_activeFieldCount = count( $fieldKeys );
        foreach ( $fieldKeys as $key ) {
            if ( empty( $this->_fields[$key] ) ) {
                $this->_activeFields[] = new CRM_Contribute_Import_Field( '', ts( '- do not import -' ) );
            } else {
                $this->_activeFields[] = clone( $this->_fields[$key] );
            }
        }
    }
    
    function setActiveFieldSoftCredit( $elements ) {
        for ($i = 0; $i < count( $elements ); $i++) {
            $this->_activeFields[$i]->_softCreditField = $elements[$i];
        }
    }

    function setActiveFieldValues( $elements, &$erroneousField ) {    
        $maxCount = count( $elements ) < $this->_activeFieldCount ? count( $elements ) : $this->_activeFieldCount;
        for ( $i = 0; $i < $maxCount; $i++ ) {
            $this->_activeFields[$i]->setValue( $elements[$i] );
        }

        // reset all the values that we did not have an equivalent import element
        for ( ; $i < $this->_activeFieldCount; $i++ ) {
            $this->_activeFields[$i]->resetValue();
        }

        // now validate the fields and return false if error
        $valid = self::VALID;
        for ( $i = 0; $i < $this->_activeFieldCount; $i++ ) {
            if ( ! $this->_activeFields[$i]->validate() ) {
                // no need to do any more validation
                $erroneousField = $i;
                $valid = self::ERROR;
                break;
            }
        }
        return $valid;
    }

    /**
     * function to format the field values for input to the api
     *
     * @return array (reference ) associative array of name/value pairs
     * @access public
     */
    function &getActiveFieldParams( ) {
        $params = array( );
        for ( $i = 0; $i < $this->_activeFieldCount; $i++ ) {
            if ( isset( $this->_activeFields[$i]->_value ) ) {
                if ( isset( $this->_activeFields[$i]->_softCreditField ) ) {
                    if (! isset($params[$this->_activeFields[$i]->_name])) {
                        $params[$this->_activeFields[$i]->_name] = array();
                    }
                    $params[$this->_activeFields[$i]->_name][$this->_activeFields[$i]->_softCreditField] = $this->_activeFields[$i]->_value;
                }
                
                if (!isset($params[$this->_activeFields[$i]->_name])) {
                    if ( !isset($this->_activeFields[$i]->_softCreditField ) ) {
                        $params[$this->_activeFields[$i]->_name] = $this->_activeFields[$i]->_value;
                    }
                }
            }
        }
        return $params;
    }

    function getSelectValues() {
        $values = array();
        foreach ($this->_fields as $name => $field ) {
            $values[$name] = $field->_title;
        }
        return $values;
    }

    function getSelectTypes() {
        $values = array();
        foreach ($this->_fields as $name => $field ) {
            if ( isset($field->_hasLocationType ) ) {
                $values[$name] = $field->_hasLocationType;
            }
        }
        return $values;
    }

    function getHeaderPatterns() {
        $values = array();
        foreach ($this->_fields as $name => $field ) {
            if ( isset($field->_headerPattern ) ) {
                $values[$name] = $field->_headerPattern;
            }
        }
        return $values;
    }

    function getDataPatterns() {
        $values = array();
        foreach ($this->_fields as $name => $field ) {
            $values[$name] = $field->_dataPattern;
        }
        return $values;
    }

    function addField( $name, $title, $type = CRM_Utils_Type::T_INT, $headerPattern = '//', $dataPattern = '//') {
        if ( empty( $name ) ) {
            $this->_fields['doNotImport'] = new CRM_Contribute_Import_Field($name, $title, $type, $headerPattern, $dataPattern);
        } else {
            $tempField = CRM_Contact_BAO_Contact::importableFields('All', null );
            if (! array_key_exists ($name,$tempField) ) {
                $this->_fields[$name] = new CRM_Contribute_Import_Field($name, $title, $type, $headerPattern, $dataPattern);
            } else {
                $this->_fields[$name] = new CRM_Import_Field( $name, $title, $type, $headerPattern, $dataPattern,
                                                               CRM_Utils_Array::value( 'hasLocationType', $tempField[$name] ) );
            }
                
        }
    }

    /**
     * setter function
     *
     * @param int $max 
     *
     * @return void
     * @access public
     */
    function setMaxLinesToProcess( $max ) {
        $this->_maxLinesToProcess = $max;
    }

    /**
     * Store parser values
     *
     * @param CRM_Core_Session $store 
     *
     * @return void
     * @access public
     */
    function set( $store, $mode = self::MODE_SUMMARY ) {
        $store->set( 'fileSize'   , $this->_fileSize          );
        $store->set( 'lineCount'  , $this->_lineCount         );
        $store->set( 'seperator'  , $this->_seperator         );
        $store->set( 'fields'     , $this->getSelectValues( ) );
        $store->set( 'fieldTypes' , $this->getSelectTypes( )  );
        
        $store->set( 'headerPatterns', $this->getHeaderPatterns( ) );
        $store->set( 'dataPatterns', $this->getDataPatterns( ) );
        $store->set( 'columnCount', $this->_activeFieldCount  );
        
        $store->set( 'totalRowCount'    , $this->_totalCount     );
        $store->set( 'validRowCount'    , $this->_validCount     );
        $store->set( 'invalidRowCount'  , $this->_invalidRowCount     );
        $store->set( 'invalidSoftCreditRowCount', $this->_invalidSoftCreditRowCount );
        $store->set( 'validSoftCreditRowCount', $this->_validSoftCreditRowCount );
        $store->set( 'invalidPledgePaymentRowCount', $this->_invalidPledgePaymentRowCount );
        $store->set( 'validPledgePaymentRowCount', $this->_validPledgePaymentRowCount );
        $store->set( 'conflictRowCount', $this->_conflictCount );
        
        switch ($this->_contactType) {
        case 'Individual':
            $store->set( 'contactType', CRM_Contribute_Import_Parser::CONTACT_INDIVIDUAL );    
            break;
        case 'Household' :
            $store->set( 'contactType', CRM_Contribute_Import_Parser::CONTACT_HOUSEHOLD );    
            break;
        case 'Organization':
            $store->set( 'contactType', CRM_Contribute_Import_Parser::CONTACT_ORGANIZATION );    
        }

        if ($this->_invalidRowCount) {
            $store->set( 'errorsFileName', $this->_errorFileName );
        }
        if ($this->_conflictCount) {
            $store->set( 'conflictsFileName', $this->_conflictFileName );
        }
        if ( isset( $this->_rows ) && ! empty( $this->_rows ) ) {
            $store->set( 'dataValues', $this->_rows );
        }

        if ( $this->_invalidPledgePaymentRowCount ) {
            $store->set( 'pledgePaymentErrorsFileName', $this->_pledgePaymentErrorsFileName );
        }

        if ( $this->_invalidSoftCreditRowCount ) {
            $store->set( 'softCreditErrorsFileName', $this->_softCreditErrorsFileName );
        }

        if ($mode == self::MODE_IMPORT) {
            $store->set( 'duplicateRowCount', $this->_duplicateCount );
            if ($this->_duplicateCount) {
                $store->set( 'duplicatesFileName', $this->_duplicateFileName );
            }
        }
        //echo "$this->_totalCount,$this->_invalidRowCount,$this->_conflictCount,$this->_duplicateCount";
    }

    /**
     * Export data to a CSV file
     *
     * @param string $filename
     * @param array $header
     * @param data $data
     * @return void
     * @access public
     */
    static function exportCSV($fileName, $header, $data) {
        $output = array();
        $fd = fopen($fileName, 'w');

        foreach ($header as $key => $value) {
            $header[$key] = "\"$value\"";
        }
        $config = CRM_Core_Config::singleton( );
        $output[] = implode($config->fieldSeparator, $header);
        
        foreach ($data as $datum) {
            foreach ($datum as $key => $value) {
                if ( is_array($value[0]) ) {
                    foreach($value[0] as $k1=>$v1) {
                        if ($k1 == 'location_type_id') {
                            continue;
                        }
                        $datum[$k1] =  $v1;
                    }
                } else {
                    $datum[$key] = "\"$value\"";
                }
            }
            $output[] = implode($config->fieldSeparator, $datum);
        }
        fwrite($fd, implode("\n", $output));
        fclose($fd);
    }

    /** 
     * Remove single-quote enclosures from a value array (row)
     *
     * @param array $values
     * @param string $enclosure
     * @return void
     * @static
     * @access public
     */
    static function encloseScrub(&$values, $enclosure = "'") {
        if (empty($values)) 
            return;

        foreach ($values as $k => $v) {
            $values[$k] = preg_replace("/^$enclosure(.*)$enclosure$/", '$1', $v);
        }
    }
    
    function errorFileName( $type ) {
        $fileName = null;
        if ( empty( $type ) ) return $fileName; 
        
        $config   = CRM_Core_Config::singleton( );
        $fileName = $config->uploadDir . "sqlImport";
        
        switch ( $type ) {
        case CRM_Contribute_Import_Parser::ERROR:
        case CRM_Contribute_Import_Parser::NO_MATCH: 
        case CRM_Contribute_Import_Parser::CONFLICT:
        case CRM_Contribute_Import_Parser::DUPLICATE:
            //here constants get collides.
            if ( $type == CRM_Contribute_Import_Parser::ERROR ) {
                $type = CRM_Import_Parser::ERROR;
            } else if ( $type == CRM_Contribute_Import_Parser::NO_MATCH ) {
                $type = CRM_Import_Parser::NO_MATCH;
            } else if ( $type == CRM_Contribute_Import_Parser::CONFLICT ) {
                $type = CRM_Import_Parser::CONFLICT;
            } else {
                $type = CRM_Import_Parser::DUPLICATE;
            }
            $fileName = CRM_Import_Parser::errorFileName( $type );
            break;
            
        case CRM_Contribute_Import_Parser::SOFT_CREDIT_ERROR :
            $fileName .= '.softCreditErrors';
            break;
            
        case CRM_Contribute_Import_Parser::PLEDGE_PAYMENT_ERROR :
            $fileName .= '.pledgePaymentErrors';
            break;
        }
        
        return $fileName;
    }
    
    function saveFileName( $type ) {
        $fileName = null;
        if ( empty( $type ) ) return $fileName;
        
        switch ( $type ) {
            
        case CRM_Contribute_Import_Parser::ERROR:
        case CRM_Contribute_Import_Parser::NO_MATCH: 
        case CRM_Contribute_Import_Parser::CONFLICT:
        case CRM_Contribute_Import_Parser::DUPLICATE:
            //here constants get collides.
            if ( $type == CRM_Contribute_Import_Parser::ERROR ) {
                $type = CRM_Import_Parser::ERROR;
            } else if ( $type == CRM_Contribute_Import_Parser::NO_MATCH ) {
                $type = CRM_Import_Parser::NO_MATCH;
            } else if ( $type == CRM_Contribute_Import_Parser::CONFLICT ) {
                $type = CRM_Import_Parser::CONFLICT;
            } else {
                $type = CRM_Import_Parser::DUPLICATE;
            }
            $fileName = CRM_Import_Parser::saveFileName( $type );
            break;
            
        case CRM_Contribute_Import_Parser::SOFT_CREDIT_ERROR :
            $fileName = 'Import_Soft_Credit_Errors.csv';
            break;
            
        case CRM_Contribute_Import_Parser::PLEDGE_PAYMENT_ERROR :
            $fileName = 'Import_Pledge_Payment_Errors.csv';
            break;
        }
        
        return $fileName;
    }
    
}


