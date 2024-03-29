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
 
class WebTest_Member_BatchUpdateViaProfileTest extends CiviSeleniumTestCase {

    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testMemberAdd()
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();

        // Create a membership type to use for this test (defaults for this helper function are rolling 1 year membership)
        $memTypeParams = $this->webtestAddMembershipType( );
        
        $endDate = date( 'F jS, Y', strtotime( "+1 year +1 month -1 day" ) );
        
        // Add new individual using Quick Add block on the main page
        $firstName1 = "John_" . substr(sha1(rand()), 0, 7);
        $lastName   = "Smith_" . substr(sha1(rand()), 0, 7);
        $Name1 = $lastName.', '.$firstName1;
        $this->webtestAddContact( $firstName1, $lastName, "$firstName1.$lastName@example.com" );
        $this->waitForPageToLoad("30000");
        
        // Add membership for this individual
        $this->_addMembership( $memTypeParams );
        // Is status message correct?
        $this->assertTrue( $this->isTextPresent( "membership for $firstName1 $lastName has been added." ),
                           "Status message didn't show up after saving!" );

        // click through to the membership view screen
        $this->click( "xpath=//div[@id='memberships']//table//tbody/tr[1]/td[7]/span/a[text()='View']" );
        $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
        
        // Verify End date
        $verifyData = array(
                            'Membership Type' => $memTypeParams['membership_type'],
                            'Status'          => 'New',
                            'End date'        => $endDate
                            );
        foreach ( $verifyData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        
        // Add new individual using Quick Add block on the main page
        $firstName2 = "John_" . substr(sha1(rand()), 0, 7);
        $Name2 = $lastName.', '.$firstName2;
        $this->webtestAddContact( $firstName2, $lastName, "$firstName2.$lastName@example.com" );
        $this->waitForPageToLoad("30000");
        
        // Add membership for this individual
        $this->_addMembership( $memTypeParams );
        // Is status message correct?
        $this->assertTrue( $this->isTextPresent( "membership for $firstName2 $lastName has been added." ),
                           "Status message didn't show up after saving!" );

        // click through to the membership view screen
        $this->click( "xpath=//div[@id='memberships']//table//tbody/tr[1]/td[7]/span/a[text()='View']" );
        $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
        
        // Verify End date
        $verifyData = array(
                            'Membership Type' => $memTypeParams['membership_type'],
                            'Status'          => 'New',
                            'End date'        => $endDate
                            );
        foreach ( $verifyData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        $profileTitle     = 'Profile_' . substr(sha1(rand()), 0, 4);
        $customDataParams = $this->_addCustomData( );
        $this->_addProfile( $profileTitle, $customDataParams );
        
        // Find Members
        $this->open( $this->sboxPath . "civicrm/member/search?reset=1" );
        $this->waitForElementPresent( '_qf_Search_refresh' );
        $this->type( 'sort_name', $lastName );
        $this->click( '_qf_Search_refresh' );
        $this->waitForElementPresent( '_qf_Search_next_print' );

        // Batch Update Via Profile
        $this->click( 'CIVICRM_QFID_ts_all_10' );
        $this->select( 'task', "label=Batch Update Members Via Profile" );
        $this->click( 'Go' );
        $this->waitForElementPresent( '_qf_PickProfile_back-bottom' );

        $this->select( 'uf_group_id', "label={$profileTitle}" );
        $this->click( '_qf_PickProfile_next-bottom' );

        $this->waitForElementPresent( '_qf_Batch_back-bottom' );
        $this->type( "xpath=//form[@id='Batch']/div[2]/table/tbody//tr/td[text()='{$Name1}']/../td[3]/input", "This is test custom data text1" );
        $this->select("xpath=//form[@id='Batch']/div[2]/table/tbody//tr/td[text()='{$Name1}']/../td[4]/select", "label=Current");

        $this->type( "xpath=//form[@id='Batch']/div[2]/table/tbody//tr/td[text()='{$Name2}']/../td[3]/input", "This is test custom data text2" );
        $this->select("xpath=//form[@id='Batch']/div[2]/table/tbody//tr/td[text()='{$Name2}']/../td[4]/select", "label=Grace");

        $this->click( '_qf_Batch_next-bottom' );
        $this->waitForElementPresent( '_qf_Result_done' );
        $this->click( '_qf_Result_done' );
        $this->waitForElementPresent( '_qf_Search_next_print' );
        
        // View Membership
        $this->click( "xpath=//div[@id='memberSearch']/table/tbody//tr/td[3]/a[text()='{$Name1}']/../../td[11]/span/a[text()='View']" );
        $this->waitForElementPresent( '_qf_MembershipView_cancel-bottom' );
        
        // Verify End date
        $verifyData = array(
                            'Membership Type' => $memTypeParams['membership_type'],
                            'Status'          => 'Current',
                            'End date'        => $endDate
                            );
        foreach ( $verifyData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        
        $this->click( '_qf_MembershipView_cancel-bottom' );
        $this->waitForElementPresent( '_qf_Search_next_print' );

        // View Membership
        $this->click( "xpath=//div[@id='memberSearch']/table/tbody//tr/td[3]/a[text()='{$Name2}']/../../td[11]/span/a[text()='View']" );
        $this->waitForElementPresent( '_qf_MembershipView_cancel-bottom' );
        
        // Verify End date
        $verifyData = array(
                            'Membership Type' => $memTypeParams['membership_type'],
                            'Status'          => 'Grace',
                            'End date'        => $endDate
                            );
        foreach ( $verifyData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
    }

    function _addMembership( $memTypeParams )
    {
        // click through to the membership view screen
        $this->click( "css=li#tab_member a" );
        $this->waitForElementPresent("link=Add Membership");
        $this->click("link=Add Membership");
        $this->waitForElementPresent("_qf_Membership_cancel-bottom");

        // fill in Membership Organization and Type
        $this->select("membership_type_id[0]", "label={$memTypeParams['member_org']}");
        // Wait for membership type select to reload
        $this->waitForTextPresent( $memTypeParams['membership_type'] );
        sleep(3);
        $this->select("membership_type_id[1]", "label={$memTypeParams['membership_type']}");
        
        $sourceText = "Membership ContactAddTest Webtest";
        // fill in Source
        $this->type("source", $sourceText );
        
        // Let Join Date stay default
        
        // fill in Start Date
        $this->webtestFillDate( 'start_date' );
        
        // Clicking save.
        $this->click("_qf_Membership_upload");
        $this->waitForPageToLoad("30000");

        // page was loaded
        $this->waitForTextPresent( $sourceText );
    }

    function _addProfile( $profileTitle, $customDataParams ) 
    {
        // Go directly to the URL of the screen that you will be testing (New Profile).
        $this->open( $this->sboxPath . "civicrm/admin/uf/group?reset=1" );

        $this->waitForPageToLoad("30000");
        $this->click( 'link=Add Profile' );
        
        // Add membership custom data field to profile
        $this->waitForElementPresent( '_qf_Group_cancel-bottom' );
        $this->type( 'title', $profileTitle );
        $this->click( '_qf_Group_next-bottom' );

        $this->waitForElementPresent( '_qf_Field_cancel-bottom' );
        $this->assertTrue( $this->isTextPresent( "Your CiviCRM Profile '{$profileTitle}' has been added. You can add fields to this profile now." ) );

        $this->select( 'field_name[0]', "value=Membership" );
        $this->select( 'field_name[1]', "label={$customDataParams[0]} :: {$customDataParams[1]}" );
        $this->click( 'field_name[1]' );
        $this->click( 'label' );
        
        // Clicking save and new
        $this->click( '_qf_Field_next_new-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( "Your CiviCRM Profile Field '{$customDataParams[0]}' has been saved to '{$profileTitle}'." ) );
        
        // Add membership status field to profile - CRM-8618
        $this->select( 'field_name[0]', "value=Membership" );
        $this->select( 'field_name[1]', "label=Membership Status" );
        $this->click( 'field_name[1]' );
        $this->click( 'label' );
        // Clicking save
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( "Your CiviCRM Profile Field 'Membership Status' has been saved to '{$profileTitle}'." ) );
        
    }

    function _addCustomData( )
    {
        $customGroupTitle = 'Custom_' . substr(sha1(rand()), 0, 4);
        // Go directly to the URL of the screen that you will be testing (New Custom Group).
        $this->open( $this->sboxPath . "civicrm/admin/custom/group?reset=1" );
        
        //add new custom data
        $this->click("//a[@id='newCustomDataGroup']/span");
        $this->waitForPageToLoad("30000");
        
        //fill custom group title
        $this->click("title");
        $this->type("title", $customGroupTitle);
        
        //custom group extends 
        $this->click("extends[0]");
        $this->select("extends[0]", "value=Membership");
        $this->click("//option[@value='Membership']");
        $this->click( '_qf_Group_next-bottom' );
        $this->waitForElementPresent( '_qf_Field_cancel-bottom' );
        
        //Is custom group created?
        $this->assertTrue( $this->isTextPresent( "Your custom field set '{$customGroupTitle}' has been added. You can add custom fields now." ) );
        
        $textFieldLabel = 'Custom Field Text_' . substr(sha1(rand()), 0, 4); 
        $this->type( 'label', $textFieldLabel );

        //enter pre help msg
        $this->type( 'help_pre', "this is field pre help");
        
        //enter post help msg
        $this->type( 'help_post', "this is field post help");
        
        //Is searchable?
        $this->click( 'is_searchable' );
        
        //clicking save
        $this->click( '_qf_Field_next' );
        $this->waitForPageToLoad( '30000' );
        
        //Is custom field created
        $this->assertTrue( $this->isTextPresent( "Your custom field '$textFieldLabel' has been saved." ) );
        
        return array( $textFieldLabel, $customGroupTitle );
    }
}