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
 * This class generates form components for processing a pcp page creati 
 * 
 */

class CRM_PCP_Form_Campaign extends CRM_Core_Form
{
    public $_context;
    public $_component;
    
    public function preProcess()  
    {
        // we do not want to display recently viewed items, so turn off
        $this->assign( 'displayRecent' , false );
        
        // component null in controller object - fix? dgg
        // $this->_component = $this->controller->get('component');
        $this->_component = CRM_Utils_Request::retrieve( 'component', 'String', $this );
        $this->assign( 'component', $this->_component );
        
        $this->_context = CRM_Utils_Request::retrieve('context', 'String', $this );
        $this->assign( 'context', $this->_context );
        
        $this->_pageId = CRM_Utils_Request::retrieve( 'id', 'Positive', $this, false );        
        $title = ts( 'Setup a Personal Campaign Page - Step 2' );
        
        if( $this->_pageId ) {
            $title = ts( 'Edit Your Personal Campaign Page' );
        }

        CRM_Utils_System::setTitle( $title );
        parent::preProcess( );
    }

    function setDefaultValues( ) 
    {
        $dafaults = array( );
        $dao = new CRM_PCP_DAO_PCP( );
        
        if( $this->_pageId ) {
            $dao->id = $this->_pageId;
            if ( $dao->find(true) ) {
                CRM_Core_DAO::storeValues( $dao, $defaults );
            }
            // fix the display of the monetary value, CRM-4038
            if (isset($defaults['goal_amount'])) {
                $defaults['goal_amount'] = CRM_Utils_Money::format($defaults['goal_amount'], null, '%a');
            }
        }
        
        if ( $this->get('action') & CRM_Core_Action::ADD ) {
            $defaults['is_active'] = 1;
            $defaults['is_honor_roll'] = 1;
            $defaults['is_thermometer'] = 1;
        }
        
        $this->_contactID    = CRM_Utils_Array::value( 'contact_id', $defaults );
        $this->_contriPageId = CRM_Utils_Array::value( 'page_id', $defaults );
         
        return $defaults;
    }
    
    
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    {
        $this->add('text', 'title', ts('Title'), null, true );
        $this->add('textarea', 'intro_text', ts('Welcome'), null, true );
        $this->add('text', 'goal_amount', ts('Your Goal'), null, true );
        $this->addRule( 'goal_amount', ts('Goal Amount should be a numeric value'), 'money' );
        
        $attributes = array( );
        if ( $this->_component == 'event') {
          if ( $this->get('action') & CRM_Core_Action::ADD ) {
              $attributes = array('value' => ts('Join Us'), 'onClick' => 'select();');
          }
          $this->add('text', 'donate_link_text', ts('Sign up Button'), $attributes);
        }
        else {
          if ( $this->get('action') & CRM_Core_Action::ADD ) {
              $attributes = array('value' => ts('Donate Now'), 'onClick' => 'select();');
          }
          $this->add('text', 'donate_link_text', ts('Donation Button'), $attributes);
        }
        
        $attrib = Array ('rows' => 8, 'cols' => 60 );
        $this->addWysiwyg( 'page_text', ts('Your Message'), $attrib ); 
        $maxAttachments = 1; 
        CRM_Core_BAO_File::buildAttachment( $this, 'civicrm_pcp', $this->_pageId, $maxAttachments );
        
        $this->addElement( 'checkbox', 'is_thermometer', ts('Progress Bar') );
        $this->addElement( 'checkbox', 'is_honor_roll', ts('Honor Roll'), null);
        $this->addElement( 'checkbox', 'is_active', ts('Active') );

        $this->addButtons( array(
                                 array ( 'type'      => 'upload',
                                         'name'      => ts('Save'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        $this->addFormRule( array( 'CRM_PCP_Form_Campaign', 'formRule' ), $this );
    }
    
    /**  
     * global form rule  
     *  
     * @param array $fields  the input form values  
     * @param array $files   the uploaded files if any  
     * @param array $options additional user data  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * @static  
     */  
    static function formRule( $fields, $files, $self ) 
    {
        $errors = array();
        if ( $fields['goal_amount'] <= 0 ) {
            $errors['goal_amount'] = ts('Goal Amount should be a numeric value greater than zero.');
        }
        if ( strlen($fields['donate_link_text']) >= 64 ){
            $errors['donate_link_text'] = ts('Button Text must be less than 64 characters.');
        }
        if ( isset($files['attachFile_1']) &&
             CRM_Utils_Array::value( 'tmp_name', $files['attachFile_1'] ) ) {
            list( $width, $height ) = getimagesize( $files['attachFile_1']['tmp_name'] );
            if ( $width > 360 || $height > 360 ) {
                $errors['attachFile_1'] = ts( "Your picture or image file cannot be larger than 360 x 360 pixels in size." ) . ' '
                    . ts( "The dimensions of the image you've selected is %1 x %2.", array( 1 => $width, 2 => $height ) ) . ' '
                    . ts( "Please shrink or crop the file or find another smaller image and try again." );
            }
        }       
        return $errors;
    }
    
    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    public function postProcess( )  
    {
        $params  = $this->controller->exportValues( );
        $checkBoxes = array( 'is_thermometer', 'is_honor_roll', 'is_active' );
        
        foreach( $checkBoxes as $key ) {
            if ( ! isset( $params[$key] ) ) {
                $params[$key] = 0;
            }
        }
        $session = CRM_Core_Session::singleton( );
        $contactID = isset( $this->_contactID ) ? $this->_contactID : $session->get('userID');
        if( ! $contactID ) {
            $contactID = $this->get('contactID');
        }
        $params['contact_id'] = $contactID;
        $params['page_id'] = $this->get('component_page_id') ? $this->get('component_page_id') : $this->_contriPageId;
        $params['page_type'] = $this->_component;


        $entity_table = CRM_PCP_BAO_PCP::getPcpEntityTable( $params['page_type'] );
             
        $pcpBlock = new CRM_PCP_DAO_PCPBlock();
        $pcpBlock->entity_table = $entity_table;
        $pcpBlock->entity_id = $params['page_id'];
        $pcpBlock->find(true);
        
        $params['pcp_block_id'] = $pcpBlock->id;
        
        $params['goal_amount'] = CRM_Utils_Rule::cleanMoney( $params['goal_amount'] );

        $approval_needed = $pcpBlock->is_approval_needed;
        $approvalMessage = null;
        
        if ( $this->get('action') & CRM_Core_Action::ADD ) {
            $params['status_id'] = $approval_needed ? 1 : 2;
            $approvalMessage     = $approval_needed ? ts('but requires administrator review before you can begin promoting your campaign. You will receive an email confirmation shortly which includes a link to return to this page.') : ts('and is ready to use.');
        }
        
        $params['id'] = $this->_pageId;

        $pcp = CRM_PCP_BAO_PCP::add( $params, false );


        // add attachments as needed
        CRM_Core_BAO_File::formatAttachment( $params,
                                             $params,
                                             'civicrm_pcp',
                                             $pcp->id );

        $pageStatus = isset( $this->_pageId ) ? ts('updated') : ts('created');
        $statusId = CRM_Core_DAO::getFieldValue( 'CRM_PCP_DAO_PCP', $pcp->id, 'status_id' );
     
        //send notification of PCP create/update.
        $pcpParams   = array( 'entity_table' => $entity_table, 'entity_id' => $pcp->page_id );
        $notifyParams = array( );
        $notifyStatus = "";
        CRM_Core_DAO::commonRetrieve('CRM_PCP_DAO_PCPBlock', $pcpParams, $notifyParams, array('notify_email'));

        if ( $emails = $pcpBlock->notify_email ) {
            $this->assign( 'pcpTitle', $pcp->title );
            
            if( $this->_pageId ) {
                $this->assign ( 'mode', 'Update');
            } else {
                $this->assign ( 'mode', 'Add');
            }
            $pcpStatus = CRM_Core_OptionGroup::getLabel( 'pcp_status', $statusId );
            $this->assign( 'pcpStatus', $pcpStatus );
            
            $this->assign( 'pcpId', $pcp->id );
            
            $supporterUrl = CRM_Utils_System::url( "civicrm/contact/view",
                                                   "reset=1&cid={$pcp->contact_id}",
                                                   true, null, false,
                                                   false );
            $this->assign( 'supporterUrl', $supporterUrl );
            $supporterName = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $pcp->contact_id, 'display_name' );
            $this->assign( 'supporterName', $supporterName );
           

            if ( $this->_component == 'contribute' ) {
                $pageUrl   = CRM_Utils_System::url( "civicrm/contribute/transact",
                                                    "reset=1&id={$pcpBlock->entity_id}",
                                                    true, null, false,
                                                    true );
                $contribPageTitle = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $pcpBlock->entity_id, 'title' );
            } else if ( $this->_component == 'event' ) {
                $pageUrl   = CRM_Utils_System::url( "civicrm/event",
                                                    "reset=1&id={$pcpBlock->entity_id}",
                                                    true, null, false,
                                                    true );
                $contribPageTitle = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event', $pcpBlock->entity_id, 'title' );
            }

            $this->assign( 'contribPageUrl', $pageUrl );
            $this->assign( 'contribPageTitle', $contribPageTitle );
            
            $managePCPUrl =  CRM_Utils_System::url( "civicrm/admin/pcp",
                                                    "reset=1",
                                                    true, null, false,
                                                    false );
            $this->assign( 'managePCPUrl', $managePCPUrl );
            
            //get the default domain email address.
            list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );
            
            if ( !$domainEmailAddress || $domainEmailAddress == 'info@FIXME.ORG') {
                $fixUrl = CRM_Utils_System::url("civicrm/admin/domain", 'action=update&reset=1');
                CRM_Core_Error::fatal( ts( 'The site administrator needs to enter a valid \'FROM Email Address\' in <a href="%1">Administer CiviCRM &raquo; Configure &raquo; Domain Information</a>. The email address used may need to be a valid mail account with your email service provider.', array( 1 => $fixUrl ) ) );
            }
          
            //if more than one email present for PCP notification ,
            //first email take it as To and other as CC and First email
            //address should be sent in users email receipt for
            //support purpose.
            $emailArray = explode(',' ,$emails );
            $to = $emailArray[0];
            unset( $emailArray[0] );
            $cc = implode(',', $emailArray );

            list ($sent, $subject, $message, $html) = CRM_Core_BAO_MessageTemplates::sendTemplate(
                array(
                    'groupName' => 'msg_tpl_workflow_contribution',
                    'valueName' => 'pcp_notify',
                    'contactId' => $contactID,
                    'from'      => "$domainEmailName <$domainEmailAddress>",
                    'toEmail'   => $to,
                    'cc'        => $cc,
                )
            );
            
            if ($sent) {
                $notifyStatus = ts('A notification email has been sent to the site administrator.'); 
            }
        }
        
        CRM_Core_BAO_File::processAttachment( $params, 'civicrm_pcp', $pcp->id );

        // send email notification to supporter, if initial setup / add mode.
        if ( ! $this->_pageId ) {
            CRM_PCP_BAO_PCP::sendStatusUpdate( $pcp->id, $statusId, true, $this->_component );
            if ( $approvalMessage && CRM_Utils_Array::value( 'status_id', $params ) == 1 ) {
                $notifyStatus .= ts(' You will receive a second email as soon as the review process is complete.');
            }
        }

        //check if pcp created by anonymous user
        $anonymousPCP = 0;
        if ( !$session->get('userID') ) {
            $anonymousPCP = 1; 
        }

        CRM_Core_Session::setStatus( ts( "Your Personal Campaign Page has been %1 %2 %3", 
                                         array(1 => $pageStatus, 2 => $approvalMessage, 3 => $notifyStatus)) );
        if ( ! $this->_pageId ) {
            $session->pushUserContext( CRM_Utils_System::url( 'civicrm/pcp/info', "reset=1&id={$pcp->id}&ap={$anonymousPCP}" ) );
        } elseif ( $this->_context == 'dashboard' ) {
            $session->pushUserContext( CRM_Utils_System::url( 'civicrm/admin/pcp', "reset=1" ) );
        }
    }
}
