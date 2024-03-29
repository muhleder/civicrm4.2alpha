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


class CRM_SMS_Provider_Clickatell extends CRM_SMS_Provider {

	/**
	 * api type to use to send a message
	 * @var	string
	 */
	protected $_apiType  = 'http';

	/**
	 * provider details 
	 * @var	string
	 */
	protected $_providerInfo = array();

	/**
	 * Clickatell API Server Session ID
     *
	 * @var string
	 */
	protected $_sessionID = null;

	/**
	 * Curl handle resource id
     *
	 */
	protected $_ch;

	/**
	 * Temporary file resource id
	 * @var	resource
	 */
	protected $_fp;

	protected $_messageType = 
        array (
               'SMS_TEXT',
               'SMS_FLASH',
               'SMS_NOKIA_OLOGO',
               'SMS_NOKIA_GLOGO',
               'SMS_NOKIA_PICTURE',
               'SMS_NOKIA_RINGTONE',
               'SMS_NOKIA_RTTL',
               'SMS_NOKIA_CLEAN',
               'SMS_NOKIA_VCARD',
               'SMS_NOKIA_VCAL'
               );
    
	protected $_messageStatus = 
        array (
               '001' => 'Message unknown',
               '002' => 'Message queued',
               '003' => 'Delivered',
               '004' => 'Received by recipient',
               '005' => 'Error with message',
               '006' => 'User cancelled message delivery',
               '007' => 'Error delivering message',
               '008' => 'OK',
               '009' => 'Routing error',
               '010' => 'Message expired',
               '011' => 'Message queued for later delivery',
               '012' => 'Out of credit'
               );

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = array();

    /**
     * Constructor
     *
     * Create and auth a Clickatell session.
     *
     * @return void
     */
    function __construct( $provider = array(), $skipAuth = false ) {
        // initialize vars
        $this->_apiType = CRM_Utils_Array::value( 'api_type', $provider, 'http' );
        $this->_providerInfo = $provider;

        if ( $skipAuth ) {
            return true;
        }

        // first create the curl handle
		/**
		 * Reuse the curl handle
		 */
        $this->_ch = curl_init();
        if (!$this->_ch || !is_resource($this->_ch)) {
            return PEAR::raiseError('Cannot initialise a new curl handle.');
        }
		
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($this->_ch, CURLOPT_VERBOSE, 1);
        curl_setopt($this->_ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->_ch, CURLOPT_COOKIEJAR, "/dev/null");
        curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_ch, CURLOPT_USERAGENT, 'CiviCRM - http://civicrm.org/');

        $this->authenticate( );
    }

    /**
     * singleton function used to manage this object
     *
     * @return object
     * @static
     *
     */
    static function &singleton( $providerParams = array(), $force = false ) {
        $providerID = CRM_Utils_Array::value( 'provider_id', $providerParams );
        $skipAuth   = $providerID ? false : true;
        $cacheKey   = (int) $providerID;

        if ( !isset( self::$_singleton[$cacheKey] ) || $force ) {
            $provider = array();
            if ( $providerID ) {
                $provider = CRM_SMS_BAO_Provider::getProviderInfo( $providerID );
            }
            self::$_singleton[$cacheKey] = new CRM_SMS_Provider_Clickatell( $provider, $skipAuth );
        }
        return self::$_singleton[$cacheKey];
    }

	/**
	 * Authenticate to the Clickatell API Server.
	 *
	 * @return mixed true on sucess or PEAR_Error object
	 * @access public
	 * @since 1.1
	 */
	function authenticate( ) {
        $url = $this->_providerInfo['api_url'] . "/http/auth";

		$postData = 
            "user="      . $this->_providerInfo['username'] . 
            "&password=" . $this->_providerInfo['password'] . 
            "&api_id="   . $this->_providerInfo['api_params']['api_id'];
        
		$response = $this->curl($url, $postData);
		if (PEAR::isError($response)) {
			return $response;
		}
		$sess = explode(":", $response['data']);

		$this->_sessionID = trim($sess[1]);

		if ($sess[0] == "OK") {
			return (true);
		} else {
			return PEAR::raiseError($response['data']);
		}
	}

    function formURLPostData( $url, $id = null ) {
		$url = $this->_providerInfo['api_url'] . $url;
		$postData = "session_id=" . $this->_sessionID;
        if ( $id ) {
            if (strlen($id) < 32 || strlen($id) > 32) {
                return PEAR::raiseError('Invalid API Message Id');
            }
            $postData .= "&apimsgid=" . $id;
        }
        return array( $url, $postData );
    }

	/**
	 * Send an SMS Message via the Clickatell API Server
	 *
	 * @param array the message with a to/from/text
	 *
	 * @return mixed true on sucess or PEAR_Error object
	 * @access public
	 */
	function send ( $recipients, $header, $message, $jobID = null ) {
        if ( $this->_apiType = 'http' ) {
            list( $url, $postData ) = $this->formURLPostData( "/http/sendmsg" );
            
            if ( array_key_exists( 'from', $this->_providerInfo['api_params'] ) ) {
                $postData .= "&from=" . $this->_providerInfo['api_params']['from'];
            }
            $postData .= "&to="   . $header['To'];
            $postData .= "&text=" . $message;
            //$postData .= "&climsgid=" . $header['id'];
            if ( array_key_exists( 'mo', $this->_providerInfo['api_params'] ) ) {
                $postData .= "&mo=" . $this->_providerInfo['api_params']['mo'];
            }
            // sendmsg with delivery acknowledgment and callback request:
            $postData .= "&callback=3&deliv_ack=1";

            /**
             * Check if we are using a queue when sending as each account
             * with Clickatell is assigned three queues namely 1, 2 and 3.
             */
            if (isset($header['queue']) && is_numeric($header['queue'])) {
                if (in_array($header['queue'], range(1, 3))) {
                    $postData .= "&queue=" . $header['queue'];
                }
            }
            
            $reqFeat = 0;
            
            /**
             * Normal text message
             */
            if (CRM_Utils_Array::value('Type', $header) == 'SMS_TEXT') {
                $reqFeat += 1;
            }
            
            /**
             * We set the sender id is alpha numeric or numeric
             * then we change the sender from data.
             */
            if (is_numeric(CRM_Utils_Array::value('From', $header))) {
                $reqFeat += 32;
            } elseif (is_string($header['From'])) {
                $reqFeat += 16;
            }
            
            /**
             * Flash Messaging
             */
            if (CRM_Utils_Array::value('msg_type', $header) == 'SMS_FLASH') {
                $reqFeat += 512;
            }
            
            /**
             * Delivery Acknowledgments
             */
            $reqFeat += 8192;
            
            if (!empty($reqFeat)) {
                $postData .= "&req_feat=" . $reqFeat;
            }
            
            /**
             * Must we escalate message delivery if message is stuck in
             * the queue at Clickatell?
             */
            if (isset($header['escalate']) && !empty($header['escalate'])) {
                if (is_numeric($header['escalate'])) {
                    if (in_array($header['escalate'], range(1, 2))) {
                        $postData .= "&escalate=" . $header['escalate'];
                    }
                }
            }
            
            $response = $this->curl($url, $postData);
            if (PEAR::isError($response)) {
                return $response;
            }
            $send = explode(":", $response['data']);
            
            if ( $send[0] == "ID" ) {
                $this->createActivity( $send[1], $message, $header, $jobID );
                return $send[1];
            } else {
                return PEAR::raiseError($response['data']);
            }
        }
	}

	function callback( ) {
        $apiMsgID = $this->retrieve( 'apiMsgId', 'String' );

        $activity = new CRM_Activity_DAO_Activity( );
        $activity->result = $apiMsgID;

        if ( $activity->find( true ) ) {
            $actStatusIDs = array_flip( CRM_Core_OptionGroup::values( 'activity_status' ) );

            $status = $this->retrieve( 'status', 'String' );
            switch( $status ) 
                {
                case "001":    $statusID = $actStatusIDs['Cancelled'];    $clickStat = $this->_messageStatus[$status]." - Message Unknown";                  
                    break;
                case "002":    $statusID = $actStatusIDs['Scheduled'];    $clickStat = $this->_messageStatus[$status]." - Message Queued";                   
                    break;
                case "003":    $statusID = $actStatusIDs['Completed'];    $clickStat = $this->_messageStatus[$status]." - Delivered to Gateway";             
                    break;
                case "004":    $statusID = $actStatusIDs['Completed'];    $clickStat = $this->_messageStatus[$status]." - Received by Recipient";            
                    break;
                case "005":    $statusID = $actStatusIDs['Cancelled'];    $clickStat = $this->_messageStatus[$status]." - Error with Message";               
                    break;
                case "006":    $statusID = $actStatusIDs['Cancelled'];    $clickStat = $this->_messageStatus[$status]." - User cancelled message";           
                    break;
                case "007":    $statusID = $actStatusIDs['Cancelled'];    $clickStat = $this->_messageStatus[$status]." - Error delivering message";         
                    break;
                case "008":    $statusID = $actStatusIDs['Completed'];    $clickStat = $this->_messageStatus[$status]." - Ok, Message Received by Gateway";  
                    break;
                case "009":    $statusID = $actStatusIDs['Cancelled'];    $clickStat = $this->_messageStatus[$status]." - Routing Error";                    
                    break;
                case "010":    $statusID = $actStatusIDs['Cancelled'];    $clickStat = $this->_messageStatus[$status]." - Message Expired";                  
                    break;
                case "011":    $statusID = $actStatusIDs['Scheduled'];    $clickStat = $this->_messageStatus[$status]." - Message Queued for Later";         
                    break;
                case "012":    $statusID = $actStatusIDs['Cancelled'];    $clickStat = $this->_messageStatus[$status]." - Out of Credit";                    
                    break;
                }

            if ( $statusID ) {
                // update activity with status + msg in location
                $activity->status_id = $statusID;
                $activity->location  = $clickStat;
                $activity->activity_date_time = CRM_Utils_Date::isoToMysql( $activity->activity_date_time );
                $activity->save( );
                CRM_Core_Error::debug_log_message( "SMS Response updated for apiMsgId={$apiMsgID}." );
                return true;
            }
        }

        // if no update is done
        CRM_Core_Error::debug_log_message( "Could not update SMS Response for apiMsgId={$apiMsgID}." );
        return false;
    }

	function inbound( ) {
        $like = "";
        $fromPhone = $this->retrieve( 'from', 'String' );
        $fromPhone = $this->formatPhone($this->stripPhone($fromPhone), $like, "like");

        return parent::inbound($fromPhone, $this->retrieve( 'text', 'String' ), null, $this->retrieve( 'moMsgId', 'String' ));
    }

	/**
	 * Perform curl stuff
	 *
	 * @param   string  URL to call
	 * @param   string  HTTP Post Data
	 * @return  mixed   HTTP response body or PEAR Error Object
	 * @access	private
	 */
	function curl ($url, $postData) {
		$this->_fp = tmpfile();

		curl_setopt($this->_ch, CURLOPT_URL, $url);
		curl_setopt($this->_ch, CURLOPT_POST, 1);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($this->_ch, CURLOPT_FILE, $this->_fp);

		$status = curl_exec($this->_ch);
		$response['http_code'] = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);

		if (empty($response['http_code'])) {
			return PEAR::raiseError ('No HTTP Status Code was returned.');
		} elseif ($response['http_code'] === 0) {
			return PEAR::raiseError ('Cannot connect to the Clickatell API Server.');
		}

		if ($status) {
			$response['error'] = curl_error($this->_ch);
			$response['errno'] = curl_errno($this->_ch);
		}

		rewind($this->_fp);

		$pairs = "";
		while ($str = fgets($this->_fp, 4096)) {
			$pairs .= $str;
		}
		fclose($this->_fp);

		$response['data'] = $pairs;
		unset($pairs);
		asort($response);

		return ($response);
	}
}
