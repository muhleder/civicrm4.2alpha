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
 * This class provides the common functionality for sending sms to
 * one or a group of contact ids.
 */
class CRM_Contact_Form_Task_SMSCommon
{
    const RECIEVED_SMS_ACTIVITY_SUBJECT = "SMS Received";
    
    public $_contactDetails      = array( );
    
    public $_allContactDetails   = array( );
    
    public $_toContactPhone      = array( );
    

    static function preProcessProvider( &$form ) 
    {
        $form->_single  = false;
        $className = CRM_Utils_System::getClassName( $form );
      
        if ( property_exists( $form , '_context' ) && 
             $form->_context != 'search' &&
             $className == 'CRM_Contact_Form_Task_SMS' ) {
            $form->_single = true;
        }
        
        $providersCount = CRM_SMS_BAO_Provider::activeProviderCount();
        
        if( !$providersCount ) {
            CRM_Core_Error::statusBounce( ts( 'There are no providers configured or no providers are set active' ));
        }
        
        if ( $className == 'CRM_Activity_Form_Task_SMS' ) {
            $activityCheck = 0;
            foreach( $form->_activityHolderIds as $value ) {
                if ( CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity',$value, 'subject','id' ) != self::RECIEVED_SMS_ACTIVITY_SUBJECT ) {
                    $activityCheck++;
                }
            }
            if( $activityCheck == count($form->_activityHolderIds) ) {
                CRM_Core_Error::statusBounce( ts( "The Reply SMS Could only be sent for activities with '%1' subject.", 
                                                  array(1 => self::RECIEVED_SMS_ACTIVITY_SUBJECT) ) );
            }
        }
        
       
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    static function buildQuickForm( &$form )
    {
        
        $toArray = array( );
        
        $form->assign( 'max_sms_length', CRM_SMS_Provider::MAX_SMS_CHAR );
        
        $providers = CRM_SMS_BAO_Provider::getProviders( null, null, true, 'is_default desc' );

		$providerSelect = array( );
        foreach ( $providers as $provider ) {
            $providerSelect[$provider['id']] = $provider['title'];
        }
        $suppressedSms = 0;
        //here we are getting logged in user id as array but we need target contact id. CRM-5988
        $cid = $form->get( 'cid' );
        
        if( $cid ) {
            $form->_contactIds = array( $cid );

        }

        $to  = $form->add( 'text', 'to', ts('To'), '', true );
        $form->add( 'text', 'activity_subject', ts('Name The SMS'), '', true );

        $form->addDateTime( 'send_at', ts('Send At'), false );  
        $form->addDateTime( 'invalid_after', ts('Invalid After'), false );

        $toSetDefault = true;
        if ( property_exists( $form , '_context' ) && $form->_context == 'standalone' ) {
            $toSetDefault = false;
        }

    	// when form is submitted recompute contactIds
    	$allToSMS = array( );
    	if ( $to->getValue( ) ) {
    	    $allToPhone = explode( ',', $to->getValue( ) );

    	    $form->_contactIds = array( );
    	    foreach( $allToPhone as $value ) {
    	        list( $contactId, $phone ) = explode( '::', $value );
    	        if ( $contactId ) {
    	            $form->_contactIds[]      = $contactId;
    	            $form->_toContactPhone[] = $phone;
	            }
    	    }
    	    $toSetDefault = true;
    	}

        //get the group of contacts as per selected by user in case of Find Activities
        if ( !empty( $form->_activityHolderIds ) ) {
            $extendTargetContacts = 0;
            $invalidActivity = 0;
            $validActivities = 0;
            foreach ( $form->_activityHolderIds as $key => $id ) {
                //valid activity check
                if( CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity', $id, 'subject','id' ) != self::RECIEVED_SMS_ACTIVITY_SUBJECT ) {
                    $invalidActivity++;
                    continue;
                }
                
                //target contacts limit check
                $ids = array_keys( CRM_Activity_BAO_ActivityTarget::getTargetNames( $id ) );

                if ( count($ids) > 1 ) {
                    $extendTargetContacts++;
                    continue;
                }
                $validActivities++;
                $form->_contactIds = empty($form->_contactIds) ? $ids : 
                    array_unique(array_merge( $form->_contactIds, $ids ));
            }
            
            if( !$validActivities ) {
                $errorMess = "";
                if ($extendTargetContacts) {
                    $errorMess = $extendTargetContacts > 1 ? 'Activities' : 'Activity';
                    $errorMess = $extendTargetContacts . " Selected " . $errorMess . " consists of more than one target contact ";
                }
                if ( $invalidActivity ) {
                    $errorMess = empty($errorMess) ? $invalidActivity . " Selected Activities are invalid " : 
                        $errorMess . " and " . $invalidActivity . " Selected Activities are invalid ";
                }
                CRM_Core_Error::statusBounce( ts( $errorMess . " so SMS Reply would not be send." ) );
            }
        }

    	if ( is_array ( $form->_contactIds ) && $toSetDefault ) {
            $returnProperties = array( 'sort_name'             => 1, 
                                       'phone'                 => 1, 
                                       'do_not_sms'            => 1, 
                                       'is_deceased'           => 1,
                                       'display_name'          => 1,
                                       );
        
            list( $form->_contactDetails ) = CRM_Utils_Token::getTokenDetails( $form->_contactIds,
                                                                               $returnProperties,
                                                                               false,
                                                                               false );

            // make a copy of all contact details
            $form->_allContactDetails = $form->_contactDetails;

            foreach ( $form->_contactIds as $key => $contactId ) {
                $value = $form->_contactDetails[$contactId];
    
                //to check if the phone type is "Mobile"
                $phoneTypes = CRM_Core_PseudoConstant::phoneType( );
                  
                if ( CRM_Utils_System::getClassName( $form ) == 'CRM_Activity_Form_Task_SMS' ) {
                    //to check for "if the contact id belongs to a specified activity type"
                    $actDetails = CRM_Activity_BAO_Activity::getContactActivity( $contactId );
                    if (self::RECIEVED_SMS_ACTIVITY_SUBJECT != 
                        CRM_Utils_Array::retrieveValueRecursive($actDetails, 'subject')) {
                        $suppressedSms++;
                        unset( $form->_contactDetails[$contactId] );
                        continue;
                    }
                } 
                
                if ( (isset($value['phone_type_id']) && $value['phone_type_id'] != CRM_Utils_Array::key( 'Mobile', $phoneTypes )) || $value['do_not_sms'] || empty( $value['phone'] ) || CRM_Utils_Array::value( 'is_deceased', $value ) ) {
                 
                    //if phone is not primary check if non-primary phone is "Mobile"
                    if ( !empty( $value['phone'] ) 
                         && $value['phone_type_id'] != CRM_Utils_Array::key( 'Mobile', $phoneTypes ) 
                         && !CRM_Utils_Array::value( 'is_deceased', $value ) ) {
                        $filter = array( 'do_not_sms' => 0 );
                        $contactPhones = CRM_Core_BAO_Phone::allPhones( $contactId, false, 'Mobile', $filter );
                        if ( count($contactPhones) > 0 ) {
                            $mobilePhone = CRM_Utils_Array::retrieveValueRecursive($contactPhones, 'phone');
                        } else {
                            $suppressedSms++;
                            unset( $form->_contactDetails[$contactId] );
                            continue;
                        }
                    } else {
                        $suppressedSms++;
                        unset( $form->_contactDetails[$contactId] );
                        continue;
                    }
                }
                
                if ( isset($mobilePhone) ) {
                    $phone = $mobilePhone;
                } elseif ( empty( $form->_toContactPhone ) ) {
                    $phone = $value['phone'];
                } else {
                    $phone = CRM_Utils_Array::value($key, $form->_toContactPhone);
                }
                
                if ( $phone ) {
                    $toArray[] = array( 'name' => '"'. $value['sort_name'] .'" &lt;' .$phone .'&gt;',
                                        'id'   => "$contactId::{$phone}" );
                }
                
            }
            
    		if ( empty( $toArray ) ) {
    			CRM_Core_Error::statusBounce( ts('Selected contact(s) do not have a valid Phone, or communication preferences specify DO NOT SMS, or they are deceased' ));

    		}
    	}

        //activity related variables 
        if( isset($invalidActivity) ) $form->assign('invalidActivity', $invalidActivity);
        if( isset($extendTargetContacts) ) $form->assign('extendTargetContacts', $extendTargetContacts);
        
        
		$form->assign('toContact', json_encode( $toArray ) );
		$form->assign('suppressedSms', $suppressedSms);
        $form->assign('totalSelectedContacts',count($form->_contactIds));

        $form->add( 'select', 'sms_provider_id', ts('From'), $providerSelect, true );
        
        CRM_Mailing_BAO_Mailing::commonCompose( $form );
        
        if ( $form->_single ) {
            // also fix the user context stack
            if ( $form->_context ) { 
                $url = CRM_Utils_System::url( 'civicrm/dashboard', 'reset=1' );  
            } else {
                $url = 
                    CRM_Utils_System::url('civicrm/contact/view',
                                          "&show=1&action=browse&cid={$form->_contactIds[0]}&selectedChild=activity");
            }
            
            $session   = CRM_Core_Session::singleton( );
            $session->replaceUserContext( $url );
            $form->addDefaultButtons( ts('Send SMS'), 'upload', 'cancel' );
        } else {
            $form->addDefaultButtons( ts('Send SMS'), 'upload' );
        }
        
        $form->addFormRule( array( 'CRM_Contact_Form_Task_SMSCommon', 'formRule' ), $form );

    }

    
    /** 
     * form rule  
     *  
     * @param array $fields    the input form values  
     * @param array $dontCare   
     * @param array $self      additional values form 'this'  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * 
     */  
    static function formRule($fields, $dontCare, $self) 
    {
        $errors = array( );
        
        $template = CRM_Core_Smarty::singleton( );
        
        if ( ! CRM_Utils_Array::value( 'text_message', $fields ) ) {
            $errors['text_message'] = ts('Please provide Text message.');
        } else {
            if ( CRM_Utils_Array::value( 'text_message', $fields ) ) {
                $messageCheck = CRM_Utils_Array::value( 'text_message', $fields );
                if ( $messageCheck && (strlen($messageCheck) > CRM_SMS_Provider::MAX_SMS_CHAR) ) {
                    $errors['text_message'] = ts("You can configure the SMS message body upto"
                                                 . CRM_SMS_Provider::MAX_SMS_CHAR . " characters");
                }
            }
        }
        
        $now = CRM_Utils_Date::format( date('YmdHi00') );
        if( CRM_Utils_Array::value( 'send_at', $fields ) && CRM_Utils_Date::format( CRM_Utils_Date::processDate( $fields['send_at'], $fields['send_at_time'] ) ) < $now ) {
            $errors['send_at'] = ts('Send At date cannot be earlier than the current time.');
        }
        
        if( CRM_Utils_Array::value( 'invalid_after', $fields ) && CRM_Utils_Date::format( CRM_Utils_Date::processDate( $fields['invalid_after'], $fields['invalid_after_time'] ) ) < $now ) {
            $errors['invalid_after'] = ts('Invalid After date cannot be earlier than the current time.');
        }
        
        //Added for CRM-1393
        if ( CRM_Utils_Array::value( 'saveTemplate', $fields ) && empty( $fields['saveTemplateName'] ) ) {
            $errors['saveTemplateName'] = ts("Enter name to save message template");
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    static function postProcess( &$form ) 
    {
  
        // check and ensure that 
        $thisValues = $form->controller->exportValues( $form->getName( ) );
        
        $fromSmsProviderId = $thisValues['sms_provider_id'];
        $thisValues['send_at'] = CRM_Utils_Date::processDate( $thisValues['send_at'], $thisValues['send_at_time'] );
        $thisValues['invalid_after'] = CRM_Utils_Date::processDate( $thisValues['invalid_after'], $thisValues['invalid_after_time'] );
        
        // process message template
        if ( CRM_Utils_Array::value( 'saveTemplate', $thisValues ) 
             || CRM_Utils_Array::value( 'updateTemplate', $thisValues ) ) {
            $messageTemplate = array( 'msg_text'    => $thisValues['text_message'],
                                      'is_active'   => true );
            
            if ( CRM_Utils_Array::value( 'saveTemplate', $thisValues ) ) {
                $messageTemplate['msg_title'] = $thisValues['saveTemplateName'];
                CRM_Core_BAO_MessageTemplates::add( $messageTemplate );
            }
            
            if ( CRM_Utils_Array::value( 'template', $thisValues ) &&
                 CRM_Utils_Array::value( 'updateTemplate', $thisValues ) ) {
                $messageTemplate['id'] = $thisValues['template'];
                unset($messageTemplate['msg_title']);
                CRM_Core_BAO_MessageTemplates::add( $messageTemplate );
            } 
        }
        
        // format contact details array to handle multiple sms from same contact
        $formattedContactDetails = array( );
        $tempPhones = array( );
        
        foreach( $form->_contactIds as $key => $contactId ) {
            $phone = $form->_toContactPhone[ $key ];

            if ( $phone ) {
                $phoneKey = "{$contactId}::{$phone}";
                if ( !in_array( $phoneKey, $tempPhones ) ) {
                    $tempPhones[] = $phoneKey; 
                    if ( CRM_Utils_Array::value($contactId, $form->_contactDetails) ) {
                        $formattedContactDetails[] = $form->_contactDetails[$contactId];
                    }
                }
            }
        }

        // $smsParams carries all the arguments provided on form (or via hooks), to the provider->send() method
        // this gives flexibity to the users / implementors to add their own args via hooks specific to their sms providers
        $smsParams = $thisValues;
        unset($smsParams['text_message'] );
        $smsParams['provider_id'] = $fromSmsProviderId;
   
        list( $sent, $activityId ) = 
            CRM_Activity_BAO_Activity::sendSMS( $formattedContactDetails,
                                                $thisValues,
                                                $smsParams,
                                                array_keys( $form->_contactDetails ) );

        if ( $sent ) {
            $status = array( '', ts('Your message has been sent.') );
        }
        
        //Display the name and number of contacts for those sms is not sent.
        $smsNotSent = array_diff_assoc( $form->_allContactDetails, $form->_contactDetails );
        
        if ( !empty( $smsNotSent ) ) {
            $extraMess = CRM_Utils_System::getClassName( $form ) == 'CRM_Activity_Form_Task_SMS' ? " or the contact is not a target contact to activity of '" . self::RECIEVED_SMS_ACTIVITY_SUBJECT . "' as subject " : "";
            
            $statusDisplay = ts("SMS not sent to contact(s) (No phone no. on file or communication preferences specify DO NOT SMS or Contact is deceased {$extraMess}): %1", array(1 => count($smsNotSent))) . '<br />' . ts('Details') . ': ';
            
            foreach( $smsNotSent as $contactId => $values ) {
                $displayName    = $values['display_name'];
                $phone          = $values['phone'];
                $contactViewUrl = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$contactId}");
                $statusDisplay .= "<a href='{$contactViewUrl}'>{$displayName}</a>, ";
            
            }
            $status[] = $statusDisplay;
        }
       
        if ( !empty($status) ) {
            CRM_Core_Session::setStatus( $status );
        }
    }    
}

