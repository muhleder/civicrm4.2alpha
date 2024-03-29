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
 
abstract class CRM_Core_Payment {
    /**
     * how are we getting billing information?
     *
     * FORM   - we collect it on the same page
     * BUTTON - the processor collects it and sends it back to us via some protocol
     */
    const
        BILLING_MODE_FORM   = 1,
        BILLING_MODE_BUTTON = 2,
        BILLING_MODE_NOTIFY = 4;

    /**
     * which payment type(s) are we using?
     * 
     * credit card
     * direct debit
     * or both
     * 
     */
    const    
        PAYMENT_TYPE_CREDIT_CARD = 1,
        PAYMENT_TYPE_DIRECT_DEBIT = 2;

    /**
     * Subscription / Recurring payment Status
     * START, END
     * 
     */
    const    
        RECURRING_PAYMENT_START = 'START',
        RECURRING_PAYMENT_END   = 'END';

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;

    protected $_paymentProcessor;

    protected $_paymentForm = null;

    /**  
     * singleton function used to manage this object  
     *  
     * @param string $mode the mode of operation: live or test
     *  
     * @return object  
     * @static  
     *  
     */  
    static function &singleton( $mode = 'test', &$paymentProcessor, &$paymentForm = null, $force = false ) 
    {
        // make sure paymentProcessor is not empty
        // CRM-7424
        if ( empty( $paymentProcessor ) ) {
            return CRM_Core_DAO::$_nullObject;
        }

        $cacheKey = "{$mode}_{$paymentProcessor['id']}_".(int)isset( $paymentForm );
        if ( !isset( self::$_singleton[$cacheKey] ) || $force ) {
            $config = CRM_Core_Config::singleton( );
            $ext = new CRM_Core_Extensions();
            if ( $ext->isExtensionKey( $paymentProcessor['class_name'] ) ) {
                $paymentClass = $ext->keyToClass( $paymentProcessor['class_name'], 'payment' );
                require_once( $ext->classToPath( $paymentClass ) );
            } else {                
                $paymentClass = 'CRM_Core_' . $paymentProcessor['class_name'];
                require_once( str_replace( '_', DIRECTORY_SEPARATOR , $paymentClass ) . '.php' );
            }
            
            //load the object.
            self::$_singleton[$cacheKey] = eval( 'return ' . $paymentClass . '::singleton( $mode, $paymentProcessor );' );
            
            //load the payment form for required processor.
            if ( $paymentForm !== null ) {
                self::$_singleton[$cacheKey]->setForm( $paymentForm );
            }
        }
        
        return self::$_singleton[$cacheKey];
    }
    
    /**
     * Setter for the payment form that wants to use the processor
     *
     * @param obj $paymentForm
     * 
     */
    function setForm( &$paymentForm ) {
        $this->_paymentForm = $paymentForm;
    }
    
    /**
     * Getter for payment form that is using the processor
     *
     * @return obj  A form object
     */
    function getForm( ) {
        return $this->_paymentForm;
    }

    /**
     * Getter for accessing member vars
     *
     */
    function getVar( $name ) {
        return isset( $this->$name ) ? $this->$name : null;
    }

    /**
     * This function collects all the information from a web/api form and invokes
     * the relevant payment processor specific functions to perform the transaction
     *
     * @param  array $params assoc array of input parameters for this transaction
     *
     * @return array the result in an nice formatted array (or an error object)
     * @abstract
     */
    abstract function doDirectPayment( &$params );

    /**
     * This function checks to see if we have the right config values
     *
     * @param  string $mode the mode we are operating in (live or test)
     *
     * @return string the error message if any
     * @public
     */
    abstract function checkConfig( );

    /**
     * This function returns the URL used to cancel recurring subscriptions
     *
     * @return string the url of the payment processor cancel page
     * @public
     */
    function cancelSubscriptionURL( ) {
        return null;
    }

    static function paypalRedirect( &$paymentProcessor ) {
        if ( ! $paymentProcessor ) {
            return false;
        }

        if ( isset( $_GET['payment_date'] )                                       &&
             isset( $_GET['merchant_return_link'] )                               &&
             CRM_Utils_Array::value( 'payment_status', $_GET ) == 'Completed'     &&
             $paymentProcessor['payment_processor_type'] == "PayPal_Standard" ) {
            return true;
        }
        
        return false;
    }

    /**
     * Function to check whether the method is present for the payment processor  
     *
     * @param  object $paymentObject Object of the payment processor.
     * @return boolean
     * @public
     */
    static function isCancelSupported( &$paymentObject ) 
    {
        return method_exists( CRM_Utils_System::getClassName( $paymentObject ), 'cancelSubscription' );
    }
    
    /**
     * Page callback for civicrm/payment/ipn
     * Load requested payment processor and call that processor's handlePaymentNotification method
     *
     * @public
     */
    static function handleIPN() {
        
        if ( !isset( $_GET['processor_name'] ) ) {
            CRM_Core_Error::fatal( "Missing 'processor_name' param for IPN callback" );
            return;
        }
        
        // Query db for processor ..
        $mode = @$_GET['mode'];
        
        $dao = CRM_Core_DAO::executeQuery( "
             SELECT ppt.class_name, ppt.name as processor_name, pp.id AS processor_id
               FROM civicrm_payment_processor_type ppt
          LEFT JOIN civicrm_payment_processor pp
                 ON pp.name = ppt.name
                AND pp.is_active
                AND pp.is_test = %1
              WHERE ppt.name = %2 
        ",
            array(
                1 => array( $mode == 'test' ? 1 : 0, 'Integer' ),
                2 => array( $_GET['processor_name'], 'String' )
            )
        );
        
        if ( $dao->fetch() ) {
            
            // Handle extensions and traditional payment modules
            $ext = new CRM_Core_Extensions( );
            if ( $ext->isExtensionKey( $dao->class_name ) ) {
                $paymentClass = $ext->keyToClass( $dao->class_name, 'payment' );
                require_once $ext->classToPath( $paymentClass );
            } else {                
                CRM_Core_Error::fatal( "handlePaymentNotification method not supported under legacy payment processors" );
            }
            
            $paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $dao->processor_id, $mode );
   
            if ( empty( $paymentProcessor ) ) {
                CRM_Core_Error::fatal( "No enabled instances of this payment processor were found." );
            }
            
            // Instantiate PP
            eval( '$processorInstance = ' . $paymentClass . '::singleton( $mode, $paymentProcessor );' );
            
            // Does PP implement a handleIPN method, and can we call it?
            if ( ! method_exists( $processorInstance, 'handlePaymentNotification' ) || 
                 ! is_callable( array( $processorInstance, 'handlePaymentNotification' ) ) ) {
                // No? Sorry then ..
                CRM_Core_Error::fatal( "Payment processor does not implement a handlePaymentNotification method" );
            }
            
            // Everything, it seems, is ok - execute pp callback handler
            $processorInstance->handlePaymentNotification();
            
        } else {
            CRM_Core_Error::fatal( "Payment processor '{$_GET['processor_name']}' was not found." );
        }
        
        CRM_Utils_System::civiExit();
              
    }

}


