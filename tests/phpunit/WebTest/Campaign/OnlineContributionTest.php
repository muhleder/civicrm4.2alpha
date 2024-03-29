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


 
class WebTest_Campaign_OnlineContributionTest extends CiviSeleniumTestCase {

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
        
        // Logging in. Remember to wait for page to load. In most cases,
        // you can rely on 30000 as the value that allows your test to pass, however,
        // sometimes your test might fail because of this. In such cases, it's better to pick one element
        // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
        // page contents loaded and you can continue your test execution.
        $this->webtestLogin();
        
        // Create new group
        $title = substr(sha1(rand()), 0, 7);
        $groupName = $this->WebtestAddGroup( );
        
        // Adding contact
        // We're using Quick Add block on the main page for this.
        $firstName1 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName1, "Smith", "$firstName1.smith@example.org" );
        
        // add contact to group
        // visit group tab
        $this->click("css=li#tab_group a");
        $this->waitForElementPresent("group_id");
        
        // add to group
        $this->select("group_id", "label=$groupName");
        $this->click("_qf_GroupContact_next");
        $this->waitForPageToLoad("30000");
        
        $firstName2 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName2, "John", "$firstName2.john@example.org" );
        
        // add contact to group
        // visit group tab
        $this->click("css=li#tab_group a");
        $this->waitForElementPresent("group_id");
        
        // add to group
        $this->select("group_id", "label=$groupName");
        $this->click("_qf_GroupContact_next");
        $this->waitForPageToLoad("30000");
        
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
        $permissions = array(
                             'edit-2-administer-civicampaign',
                             'edit-1-make-online-contributions',
                             'edit-1-profile-listings-and-forms'
                             );
        $this->changePermissions( $permissions );
        
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
        
        $this->onlineContributionAddTest( $campaignTitle, $id );
    }
    
    function onlineContributionAddTest( $campaignTitle, $id )
    {
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr( sha1( rand( ) ), 0, 7 );
        $this->webtestAddPaymentProcessor( $processorName );
        
        $this->open( $this->sboxPath . "civicrm/admin/contribute/add?reset=1&action=add" );
        
        $contributionTitle = substr( sha1( rand( ) ), 0, 7 );
        $rand = 2 * rand( 2, 50 );
        
        // fill in step 1 (Title and Settings)
        $contributionPageTitle = "Title $contributionTitle";
        $this->type( 'title', $contributionPageTitle );
        $this->select( 'contribution_type_id', 'value=1' );
        
        // select campaign
        $this->click("campaign_id");
        $this->select("campaign_id", "value=$id" );
        
        $this->fillRichTextField( 'intro_text','This is Test Introductory Message','CKEditor' );
        $this->fillRichTextField( 'footer_text','This is Test Footer Message','CKEditor' );
        
        // go to step 2
        $this->click( '_qf_Settings_next' );
        $this->waitForElementPresent( "_qf_Amount_next-bottom" );
        
        //this contribution page for online contribution 
        $this->select( "payment_processor_id", "label=" . $processorName );
        $this->isTextPresent( "Contribution Amounts section enabled" );
        $this->type( "label_1", "amount 1" );
        $this->type( "value_1", "100" );
        $this->type( "label_2", "amount 2" );
        $this->type( "value_2", "200" );
        $this->click( "CIVICRM_QFID_1_2" );
        
        $this->click( "_qf_Amount_next-bottom" );
        $this->waitForPageToLoad( "30000" );
        
        // go to step 4
        $this->click("//div[@id='mainTabContainer']/ul//li/a[text()='Receipt']");
        $this->waitForElementPresent( '_qf_ThankYou_next-bottom' );
        $this->waitForElementPresent( "cke_thankyou_footer" );
        
        // fill in step 4 (Thanks and Receipt)
        $this->type( 'thankyou_title',     "Thank-you Page Title $contributionTitle" );
        $this->type( 'receipt_from_name',  "Receipt From Name $contributionTitle" );
        $this->type( 'receipt_from_email', "$contributionTitle@example.org" );
        $this->type( 'receipt_text',       "Receipt Message $contributionTitle" );
        $this->type( 'cc_receipt',         "$contributionTitle@example.net" );
        $this->type( 'bcc_receipt',        "$contributionTitle@example.com" );
        
        $this->click( '_qf_ThankYou_next-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        // go to step 5
        $this->click("//div[@id='mainTabContainer']/ul//li/a[text()='Tell a Friend']");
        $this->waitForElementPresent( "_qf_Contribute_next-bottom" );
        
        // fill in step 5 (Tell a Friend)
        $this->click( 'tf_is_active' );
        $this->type( 'tf_title',          "TaF Title $contributionTitle" );
        $this->type( 'intro',             "TaF Introduction $contributionTitle" );
        $this->type( 'suggested_message', "TaF Suggested Message $contributionTitle" );
        $this->type( 'general_link',      "TaF Info Page Link $contributionTitle" );
        $this->type( 'tf_thankyou_title',    "TaF Thank-you Title $contributionTitle" );
        $this->type( 'tf_thankyou_text',     "TaF Thank-you Message $contributionTitle" );
        
        $this->click( '_qf_Contribute_next-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        // go to step 6
        $this->click("//div[@id='mainTabContainer']/ul//li/a[text()='Profiles']");
        $this->waitForElementPresent( "_qf_Custom_next-bottom" );
        
        // fill in step 6 (Include Profiles)
        $this->select( 'custom_pre_id',  'value=1' );
        
        $this->click( '_qf_Custom_next-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        // go to step 7
        $this->click("//div[@id='mainTabContainer']/ul//li/a[text()='Premiums']");
        $this->waitForElementPresent( "_qf_Premium_next-bottom" );
        
        // fill in step 7 (Premiums)
        $this->click( 'premiums_active' );
        $this->type( 'premiums_intro_title',   "Prem Title $contributionTitle" );
        $this->type( 'premiums_intro_text',    "Prem Introductory Message $contributionTitle" );
        $this->type( 'premiums_contact_email', "$contributionTitle@example.info" );
        $this->type( 'premiums_contact_phone', rand( 100000000, 999999999 ) );
        $this->click( 'premiums_display_min_contribution' );
        
        $this->click( '_qf_Premium_next-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        // go to step 8
        $this->click("//div[@id='mainTabContainer']/ul//li/a[text()='Widgets']");
        $this->waitForElementPresent( "_qf_Widget_next-bottom" );
        
        // fill in step 8 (Widget Settings)
        $this->click( 'is_active' );
        $this->type( 'url_logo',     "URL to Logo Image $contributionTitle" );
        $this->type( 'button_title', "Button Title $contributionTitle" );
        $this->type( 'about',        "About $contributionTitle" );
        
        $this->click( '_qf_Widget_next-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        // go to step 9
        $this->click("//div[@id='mainTabContainer']/ul//li/a[text()='Personal Campaigns']");
        $this->waitForElementPresent( "_qf_Contribute_next-bottom" );
        
        // fill in step 9 (Enable Personal Campaign Pages)
        $this->click( 'pcp_active' );
        $this->click( 'is_approval_needed' );
        $this->type( 'notify_email', "$contributionTitle@example.name" );
        $this->select( 'supporter_profile_id', 'value=2' );
        $this->type( 'tellfriend_limit', 7 );
        $this->type( 'link_text', "'Create Personal Campaign Page' link text $contributionTitle" );
        
        // submit new contribution page
        $this->click( '_qf_Contribute_next-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        //get Url for Live Contribution Page
        $registerUrl = $this->_testVerifyRegisterPage( $contributionPageTitle );
        
        //logout
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        //Open Live Contribution Page
        $this->open( $this->sboxPath . $registerUrl );
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        
        $firstName = 'Ma'.substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An'.substr( sha1( rand( ) ), 0, 7 );
        
        $this->type( "email-5", $firstName . "@example.com" );
        
        $this->type( "first_name", $firstName );
        $this->type( "last_name",$lastName );
        
        $streetAddress = "100 Main Street";
        $this->type( "street_address-1", $streetAddress );
        $this->type( "city-1", "San Francisco" );
        $this->type( "postal_code-1", "94117" );
        $this->select( "country-1", "value=1228" );
        $this->select( "state_province-1", "value=1001" );
        
        //Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        
        //Billing Info
        $this->type( "billing_first_name", $firstName."billing" );
        $this->type( "billing_last_name", $lastName."billing"  );
        $this->type( "billing_street_address-5", "15 Main St." );
        $this->type( " billing_city-5", "San Jose" );
        $this->select( "billing_country_id-5", "value=1228" );
        $this->select( "billing_state_province_id-5", "value=1004" );
        $this->type( "billing_postal_code-5", "94129" );  
        $this->click( "_qf_Main_upload-bottom" );
        
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        //login to check contribution
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin( );
        
        //Find Contribution
        $this->open( $this->sboxPath . "civicrm/contribute/search?reset=1" );
        
        $this->waitForElementPresent( "contribution_date_low" );
        
        $this->type( "sort_name", "$firstName $lastName" );
        $this->click( "_qf_Search_refresh" );
        
        $this->waitForPageToLoad( '30000' );
        
        $this->waitForElementPresent( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" );
        
        //View Contribution Record
        $this->verifyText( "xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[10]/td[2]", preg_quote($campaignTitle) );
    }
    
    function _testVerifyRegisterPage( $contributionPageTitle ) 
    {
        $this->open( $this->sboxPath . "civicrm/admin/contribute?reset=1" );
        $this->waitForElementPresent( "_qf_SearchContribution_refresh" );
        $this->type( 'title', $contributionPageTitle );
        $this->click( "_qf_SearchContribution_refresh" );
        $this->waitForPageToLoad( '50000' );
        $id = $this->getAttribute("//div[@id='configure_contribution_page']//table/tbody/tr/td/strong[text()='$contributionPageTitle']/../../td[5]/div/span/ul/li/a[text()='Title and Settings']@href");
        $id = explode( 'id=', $id );
        $registerUrl = "civicrm/contribute/transact?reset=1&id=$id[1]";
        return $registerUrl;
    }
}