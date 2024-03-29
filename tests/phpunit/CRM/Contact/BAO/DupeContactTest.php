<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';

class CRM_Contact_BAO_DupeContactTest extends CiviUnitTestCase 
{
    
    function setUp( ) 
    {
        parent::setUp();
    }
    
    function testFuzzyDupes( )
    {
        // make dupe checks based on based on following contact sets:
        // FIRST - LAST - EMAIL
        // ---------------------------------
        // robin  - hood - robin@example.com
        // robin  - hood - hood@example.com
        // robin  - dale - robin@example.com
        // little - dale - dale@example.com
        // will   - dale - dale@example.com
        // will   - dale - will@example.com
        // will   - dale - will@example.com

        // create a group to hold contacts, so that dupe checks don't consider any other contacts in the DB
        $params = array( 'name'        => 'Dupe Group',
                         'title'       => 'New Test Dupe Group',
                         'domain_id'   => 1,
                         'is_active'   => 1,
                         'visibility'  => 'Public Pages',
                         'version'     => 3,
                         );
        // TODO: This is not an API test!!
        $result  = &civicrm_api('group', 'create', $params );
        $groupId = $result['id'];

        // contact data set
        // FIXME: move create params to separate function
        $params = array(
                        array('first_name'   => 'robin',
                              'last_name'    => 'hood',
                              'email'        => 'robin@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'robin',
                              'last_name'    => 'hood',
                              'email'        => 'hood@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'robin',
                              'last_name'    => 'dale',
                              'email'        => 'robin@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'little',
                              'last_name'    => 'dale',
                              'email'        => 'dale@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'will',
                              'last_name'    => 'dale',
                              'email'        => 'dale@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'will',
                              'last_name'    => 'dale',
                              'email'        => 'will@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'will',
                              'last_name'    => 'dale',
                              'email'        => 'will@example.com',
                              'contact_type' => 'Individual'),
                         );

        $count = 1;
        // TODO: This is not an API test!!
        foreach ( $params as $param ) {
            $param['version'] = 3;
            $contact =& civicrm_api('contact', 'create', $param );
            $contactIds[$count++] = $contact['id'];

            $grpParams = array( 'contact_id' => $contact['id'],
                                'group_id'   => $groupId,
                                'version'    => 3 );
            $res = civicrm_api('group_contact', 'create', $grpParams );
        }

        // verify that all contacts have been created separately
        $this->assertEquals( count($contactIds), 7, 'Check for number of contacts.' );

        require_once 'CRM/Dedupe/DAO/RuleGroup.php';
        require_once 'CRM/Dedupe/Finder.php';
        $dao = new CRM_Dedupe_DAO_RuleGroup();
        $dao->contact_type = 'Individual';
        $dao->level        = 'Fuzzy';
        $dao->is_default   = 1;
        $dao->find( true );

        $foundDupes = CRM_Dedupe_Finder::dupesInGroup($dao->id, $groupId);

        // -------------------------------------------------------------------------
        // default dedupe rule: threshold = 20 => (First + Last + Email) Matches ( 1 pair )
        // --------------------------------------------------------------------------
        // will   - dale - will@example.com
        // will   - dale - will@example.com
        // so 1 pair for - first + last + mail
        $this->assertEquals( count($foundDupes), 1, 'Check Individual-Fuzzy dupe rule for dupesInGroup().' );
  
        // delete all created contacts
        foreach ( $contactIds as $contactId ) {
            Contact::delete( $contactId );
        }
        // delete dupe group
        $params = array( 'id' => $groupId, 'version' => 3 );
        civicrm_api('group', 'delete', $params );
    }

    function testDupesByParams( )
    {
        // make dupe checks based on based on following contact sets:
        // FIRST - LAST - EMAIL
        // ---------------------------------
        // robin  - hood - robin@example.com
        // robin  - hood - hood@example.com
        // robin  - dale - robin@example.com
        // little - dale - dale@example.com
        // will   - dale - dale@example.com
        // will   - dale - will@example.com
        // will   - dale - will@example.com

        // contact data set
        // FIXME: move create params to separate function
        $params = array( 
                        array('first_name'   => 'robin',     
                              'last_name'    => 'hood',
                              'email'        => 'robin@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'robin',     
                              'last_name'    => 'hood',
                              'email'        => 'hood@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'robin',     
                              'last_name'    => 'dale',
                              'email'        => 'robin@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'little',     
                              'last_name'    => 'dale',
                              'email'        => 'dale@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'will',     
                              'last_name'    => 'dale',
                              'email'        => 'dale@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'will',     
                              'last_name'    => 'dale',
                              'email'        => 'will@example.com',
                              'contact_type' => 'Individual'),

                        array('first_name'   => 'will',     
                              'last_name'    => 'dale',
                              'email'        => 'will@example.com',
                              'contact_type' => 'Individual'),
                         );

        $count = 1;
        // TODO: This is not an API test!!
        foreach ( $params as $param ) {
            $param['version'] = 3;
            $contact =& civicrm_api('contact', 'create', $param );
            $contactIds[$count++] = $contact['id'];
        }

        // verify that all contacts have been created separately
        $this->assertEquals( count($contactIds), 7, 'Check for number of contacts.' );

        require_once 'CRM/Dedupe/DAO/RuleGroup.php';
        require_once 'CRM/Dedupe/Finder.php';
        $dao = new CRM_Dedupe_DAO_RuleGroup();
        $dao->contact_type = 'Individual';
        $dao->level        = 'Fuzzy';
        $dao->is_default   = 1;
        $dao->find( true );

        $fields = array( 'first_name' => 'robin',
                         'last_name'  => 'hood', 
                         'email'      => 'hood@example.com' );
        $dedupeParams = CRM_Dedupe_Finder::formatParams($fields, 'Individual');
        $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual', 'Fuzzy' );

        // Check with default Individual-Fizzy rule
        $this->assertEquals( count($ids), 1, 'Check Individual-Fizzy rule for dupesByParams().' );

        // delete all created contacts
        foreach ( $contactIds as $contactId ) {
            Contact::delete( $contactId );
        }
    }
}

?>
