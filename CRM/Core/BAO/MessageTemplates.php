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

require_once 'Mail/mime.php';


class CRM_Core_BAO_MessageTemplates extends CRM_Core_DAO_MessageTemplates 
{
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_BAO_MessageTemplates object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $messageTemplates = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->copyValues( $params );
        if ( $messageTemplates->find( true ) ) {
            CRM_Core_DAO::storeValues( $messageTemplates, $defaults );
            return $messageTemplates;
        }
        return null;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_MessageTemplates', $id, 'is_active', $is_active );
    }

    /**
     * function to add the Message Templates
     *
     * @param array $params reference array contains the values submitted by the form
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add( &$params ) 
    {
        $params['is_active']            =  CRM_Utils_Array::value( 'is_active', $params, false );

        $messageTemplates               = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->copyValues( $params );
        
        $messageTemplates->save( );
        return $messageTemplates;
    }

    /**
     * function to delete the Message Templates
     *
     * @access public
     * @static 
     * @return object
     */
    static function del( $messageTemplatesID ) 
    {
        // make sure messageTemplatesID is an integer
        if ( ! CRM_Utils_Rule::positiveInteger( $messageTemplatesID ) ) {
            CRM_Core_Error::fatal( ts( 'Invalid Message template' ) );
        }
        
        // set membership_type to null
        $query = "UPDATE civicrm_membership_type
                  SET renewal_msg_id = NULL
                  WHERE renewal_msg_id = %1";
        $params = array( 1 => array( $messageTemplatesID, 'Integer' ) );
        CRM_Core_DAO::executeQuery( $query, $params );
        
        $query = "UPDATE civicrm_mailing
                  SET msg_template_id = NULL
                  WHERE msg_template_id = %1";
        CRM_Core_DAO::executeQuery( $query, $params );
        
        $messageTemplates = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->id = $messageTemplatesID;
        $messageTemplates->delete();
        CRM_Core_Session::setStatus( ts('Selected message templates has been deleted.') );
    }
    
    /**
     * function to get the Message Templates
     *
     * @access public
     * @static 
     * @return object
     */
    static function getMessageTemplates( $all = true) {
        $msgTpls =array();

        $messageTemplates = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->is_active = 1;
        
        if ( ! $all ) {
            $messageTemplates->workflow_id = 'NULL';
        } 
        $messageTemplates->find();
        while ( $messageTemplates->fetch() ) {
            $msgTpls[$messageTemplates->id] = $messageTemplates->msg_title;
        }
        asort($msgTpls);
        return $msgTpls;
    }

    static function sendReminder( $contactId, $email, $messageTemplateID ,$from) {

        $messageTemplates = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->id = $messageTemplateID;

        $domain = CRM_Core_BAO_Domain::getDomain( );
        $result = null;
        $hookTokens = array();
        
        if ( $messageTemplates->find(true) ) {
            $body_text = $messageTemplates->msg_text;
            $body_html = $messageTemplates->msg_html;
            $body_subject = $messageTemplates->msg_subject;
            if (!$body_text) {
                $body_text = CRM_Utils_String::htmlToText($body_html);
            }
            
            $params = array(array('contact_id', '=', $contactId, 0, 0));
            list($contact, $_) = CRM_Contact_BAO_Query::apiQuery($params);

            //CRM-4524
            $contact = reset( $contact );
            
            if ( !$contact || is_a( $contact, 'CRM_Core_Error' ) ) {
                return null;
            }

            //CRM-5734
            
            $contactArray = array( $contactId => $contact );
            CRM_Utils_Hook::tokenValues( $contactArray,
                                         array( $contactId ) );
                
            
            CRM_Utils_Hook::tokens( $hookTokens );
            $categories = array_keys( $hookTokens );
            
            $type = array('html', 'text');
            
            foreach( $type as $key => $value ) {
                $dummy_mail = new CRM_Mailing_BAO_Mailing();
                $bodyType = "body_{$value}";
                $dummy_mail->$bodyType = $$bodyType;
                $tokens = $dummy_mail->getTokens();
                
                if ( $$bodyType ) {
                    CRM_Utils_Token::replaceGreetingTokens( $$bodyType, null, $contact['contact_id'] );
                    $$bodyType = CRM_Utils_Token::replaceDomainTokens($$bodyType, $domain, true, $tokens[$value], true );
                    $$bodyType = CRM_Utils_Token::replaceContactTokens($$bodyType, $contact, false, $tokens[$value], false, true );
                    $$bodyType = CRM_Utils_Token::replaceComponentTokens($$bodyType, $contact, $tokens[$value], true );
                    $$bodyType = CRM_Utils_Token::replaceHookTokens ( $$bodyType, $contact , $categories, true );
                }
            }
            $html = $body_html;
            $text = $body_text;
            
            require_once 'CRM/Core/Smarty/resources/String.php';
            civicrm_smarty_register_string_resource( );
            $smarty = CRM_Core_Smarty::singleton( );
            foreach( array( 'text', 'html') as $elem) {
                $$elem = $smarty->fetch("string:{$$elem}");
            }
            
            $matches = array();
            preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                            $body_subject,
                            $matches,
                            PREG_PATTERN_ORDER);
            
            $subjectToken = null;
            if ( $matches[1] ) {
                foreach ( $matches[1] as $token ) {
                    list($type,$name) = preg_split( '/\./', $token, 2 );
                    if ( $name ) {
                        if ( ! isset( $subjectToken['contact'] ) ) {
                            $subjectToken['contact'] = array( );
                        }
                        $subjectToken['contact'][] = $name;
                    }
                }
            }
            
            $messageSubject = CRM_Utils_Token::replaceContactTokens($body_subject, $contact, false, $subjectToken);
            $messageSubject = CRM_Utils_Token::replaceDomainTokens($messageSubject, $domain, true, $tokens[$value] );
            $messageSubject = CRM_Utils_Token::replaceComponentTokens($messageSubject, $contact, $tokens[$value], true );
            $messageSubject = CRM_Utils_Token::replaceHookTokens ( $messageSubject, $contact, $categories, true );
          
            $messageSubject = $smarty->fetch("string:{$messageSubject}");
            
            // set up the parameters for CRM_Utils_Mail::send
            $mailParams = array(
                                'groupName' => 'Scheduled Reminder Sender',
                                'from'      => $from,
                                'toName'    => $contact['display_name'],
                                'toEmail'   => $email,
                                'subject'   => $messageSubject,
            );
            if ( !$html || $contact['preferred_mail_format'] == 'Text' ||
                 $contact['preferred_mail_format'] == 'Both') {
            	// render the &amp; entities in text mode, so that the links work
            	$mailParams['text'] = str_replace('&amp;', '&', $text);
            }
            if ($html && ( $contact['preferred_mail_format'] == 'HTML' ||
                $contact['preferred_mail_format'] == 'Both')) {
            	$mailParams['html'] = $html;
            }
            
            $result = CRM_Utils_Mail::send( $mailParams );
        }

        $messageTemplates->free( );
        
        return $result;
    }

    /**
     * Revert a message template to its default subject+text+HTML state
     *
     * @param integer id  id of the template
     *
     * @return void
     */
    static function revert($id)
    {
        $diverted = new self;
        $diverted->id = (int) $id;
        $diverted->find(1);

        if ($diverted->N != 1) {
            CRM_Core_Error::fatal(ts('Did not find a message template with id of %1.', array(1 => $id)));
        }

        $orig = new self;
        $orig->workflow_id = $diverted->workflow_id;
        $orig->is_reserved = 1;
        $orig->find(1);

        if ($orig->N != 1) {
            CRM_Core_Error::fatal(ts('Message template with id of %1 does not have a default to revert to.', array(1 => $id)));
        }

        $diverted->msg_subject = $orig->msg_subject;
        $diverted->msg_text    = $orig->msg_text;
        $diverted->msg_html    = $orig->msg_html;
        $diverted->pdf_format_id = is_null( $orig->pdf_format_id ) ? 'null' : $orig->pdf_format_id;
        $diverted->save();
    }

    /**
     * Send an email from the specified template based on an array of params
     *
     * @param array $params  a string-keyed array of function params, see function body for details
     *
     * @return array  of four parameters: a boolean whether the email was sent, and the subject, text and HTML templates
     */
    static function sendTemplate($params)
    {
        $defaults = array(
            'groupName'         => null,    // option group name of the template
            'valueName'         => null,    // option value name of the template
            'messageTemplateID' => null,    // ID of the template
            'contactId'         => null,    // contact id if contact tokens are to be replaced
            'tplParams'         => array(), // additional template params (other than the ones already set in the template singleton)
            'from'              => null,    // the From: header
            'toName'            => null,    // the recipient’s name
            'toEmail'           => null,    // the recipient’s email - mail is sent only if set
            'cc'                => null,    // the Cc: header
            'bcc'               => null,    // the Bcc: header
            'replyTo'           => null,    // the Reply-To: header
            'attachments'       => null,    // email attachments
            'isTest'            => false,   // whether this is a test email (and hence should include the test banner)
            'PDFFilename'       => null,    // filename of optional PDF version to add as attachment (do not include path)
        );
        $params = array_merge($defaults, $params);
                
        if ( ( ! $params['groupName'] ||
               ! $params['valueName'] ) &&
             ! $params['messageTemplateID'] ) {
            CRM_Core_Error::fatal(ts("Message template's option group and/or option value or ID missing."));
        }

        if ($params['messageTemplateID']) {
            // fetch the three elements from the db based on id
            $query = 'SELECT msg_subject subject, msg_text text, msg_html html, pdf_format_id format
                      FROM civicrm_msg_template mt
                      WHERE mt.id = %1 AND mt.is_default = 1';
            $sqlParams = array(1 => array($params['messageTemplateID'], 'String'));
        } else {
            // fetch the three elements from the db based on option_group and option_value names
            $query = 'SELECT msg_subject subject, msg_text text, msg_html html, pdf_format_id format
                      FROM civicrm_msg_template mt
                      JOIN civicrm_option_value ov ON workflow_id = ov.id
                      JOIN civicrm_option_group og ON ov.option_group_id = og.id
                      WHERE og.name = %1 AND ov.name = %2 AND mt.is_default = 1';
            $sqlParams = array(1 => array($params['groupName'], 'String'), 2 => array($params['valueName'], 'String'));
        }
        $dao = CRM_Core_DAO::executeQuery($query, $sqlParams);
        $dao->fetch();

        if (!$dao->N) {
            if ($params['messageTemplateID']) {
                CRM_Core_Error::fatal(ts('No such message template: id=%1.', array(1 => $params['messageTemplateID'])));
            } else {
                CRM_Core_Error::fatal(ts('No such message template: option group %1, option value %2.', array(1 => $params['groupName'], 2 => $params['valueName'])));
            }
        }

        $subject = $dao->subject;
        $text    = $dao->text;
        $html    = $dao->html;
        $format  = $dao->format;
        $dao->free( );

        // add the test banner (if requested)
        if ($params['isTest']) {
            $query = "SELECT msg_subject subject, msg_text text, msg_html html
                      FROM civicrm_msg_template mt
                      JOIN civicrm_option_value ov ON workflow_id = ov.id
                      JOIN civicrm_option_group og ON ov.option_group_id = og.id
                      WHERE og.name = 'msg_tpl_workflow_meta' AND ov.name = 'test_preview' AND mt.is_default = 1";
            $testDao = CRM_Core_DAO::executeQuery($query);
            $testDao->fetch();

            $subject = $testDao->subject . $subject;
            $text    = $testDao->text    . $text;
            $html    = preg_replace('/<body(.*)$/im', "<body\\1\n{$testDao->html}", $html);
            $testDao->free( );
        }

        // replace tokens in the three elements (in subject as if it was the text body)

        $domain = CRM_Core_BAO_Domain::getDomain();
        $hookTokens = array();
        $mailing = new CRM_Mailing_BAO_Mailing;
        $mailing->body_text = $text;
        $mailing->body_html = $html;
        $tokens = $mailing->getTokens();
        CRM_Utils_Hook::tokens( $hookTokens );
        $categories = array_keys( $hookTokens );
        
        $contactID = CRM_Utils_Array::value( 'contactId', $params );

        if ( $contactID ) {
            $contactParams = array('contact_id' => $contactID );
            $returnProperties = array( );

            if ( isset( $tokens['text']['contact'] ) ) {
                foreach ( $tokens['text']['contact'] as $name ) {
                    $returnProperties[$name] = 1;
                }
            }

            if ( isset( $tokens['html']['contact'] ) ) {
                foreach ( $tokens['html']['contact'] as $name ) {
                    $returnProperties[$name] = 1;
                }
            }
            list( $contact ) = CRM_Utils_Token::getTokenDetails($contactParams,
                                                                $returnProperties,
                                                                false, false, null,
                                                                CRM_Utils_Token::flattenTokens( $tokens ),
                                                                // we should consider adding groupName and valueName here
                                                                'CRM_Core_BAO_MessageTemplate' );
            $contact = $contact[$contactID];
        }

        $subject = CRM_Utils_Token::replaceDomainTokens($subject, $domain, true, $tokens['text'], true);
        $text    = CRM_Utils_Token::replaceDomainTokens($text,    $domain, true, $tokens['text'], true);
        $html    = CRM_Utils_Token::replaceDomainTokens($html,    $domain, true, $tokens['html'], true);

        if ( $contactID ) {
            $subject = CRM_Utils_Token::replaceContactTokens($subject, $contact, false, $tokens['text'], false, true);
            $text    = CRM_Utils_Token::replaceContactTokens($text,    $contact, false, $tokens['text'], false, true);
            $html    = CRM_Utils_Token::replaceContactTokens($html,    $contact, false, $tokens['html'], false, true);


            $contactArray = array( $contactID => $contact );
            CRM_Utils_Hook::tokenValues( $contactArray, 
                                         array( $contactID ),
                                         null,
                                         CRM_Utils_Token::flattenTokens( $tokens ),
                                         // we should consider adding groupName and valueName here
                                         'CRM_Core_BAO_MessageTemplate' );
            $contact = $contactArray[$contactID];

            $subject = CRM_Utils_Token::replaceHookTokens ( $subject, $contact , $categories, true );
            $text = CRM_Utils_Token::replaceHookTokens ( $text, $contact , $categories, true );
            $html = CRM_Utils_Token::replaceHookTokens ( $html, $contact, $categories, true );
      
        }

        // strip whitespace from ends and turn into a single line
        $subject = "{strip}$subject{/strip}";

        // parse the three elements with Smarty
        require_once 'CRM/Core/Smarty/resources/String.php';
        civicrm_smarty_register_string_resource();

        $smarty = CRM_Core_Smarty::singleton();
        foreach ($params['tplParams'] as $name => $value) {
            $smarty->assign($name, $value);
        }
        foreach (array('subject', 'text', 'html') as $elem) {
            $$elem = $smarty->fetch("string:{$$elem}");
        }

        // send the template, honouring the target user’s preferences (if any)
        $sent = false;

        // create the params array
        $params['subject'] = $subject;
        $params['text'   ] = $text;
        $params['html'   ] = $html;
        
        if ($params['toEmail']) {
            $contactParams = array(array('email', 'LIKE', $params['toEmail'], 0, 1));
            list($contact, $_) = CRM_Contact_BAO_Query::apiQuery($contactParams);

            $prefs = array_pop($contact);

            if ( isset($prefs['preferred_mail_format']) and $prefs['preferred_mail_format'] == 'HTML' ) {
                $params['text'] = null;
            }

            if ( isset($prefs['preferred_mail_format']) and $prefs['preferred_mail_format'] == 'Text' ) {
                $params['html'] = null;
            }

            $config = CRM_Core_Config::singleton();
            $pdf_filename = '';
            if ( $config->doNotAttachPDFReceipt && 
                 $params['PDFFilename'] && 
                 $params['html'] ) {
                $pdf_filename = $config->templateCompileDir . CRM_Utils_File::makeFileName( $params['PDFFilename'] );
                
                //FIXME : CRM-7894
                //xmlns attribute is required in XHTML but it is invalid in HTML, 
                //Also the namespace "xmlns=http://www.w3.org/1999/xhtml" is default, 
                //and will be added to the <html> tag even if you do not include it. 
                $html = preg_replace( '/(<html)(.+?xmlns=["\'].[^\s]+["\'])(.+)?(>)/', '\1\3\4', $params['html'] );
                
                file_put_contents( $pdf_filename, CRM_Utils_PDF_Utils::html2pdf( $html,
                                                                                 $params['PDFFilename'],
                                                                                 true,
                                                                                 $format
                                                                               )
                                 );
                                 
			    if ( empty( $params['attachments'] ) ) {
			        $params['attachments'] = array();
			    }
			    $params['attachments'][] = array(
			        'fullPath' => $pdf_filename,
			        'mime_type' => 'application/pdf',
			        'cleanName' => $params['PDFFilename'],
			    );
            }
            
            $sent = CRM_Utils_Mail::send( $params );

            if ( $pdf_filename ) {
                unlink($pdf_filename);
            }
        }

        return array($sent, $subject, $text, $html);
    }
}
