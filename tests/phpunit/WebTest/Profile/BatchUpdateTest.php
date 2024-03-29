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
 
class WebTest_Profile_BatchUpdateTest extends CiviSeleniumTestCase {
    
    protected $captureScreenshotOnFailure = FALSE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testBatchUpdate()
    {
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // Add new individual using Quick Add block on the main page
        $firstName1 = "John_" . substr(sha1(rand()), 0, 7);
        $lastName   = "Smith_" . substr(sha1(rand()), 0, 7);
        $Name1 = $lastName.', '.$firstName1;
        $this->webtestAddContact( $firstName1, $lastName, "$firstName1.$lastName@example.com" );
        $this->waitForPageToLoad("30000");

        // Add new individual using Quick Add block on the main page
        $firstName1 = "James_" . substr(sha1(rand()), 0, 7);
        $Name2 = $lastName.', '.$firstName1;
        $this->webtestAddContact( $firstName1, $lastName, "$firstName1.$lastName@example.com" );
        $this->waitForPageToLoad("30000");
        $profileTitle = 'Batch Profile test for contacts ' . substr(sha1(rand()), 0, 7);
        $profileFor = 'Contacts';
        $customDataArr = $this->_addCustomData( $profileFor );
        $this->_addProfile( $profileTitle, $customDataArr, $profileFor );
        
        //setting ckeditor as WYSIWYG
        $this->open( $this->sboxPath . "civicrm/admin/setting/preferences/display?reset=1" );
        $this->waitForElementPresent( '_qf_Display_next-bottom' );
        $this->select( 'editor_id', 'CKEditor' );
        $this->click( '_qf_Display_next-bottom' );
        $this->waitForPageToLoad("30000");
        
        // Find Contact
        $this->open( $this->sboxPath . "civicrm/contact/search?reset=1" );
        $this->waitForElementPresent( '_qf_Basic_refresh' );
        $this->type( 'sort_name', $lastName );
        $this->click( '_qf_Basic_refresh' );
        $this->waitForElementPresent( '_qf_Basic_next_print' );
        
        // Batch Update Via Profile
        $this->waitForElementPresent( 'CIVICRM_QFID_ts_all_4' );
        $this->click( 'CIVICRM_QFID_ts_all_4' );
        
        $this->select( 'task', "label=Batch Update via Profile" );
        $this->click( 'Go' );
        $this->waitForElementPresent( '_qf_PickProfile_next' );

        $this->select( 'uf_group_id', "label={$profileTitle}" );
        $this->click( '_qf_PickProfile_next' );

        $this->waitForElementPresent( '_qf_Batch_next' );
        
        $this->isElementPresent("xpath=//form[@id='Batch']/div[2]/table/tbody//tr/td[text()='{$Name2}']");
        $this->isElementPresent("xpath=//form[@id='Batch']/div[2]/table/tbody//tr/td[text()='{$Name1}']");
        
        // selecting first check of profile
        $this->click( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[2]/input[2]" );
        // selecting second check of profile
        $this->click( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[3]/input[2]" );
        // clicking copy values to rows of first check and verifying
        // if other check Profile Field check box are affected
        
        $this->click( "xpath=//table[@class='crm-copy-fields']/thead/tr/td[2]/img" );
        sleep(5);
        if( $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[3]/input[2]" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[3]/input[4]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[3]/input[6]" )&&
            
            //verification for second field first row
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[3]/input[2]" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[3]/input[4]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[3]/input[6]" )&&
            
            //verification for first field second row
            $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[2]/input[2]" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[2]/input[4]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[2]/input[6]" )
            )
            {
                $assertCheck = true;
            }
        else 
            $assertCheck = false;
        
        $this->assertTrue($assertCheck, 'copy rows for field one failed');
        
        $this->click( "xpath=//table[@class='crm-copy-fields']/thead/tr/td[3]/img" );
        sleep(5);
        if( $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[3]/input[2]" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[3]/input[4]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[3]/input[6]" )&&
            
            //verification for second field first row
            $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[3]/input[2]" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[3]/input[4]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[3]/input[6]" )&&
            
            //verification for first field second row
            $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[2]/input[2]" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[2]/input[4]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[2]/input[6]" )
            )
            {
                $assertCheck = true;
            }
        else 
            $assertCheck = false;        
        
        $this->assertTrue($assertCheck, 'copy rows for field two failed');
        
        $dateElementIdFirstRow = $this->getAttribute("xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[4]/input/@id");
        $dateElementIdSecondRow = $this->getAttribute("xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[4]/input/@id");
        
        $this->webtestFillDateTime($dateElementIdFirstRow, "+1 week");
        $this->click( "xpath=//table[@class='crm-copy-fields']/thead/tr/td[4]/img" );
        sleep(5);
        
        if( $this->getValue("{$dateElementIdFirstRow}_time") == $this->getValue("{$dateElementIdSecondRow}_time") && $this->getValue("{$dateElementIdFirstRow}_display") == $this->getValue("{$dateElementIdSecondRow}_display")){
            $assertCheck = true;
        }
        else
            $assertCheck = false;        
        
        $this->assertTrue($assertCheck, 'date / time coping failed');
        
        $richTextAreaIdOne = $this->getAttribute("xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[5]/textarea/@id");
        $richTextAreaIdTwo = $this->getAttribute("xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[5]/textarea/@id");
        
        $this->fillRichTextField( $richTextAreaIdOne,'This is Test Introductory Message','CKEditor' );
        $this->click( "xpath=//table[@class='crm-copy-fields']/thead/tr/td[5]/img" );
        sleep(5);
        
        if($this->getValue($richTextAreaIdOne) == $this->getValue($richTextAreaIdTwo)){
            $assertCheck = true;
        }
        else
            $assertCheck = false;
        
        $this->assertTrue($assertCheck, 'Rich Text Area coping failed');  
        
        // selecting first check of profile
        $this->click( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[6]/input" );
        // selecting second check of profile
        $this->click( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[7]/input" );
        // clicking copy values to rows of first check and verifying
        // if other radio Profile Field radio buttons are affected
        
        $this->click( "xpath=//table[@class='crm-copy-fields']/thead/tr/td[6]/img" );
        sleep(5);
        if( $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[7]/input" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[7]/input[2]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[7]/input[3]" )&&
            
            //verification for second field first row
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[7]/input" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[7]/input[2]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[7]/input[3]" )&&
            
            //verification for first field second row
            $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[6]/input" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[6]/input[2]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[6]/input[3]" )
            )
            {
                $assertCheck = true;
            }
        else 
            $assertCheck = false;
        
        $this->assertTrue($assertCheck, 'copy rows for field one failed[radio button]');
        
        $this->click( "xpath=//table[@class='crm-copy-fields']/thead/tr/td[7]/img" );
        sleep(5);
        if( $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[7]/input" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[7]/input[2]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[7]/input[3]" )&&
            
            //verification for second field first row
            $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[7]/input" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[7]/input[2]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[7]/input[3]" )&&
            
            //verification for first field second row
            $this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[6]/input" )&& 
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[6]/input[2]" )&&
            !$this->isChecked( "xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[6]/input[3]" )
            )
            {
                $assertCheck = true;
            }
        else 
            $assertCheck = false;        
        
        $this->assertTrue($assertCheck, 'copy rows for field two failed[radio button]');
        
        //test with tinymce editor
        $this->open( $this->sboxPath . "civicrm/admin/setting/preferences/display?reset=1" );
        $this->waitForElementPresent( '_qf_Display_next-bottom' );
        $this->select( 'editor_id', 'TinyMCE' );
        $this->click( '_qf_Display_next-bottom' );
        $this->waitForPageToLoad("30000");
        
        // Find Contact
        $this->open( $this->sboxPath . "civicrm/contact/search?reset=1" );
        $this->waitForElementPresent( '_qf_Basic_refresh' );
        $this->type( 'sort_name', $lastName );
        $this->click( '_qf_Basic_refresh' );
        $this->waitForElementPresent( '_qf_Basic_next_print' );
        
        // Batch Update Via Profile
        $this->waitForElementPresent( 'CIVICRM_QFID_ts_all_4' );
        $this->click( 'CIVICRM_QFID_ts_all_4' );
        
        $this->select( 'task', "label=Batch Update via Profile" );
        $this->click( 'Go' );
        $this->waitForElementPresent( '_qf_PickProfile_next' );

        $this->select( 'uf_group_id', "label={$profileTitle}" );
        $this->click( '_qf_PickProfile_next' );

        $this->waitForElementPresent( '_qf_Batch_next' );
        
        $this->isElementPresent("xpath=//form[@id='Batch']/div[2]/table/tbody//tr/td[text()='{$Name2}']");
        $this->isElementPresent("xpath=//form[@id='Batch']/div[2]/table/tbody//tr/td[text()='{$Name1}']");
        
        $richTextAreaIdOne = $this->getAttribute("xpath=//form[@id='Batch']/div[2]/table/tbody/tr/td[5]/textarea/@id");
        $richTextAreaIdTwo = $this->getAttribute("xpath=//form[@id='Batch']/div[2]/table/tbody/tr[2]/td[5]/textarea/@id");
        
        $this->selectFrame("css=td.mceIframeContainer iframe#{$richTextAreaIdOne}_ifr");
        $this->type("//html/body", 'this is intro text');
        $this->selectFrame('relative=top');
        
        $this->click( "xpath=//table[@class='crm-copy-fields']/thead/tr/td[5]/img" );
        sleep(5);
    
        if( $this->getValue($richTextAreaIdOne) == $this->getValue($richTextAreaIdTwo) ){
            $assertCheck = true;
        }
        else
            $assertCheck = false;
        
        $this->assertTrue($assertCheck, 'Rich Text Area coping failed [TinyMCE]');
        
        //campaign test for interview 
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
        
        //Adding a survey
        $this->open( $this->sboxPath . "civicrm/survey/add?reset=1" );
        $this->waitForElementPresent( '_qf_Survey_next-bottom' );
        $surveyTitle = "BatchUpdateTest Survey" . substr(sha1(rand()), 0, 7);
        $this->type( "title", $surveyTitle );
        $this->select( 'activity_type_id', 'label=Survey' );
        $this->select( 'profile_id', "label={$profileTitle}" );
        $this->click('CIVICRM_QFID_1_2');
        $this->type( 'option_label_1', 'option1' ); 
        $this->type( 'option_value_1', 'option1' );
        $this->type( 'option_label_2', 'option2' );
        $this->type( 'option_value_2', 'option2' );
        $this->click( '_qf_Survey_next-bottom' );
        $this->waitForPageToLoad('30000');
        
        //Reserve and interview responsedents
        $this->waitForElementPresent( "xpath=//table[@id='surveys']/tbody//tr/td[2][text()='{$surveyTitle}']/../td[@class='crm-campaign-voterLinks']/span/ul/li/a" );
        $this->click( "xpath=//table[@id='surveys']/tbody//tr/td[2][text()='{$surveyTitle}']/../td[@class='crm-campaign-voterLinks']/span/ul/li/a" );
        $this->waitForPageToLoad('30000');
        $this->click( "xpath=//div[@id='search_form_reserve']/div" );
        $this->waitForElementPresent( 'sort_name' );
        $this->type( 'sort_name', $lastName );
        $this->waitForElementPresent( '_qf_Search_refresh' );
        $this->click( '_qf_Search_refresh' );
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent( 'Go' );
        $this->click( 'CIVICRM_QFID_ts_all_4' );
        $this->click( 'Go' );
        $this->waitForElementPresent( '_qf_Reserve_next_reserveToInterview-top' );
        $this->click( '_qf_Reserve_next_reserveToInterview-top' );
        $this->waitForElementPresent( '_qf_Interview_cancel_interview' );
        
        $this->isElementPresent("xpath=//table[@id='voterRecords']/tbody//tr/td[text()='{$Name2}']");
        $this->isElementPresent("xpath=//table[@id='voterRecords']/tbody//tr/td[text()='{$Name1}']");
        
        //edition to be done here
        // selecting first check of profile
        $this->click( "xpath=//table[@id='voterRecords']/tbody/tr/td[3]/input[2]" );
        // selecting second check of profile
        $this->click( "xpath=//table[@id='voterRecords']/tbody/tr/td[4]/input[2]" );
        // clicking copy values to rows of first check and verifying
        // if other check Profile Field check box are affected
        
        $this->click( "xpath=//table[@id='voterRecords']/thead/tr/th[3]/div/img" );
        sleep(5);
        if( $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[4]/input[2]" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[4]/input[4]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[4]/input[6]" )&&
            
            //verification for second field first row
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[4]/input[2]" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[4]/input[4]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[4]/input[6]" )&&
            
            //verification for first field second row
            $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[3]/input[2]" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[3]/input[4]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[3]/input[6]" )
            )
            {
                $assertCheck = true;
            }
        else 
            $assertCheck = false;
        
        $this->assertTrue($assertCheck, 'copy rows for field one failed for inteview (campaign)');
        
        $this->click( "xpath=//table[@id='voterRecords']/thead/tr/th[4]/div/img" );
        sleep(5);
        if( $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[4]/input[2]" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[4]/input[4]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[4]/input[6]" )&&
            
            //verification for second field first row
            $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[4]/input[2]" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[4]/input[4]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[4]/input[6]" )&&
            
            //verification for first field second row
            $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[3]/input[2]" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[3]/input[4]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[3]/input[6]" )
            )
            {
                $assertCheck = true;
            }
        else 
            $assertCheck = false;        
        
        $this->assertTrue($assertCheck, 'copy rows for field two failed for inteview (campaign)'); 
        
        $dateElementIdFirstRow = $this->getAttribute("xpath=//table[@id='voterRecords']/tbody/tr/td[5]/input/@id");
        $dateElementIdSecondRow = $this->getAttribute("xpath=//table[@id='voterRecords']/tbody/tr[2]/td[5]/input/@id");
        
        $this->webtestFillDateTime($dateElementIdFirstRow, "+1 week");
        $this->click( "xpath=//table[@id='voterRecords']/thead/tr/th[5]/div/img" );
        sleep(5);
        
        if( $this->getValue("{$dateElementIdFirstRow}_time") == $this->getValue("{$dateElementIdSecondRow}_time") && $this->getValue("{$dateElementIdFirstRow}_display") == $this->getValue("{$dateElementIdSecondRow}_display")){
            $assertCheck = true;
        }
        else
            $assertCheck = false;        
        
        $this->assertTrue($assertCheck, 'date / time coping failed for inteview (campaign)');
        
        $this->type( "xpath=//table[@id='voterRecords']/tbody/tr/td[@class='note']/input",'This is Test Introductory Message' );
        $this->click( "xpath=//table[@id='voterRecords']/thead/tr/th[8]/div/img" );
        sleep(5);
        
        if($this->getValue("xpath=//table[@id='voterRecords']/tbody/tr/td[@class='note']/input") == $this->getValue("xpath=//table[@id='voterRecords']/tbody/tr[2]/td[@class='note']/input")){
            $assertCheck = true;
        }
        else
            $assertCheck = false;
        
        $this->assertTrue($assertCheck, 'Note Custom field coping failed');
        
        $this->click( "xpath=//table[@id='voterRecords']/tbody/tr/td[6]/input" );
        // selecting second check of profile
        $this->click( "xpath=//table[@id='voterRecords']/tbody/tr/td[7]/input" );
        // clicking copy values to rows of first check and verifying
        // if other radio Profile Field radio buttons are affected
        
        $this->click( "xpath=//table[@id='voterRecords']/thead/tr/th[6]/div/img" );
        sleep(5);
        if( $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[7]/input" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[7]/input[2]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[7]/input[3]" )&&
            
            //verification for second field first row
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[7]/input" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[7]/input[2]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[7]/input[3]" )&&
            
            //verification for first field second row
            $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[6]/input" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[6]/input[2]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[6]/input[3]" )
            )
            {
                $assertCheck = true;
            }
        else 
            $assertCheck = false;
        
        $this->assertTrue($assertCheck, 'copy rows for field one failed for inteview (campaign)[radio button]');
        
        $this->click( "xpath=//table[@id='voterRecords']/thead/tr/th[7]/div/img" );
        sleep(5);
        if( $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[7]/input" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[7]/input[2]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr/td[7]/input[3]" )&&
            
            //verification for second field first row
            $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[7]/input" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[7]/input[2]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[7]/input[3]" )&&
            
            //verification for first field second row
            $this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[6]/input" )&& 
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[6]/input[2]" )&&
            !$this->isChecked( "xpath=//table[@id='voterRecords']/tbody/tr[2]/td[6]/input[3]" )
            )
            {
                $assertCheck = true;
            }
        else 
            $assertCheck = false;        
        
        $this->assertTrue($assertCheck, 'copy rows for field two failed for inteview (campaign)[radio button]'); 
        
        //change the editor back to ckeditor
        $this->open( $this->sboxPath . "civicrm/admin/setting/preferences/display?reset=1" );
        $this->waitForElementPresent( '_qf_Display_next-bottom' );
        $this->select( 'editor_id', 'CKEditor' );
        $this->click( '_qf_Display_next-bottom' );
        $this->waitForPageToLoad("30000");
    }
    
    function _addProfile( $profileTitle, $customDataArr, $profileFor ) 
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
        
        foreach($customDataArr as $key => $customDataParams){
            $this->select( 'field_name[0]', "label={$profileFor}" );
            $this->select( 'field_name[1]', "label={$customDataParams[1]} :: {$customDataParams[0]}" );
            $this->click( 'field_name[1]' );
            $this->click( 'label' );
            
            // Clicking save and new
            $this->click( '_qf_Field_next_new-bottom' );
            $this->waitForPageToLoad("30000");
            $this->assertTrue( $this->isTextPresent( "Your CiviCRM Profile Field '{$customDataParams[1]}' has been saved to '{$profileTitle}'." ) ); 
        }
    }
    
    function _addCustomData( $profileFor )
    {
        $returnArray = array();
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
        $this->select("extends[0]", "label={$profileFor}");
        if( $this->isElementPresent( '//option' ) ){
            $this->click("//option[@value='']");
        }
        
        $this->click( '_qf_Group_next-bottom' );
        $this->waitForElementPresent( '_qf_Field_cancel-bottom' );
        
        //Is custom group created?
        $this->assertTrue( $this->isTextPresent( "Your custom field set '{$customGroupTitle}' has been added. You can add custom fields now." ) );
        
        //for checkbox 1
        $checkLabel1 = 'Custom Check One Text_' . substr(sha1(rand()), 0, 4); 
        $this->type( 'label', $checkLabel1 );
        $this->click( 'data_type[0]' );
        $this->select( 'data_type[0]', "label=Alphanumeric" );
        $this->select( 'data_type[1]', "label=CheckBox" );
        
        // enter checkbox options
        $checkOneOptionLabel1 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_1', $checkOneOptionLabel1 );
        $this->type( 'option_value_1', 1 );
        $checkOneOptionLabel2 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_2', $checkOneOptionLabel2 );
        $this->type( 'option_value_2', 2 );
        $this->click("link=another choice");
        $checkOneOptionLabel3 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_3', $checkOneOptionLabel3 );
        $this->type( 'option_value_3', 3 );
        
        //clicking save
        $this->click( '_qf_Field_next' );
        $this->waitForPageToLoad( '30000' );
        
        //Is custom field created
        $this->assertTrue( $this->isTextPresent( "Your custom field '$checkLabel1' has been saved." ) );
        $returnArray[1] = array( $customGroupTitle, $checkLabel1 );
        
        // create another custom field - Integer Radio
        $this->click("//a[@id='newCustomField']/span");
        $this->waitForPageToLoad("30000");
        
        //for checkbox 2
        $checkLabel2 = 'Custom Check Two Text_' . substr(sha1(rand()), 0, 4); 
        $this->type( 'label', $checkLabel2 );
        $this->click( 'data_type[0]' );
        $this->select( 'data_type[0]', "label=Alphanumeric" );
        $this->select( 'data_type[1]', "label=CheckBox" );
        
        // enter checkbox options
        $checkTwoOptionLabel1 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_1', $checkTwoOptionLabel1 );
        $this->type( 'option_value_1', 1 );
        $checkTwoOptionLabel2 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_2', $checkTwoOptionLabel2 );
        $this->type( 'option_value_2', 2 );
        $this->click("link=another choice");
        $checkTwoOptionLabel3 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_3', $checkTwoOptionLabel3 );
        $this->type( 'option_value_3', 3 );
        
        //clicking save
        $this->click( '_qf_Field_next' );
        $this->waitForPageToLoad( '30000' );
        
        //Is custom field created
        $this->assertTrue( $this->isTextPresent( "Your custom field '$checkLabel2' has been saved." ) );
        $returnArray[2] = array( $customGroupTitle,$checkLabel2 );
        
        // create another custom field - Integer Radio
        $this->click("//a[@id='newCustomField']/span");
        $this->waitForPageToLoad("30000");
        
        // create another custom field - Date
        $dateFieldLabel = 'Custom Date Field'.substr(sha1(rand()), 0, 4);
        $this->type( 'label', $dateFieldLabel );
        $this->click( 'data_type[0]' );
        $this->select( 'data_type[0]', "label=Date" );
        $this->waitForElementPresent( 'start_date_years' );
        
        // enter years prior to current date
        $this->type( 'start_date_years', 3 );
        
        // enter years upto the end year
        $this->type( 'end_date_years', 3 );
        
        // select the date and time format 
        $this->select( 'date_format', "value=yy-mm-dd" );
        $this->select( 'time_format', "value=2" );
        
        //clicking save
        $this->click( '_qf_Field_next' );
        $this->waitForPageToLoad( '30000' );
        
        //Is custom field created
        $this->assertTrue( $this->isTextPresent( "Your custom field '$dateFieldLabel' has been saved." ) );
        $returnArray[3] = array( $customGroupTitle,$dateFieldLabel );
        
        // create another custom field - Integer Radio
        $this->click("//a[@id='newCustomField']/span");
        $this->waitForPageToLoad("30000");
        
        //create rich text editor field
        $richTextField = 'Custom Rich TextField_' . substr(sha1(rand()), 0, 4); 
        $this->type( 'label', $richTextField );
        $this->click( 'data_type[0]' );
        $this->select( 'data_type[0]', "label=Note" );
        $this->select( 'data_type[1]', "label=RichTextEditor" );
        
        //clicking save
        $this->click( '_qf_Field_next' );
        $this->waitForPageToLoad( '30000' );
        
        //Is custom field created
        $this->assertTrue( $this->isTextPresent( "Your custom field '$richTextField' has been saved." ) );
        $returnArray[4] = array( $customGroupTitle,$richTextField );
               
        // create another custom field - Integer Radio
        $this->click("//a[@id='newCustomField']/span");
        $this->waitForPageToLoad("30000");
        
        //create radio button field
        //for radio 1
        $radioLabel1 = 'Custom Radio One Text_' . substr(sha1(rand()), 0, 4); 
        $this->type( 'label', $radioLabel1 );
        $this->click( 'data_type[0]' );
        $this->select( 'data_type[0]', "label=Alphanumeric" );
        $this->select( 'data_type[1]', "label=Radio" );
        
        // enter radio options
        $radioOneOptionLabel1 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_1', $radioOneOptionLabel1 );
        $this->type( 'option_value_1', 1 );
        $radioOneOptionLabel2 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_2', $radioOneOptionLabel2 );
        $this->type( 'option_value_2', 2 );
        $this->click("link=another choice");
        $radioOneOptionLabel3 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_3', $radioOneOptionLabel3 );
        $this->type( 'option_value_3', 3 );
        
        //clicking save
        $this->click( '_qf_Field_next' );
        $this->waitForPageToLoad( '30000' );
        
        //Is custom field created
        $this->assertTrue( $this->isTextPresent( "Your custom field '$radioLabel1' has been saved." ) );
        $returnArray[5] = array( $customGroupTitle, $radioLabel1 );
        
        // create another custom field - Integer Radio
        $this->click("//a[@id='newCustomField']/span");
        $this->waitForPageToLoad("30000");
        
        //for radio 2
        $radioLabel2 = 'Custom Radio Two Text_' . substr(sha1(rand()), 0, 4); 
        $this->type( 'label', $radioLabel2 );
        $this->click( 'data_type[0]' );
        $this->select( 'data_type[0]', "label=Alphanumeric" );
        $this->select( 'data_type[1]', "label=Radio" );
        
        // enter radio options
        $radioTwoOptionLabel1 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_1', $radioTwoOptionLabel1 );
        $this->type( 'option_value_1', 1 );
        $radioTwoOptionLabel2 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_2', $radioTwoOptionLabel2 );
        $this->type( 'option_value_2', 2 );
        $this->click("link=another choice");
        $radioTwoOptionLabel3 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
        $this->type( 'option_label_3', $radioTwoOptionLabel3 );
        $this->type( 'option_value_3', 3 );
        
        //clicking save
        $this->click( '_qf_Field_next' );
        $this->waitForPageToLoad( '30000' );
        
        //Is custom field created
        $this->assertTrue( $this->isTextPresent( "Your custom field '$radioLabel2' has been saved." ) );
        $returnArray[6] = array( $customGroupTitle,$radioLabel2 );
        
        return $returnArray;
    }
}