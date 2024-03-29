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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviSeleniumTestCase.php';



class WebTest_Grant_ContactContextAddTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testContactContextAddTest() {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in as admin first to verify permissions for CiviGrant
        $this->webtestLogin( true );        
        
        // Enable CiviGrant module if necessary
        $this->open($this->sboxPath . 'civicrm/admin/setting/component?reset=1');
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent('_qf_Component_next-bottom');
        $enabledComponents = $this->getSelectOptions('enableComponents-t');
        if (! in_array( 'CiviGrant', $enabledComponents ) ) {
            $this->addSelection('enableComponents-f', 'label=CiviGrant');
            $this->click("//option[@value='CiviGrant']");
            $this->click('add');
            $this->click('_qf_Component_next-bottom');
            $this->waitForPageToLoad('30000'); 
            $this->waitForElementPresent('css=div.messages');
            $this->assertTrue($this->isTextPresent('Your changes have been saved.'));          
        }

        // let's give full CiviGrant permissions to demo user (registered user).
        $permission = array('edit-2-access-civigrant','edit-2-edit-grants','edit-2-delete-in-civigrant');
        $this->changePermissions( $permission );
        
        // create unique name
        $name      = substr(sha1(rand()), 0, 7);
        $firstName = 'Grant'.$name;
        $lastName  = 'L'.$name;
        
        // create new contact
        $this->webtestAddContact( $firstName, $lastName );
        
        // wait for action element
        $this->waitForElementPresent('crm-contact-actions-link');
        
        // now add grant from contact summary
        $this->click("//div[@id='crm-contact-actions-link']/span/div");
        
        // wait for add Grant link
        $this->waitForElementPresent('link=Add Grant');
        
        $this->click('link=Add Grant');
        
        // wait for grant form to load completely
        $this->waitForElementPresent('note');
        
        // check contact name on Grant form
        $this->assertTrue($this->isTextPresent("$firstName $lastName"));   
        
        // Let's start filling the form with values.
        
        // select grant Status
        $this->select('status_id', 'value=1');
        
        // select grant type
        $this->select('grant_type_id', 'value=1');
        
        // total amount
        $this->type('amount_total', '200');
        
        // amount requested
        $this->type('amount_requested', '200');
        
        // amount granted
        $this->type('amount_granted', '190');
        
        // fill in application received Date
        $this->webtestFillDate('application_received_date', 'now');
        
        // fill in decision Date
        $this->webtestFillDate('decision_date', 'now');
        
        // fill in money transfered date
        $this->webtestFillDate('money_transfer_date', 'now');
        
        // fill in grant due Date
        $this->webtestFillDate('grant_due_date', 'now');
        
        // check  grant report recieved.
        $this->check('grant_report_received');
        
        // grant rationale
        $this->type('rationale', 'Grant Rationale for webtest');
        
        // grant  note
        $this->type('note', "Grant Note for $firstName");
        
        // Clicking save.
        $this->click('_qf_Grant_upload');
        
        // wait for page to load
        $this->waitForPageToLoad('30000'); 
        
        // verify if grant is created with presence of view link
        $this->waitForElementPresent("xpath=//div[@id='Grants']//table/tbody/tr[1]/td[8]/span/a[text()='View']");
        
        // click through to the Grant view screen
        $this->click("xpath=//div[@id='Grants']//table/tbody/tr[1]/td[8]/span/a[text()='View']");
        $this->waitForElementPresent('_qf_GrantView_cancel-bottom');
        
        $gDate = date('F jS, Y', strtotime('now'));
        
        // verify tabular data for grant view
        $this->webtestVerifyTabularData( array(
                                               'Name'                   => "$firstName $lastName",
                                               'Grant Status'           => 'Pending',
                                               'Grant Type'             => 'Emergency',
                                               'Application Received'   => $gDate,
                                               'Grant Decision'         => $gDate,
                                               'Money Transferred'      => $gDate,
                                               'Grant Report Due'       => $gDate,
                                               'Amount Requested'       => '$ 200.00',
                                               'Amount Granted'         => '$ 190.00',
                                               'Grant Report Received?' => 'Yes',
                                               'Rationale'              => 'Grant Rationale for webtest',
                                               'Notes'                  => "Grant Note for $firstName"
                                               )
                                         );
        
    }
}