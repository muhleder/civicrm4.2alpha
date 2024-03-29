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


 
class WebTest_Campaign_MailingTest extends CiviSeleniumTestCase {

    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
    protected function setUp()
    {
        parent::setUp();
    }

    function testCreateCampaign()
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in as admin first to verify permissions for CiviCampaign
        $this->webtestLogin( true );        

        // Enable CiviCampaign module if necessary
        $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent("_qf_Component_next-bottom");
        $enabledComponents = $this->getSelectOptions("enableComponents-t");
        if (! in_array( "CiviCampaign", $enabledComponents ) ) {
            $this->addSelection("enableComponents-f", "label=CiviCampaign");
            $this->click("//option[@value='CiviCampaign']");
            $this->click("add");
            $this->click("_qf_Component_next-bottom");
            $this->waitForPageToLoad("30000");          
            $this->assertTrue($this->isTextPresent("Your changes have been saved."));    
        }
        
        // add the required Drupal permission
        $permissions = array('edit-2-administer-civicampaign');
        $this->changePermissions( $permissions );
                
        // Create new group
        $title = substr(sha1(rand()), 0, 7);
        $groupName = $this->WebtestAddGroup( );
        
        // Adding contact
        // We're using Quick Add block on the main page for this.
        $firstName = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName, "Smith", "$firstName.smith@example.org" );
        
        // add contact to group
        // visit group tab
        $this->click("css=li#tab_group a");
        $this->waitForElementPresent("group_id");
        
        // add to group
        $this->select("group_id", "label=$groupName");
        $this->click("_qf_GroupContact_next");
        $this->waitForPageToLoad("30000");
        
        // Go directly to the URL of the screen that you will be testing
        $this->open($this->sboxPath . "civicrm/campaign/add?reset=1");
        
        // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent("_qf_Campaign_upload-bottom");
        
        // Let's start filling the form with values.
        
        $campaignTitle = "Campaign $title";
        $this->type( "title", $campaignTitle );
        
        // select the campaign type
        $this->select("campaign_type_id", "value=2");
        
        // fill in the description
        $this->type("description", "This is a test campaign");
        
        // include groups for the campaign
        $this->addSelection("includeGroups-f", "label=$groupName");
        $this->click("//option[@value=4]");
        $this->click("add");
        
        // fill the end date for campaign
        $this->webtestFillDate("end_date", "+1 year");
        
        // select campaign status
        $this->select("status_id", "value=2");
        
        // click save
        $this->click("_qf_Campaign_upload-bottom");
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue($this->isTextPresent("Campaign Campaign $title has been saved."), 
                          "Status message didn't show up after saving campaign!");
        
        $this->waitForElementPresent("//div[@id='campaignList']/div[@class='dataTables_wrapper']/table/tbody/tr/td[text()='{$campaignTitle}']/../td[1]");
        $id = (int) $this->getText("//div[@id='campaignList']/div[@class='dataTables_wrapper']/table/tbody/tr/td[text()='{$campaignTitle}']/../td[1]");
        $this->mailingAddTest( $groupName, $campaignTitle, $id );
    }

    function mailingAddTest( $groupName, $campaignTitle, $id ) 
    {
        //---- create mailing contact and add to mailing Group
        $firstName = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName, "Mailson", "mailino$firstName@mailson.co.in" );
        
        // go to group tab and add to mailing group
        $this->click("css=li#tab_group a");
        $this->waitForElementPresent("_qf_GroupContact_next");
        $this->select("group_id", "$groupName");
        $this->click("_qf_GroupContact_next");
        
        // configure default mail-box
        $this->open( $this->sboxPath . "civicrm/admin/mailSettings?action=update&id=1&reset=1" );
        $this->waitForElementPresent( '_qf_MailSettings_cancel-bottom' );
        $this->type( 'name', 'Test Domain' );
        $this->type( 'domain', 'example.com' );
        $this->select( 'protocol', 'value=1' );
        $this->click( '_qf_MailSettings_next-bottom' );
        $this->waitForPageToLoad("30000");

        // Go directly to Schedule and Send Mailing form
        $this->open($this->sboxPath . "civicrm/mailing/send?reset=1");
        $this->waitForElementPresent("_qf_Group_cancel");
              
        //-------select recipients----------
        
        // fill mailing name
        $mailingName = substr(sha1(rand()), 0, 7);
        $this->type("name", "Mailing $mailingName Webtest");
        
        // select campaign
        $this->click("campaign_id");
        $this->select("campaign_id", "value=$id" );
        
        // Add the test mailing group
        $this->select("includeGroups-f", "$groupName");
        $this->click("add");
        
        // click next
        $this->click("_qf_Group_next");
        $this->waitForElementPresent("_qf_Settings_cancel");
        
        //--------track and respond----------
        
        // check for default settings options
        $this->assertChecked("url_tracking");
        $this->assertChecked("open_tracking");
        
        // do check count for Recipient
        $this->assertTrue($this->isTextPresent("Total Recipients: 2"));
        
        // no need tracking for this test      
        
        // click next with default settings
        $this->click("_qf_Settings_next");
        $this->waitForElementPresent("_qf_Upload_cancel");
        
        //--------Mailing content------------
        // let from email address be default
        
        // fill subject for mailing
        $this->type("subject", "Test subject $mailingName for Webtest");
        
        // check for default option enabled
        $this->assertChecked("CIVICRM_QFID_1_4");
        
        // fill message (presently using script for simple text area)
        $this->click("//fieldset[@id='compose_id']/div[2]/div[1]");
        $this->type("text_message", "this is test content for Mailing $mailingName Webtest");
        
        // add attachment?

        // default header and footer ( with label ) 
        $this->select("header_id", "label=Mailing Header");
        $this->select("footer_id", "label=Mailing Footer");
        
        // do check count for Recipient
        $this->assertTrue($this->isTextPresent("Total Recipients: 2"));
        
        // click next with nominal content
        $this->click("_qf_Upload_upload");
        $this->waitForElementPresent("_qf_Test_cancel");
        
        //---------------Test------------------

        ////////--Commenting test mailing and mailing preview (test mailing and preview not presently working).
        
        // send test mailing
        //$this->type("test_email", "mailino@mailson.co.in");
        //$this->click("sendtest");
        
        // verify status message 
        //$this->assertTrue($this->isTextPresent("Your test message has been sent. Click 'Next' when you are ready to Schedule or Send your live mailing (you will still have a chance to confirm or cancel sending this mailing on the next page)."));
        
        // check mailing preview 
        //$this->click("//form[@id='Test']/div[2]/div[4]/div[1]");
        //$this->assertTrue($this->isTextPresent("this is test content for Mailing $mailingName Webtest"));
        
        ////////
        
        // do check count for Recipient
        $this->assertTrue($this->isTextPresent("Total Recipients: 2"));
        
        // click next
        $this->click("_qf_Test_next");
        $this->waitForElementPresent("_qf_Schedule_cancel");      
        
        //----------Schedule or Send------------
        
        // do check for default option enabled
        $this->assertChecked("now");
        
        // do check count for Recipient
        $this->assertTrue($this->isTextPresent("Total Recipients: 2"));
        
        // finally schedule the mail by clicking submit
        $this->click("_qf_Schedule_next");
        $this->waitForPageToLoad("30000");
        
        //----------end New Mailing-------------
        
        //check redirected page to Scheduled and Sent Mailings and  verify for mailing name
        $this->assertTrue($this->isTextPresent("Scheduled and Sent Mailings"));
        $this->assertTrue($this->isTextPresent("Mailing $mailingName Webtest"));
                
        //--------- mail delivery verification---------
        
        // test undelivered report
        
        // click report link of created mailing
        $this->click("xpath=//table//tbody/tr[td[1]/text()='Mailing $mailingName Webtest']/descendant::a[text()='Report']");
        $this->waitForPageToLoad("30000");
        
        // verify undelivered status message
        $this->assertTrue($this->isTextPresent("Delivery has not yet begun for this mailing. If the scheduled delivery date and time is past, ask the system administrator or technical support contact for your site to verify that the automated mailer task ('cron job') is running - and how frequently."));
        
        // do check for recipient group
        $this->assertTrue($this->isTextPresent("Members of $groupName"));
        
        // directly send schedule mailing -- not working right now
        $this->open($this->sboxPath . "civicrm/mailing/queue?reset=1");
        $this->waitForPageToLoad("300000");
        
        //click report link of created mailing
        $this->click("xpath=//table//tbody/tr[td[1]/text()='Mailing $mailingName Webtest']/descendant::a[text()='Report']");
        $this->waitForPageToLoad("30000");
        
        // do check again for recipient group
        $this->assertTrue($this->isTextPresent("Members of $groupName"));
        
        // check for 100% delivery
        $this->assertTrue($this->isTextPresent("2 (100.00%)"));
        
        // verify intended recipients
        $this->verifyText("xpath=//table//tr[td/a[text()='Intended Recipients']]/descendant::td[2]", preg_quote("2"));
        
        // verify successful deliveries
        $this->verifyText("xpath=//table//tr[td/a[text()='Successful Deliveries']]/descendant::td[2]", preg_quote("2 (100.00%)"));
        
        // verify status
        $this->verifyText("xpath=//table//tr[td[1]/text()='Status']/descendant::td[2]", preg_quote("Complete"));
        
        // verify mailing name
        $this->verifyText("xpath=//table//tr[td[1]/text()='Mailing Name']/descendant::td[2]", preg_quote("Mailing $mailingName Webtest"));
        
        // verify mailing subject
        $this->verifyText("xpath=//table//tr[td[1]/text()='Subject']/descendant::td[2]", preg_quote("Test subject $mailingName for Webtest"));
        
        $this->verifyText( "xpath=//table//tr[td[1]/text()='Campaign']/descendant::td[2]", preg_quote("$campaignTitle") );

        //---- check for delivery detail--
        
        $this->click("link=Successful Deliveries");
        $this->waitForPageToLoad("30000");
      
        // check for open page
        $this->assertTrue($this->isTextPresent("Successful Deliveries"));
        
        // verify email
        $this->assertTrue($this->isTextPresent("mailino$firstName@mailson.co.in"));
        //------end delivery verification---------
    }
 
}
