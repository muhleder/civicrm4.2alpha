<?php
// $Id$



/*
 
 */
function uf_group_delete_example() {
  $params = array(
    'add_captcha' => 1,
    'add_contact_to_group' => 2,
    'cancel_URL' => 'http://example.org/cancel',
    'created_date' => '2009-06-27 00:00:00',
    'created_id' => 69,
    'group' => 2,
    'group_type' => 'Individual,Contact',
    'help_post' => 'help post',
    'help_pre' => 'help pre',
    'is_active' => 0,
    'is_cms_user' => 1,
    'is_edit_link' => 1,
    'is_map' => 1,
    'is_reserved' => 1,
    'is_uf_link' => 1,
    'is_update_dupe' => 1,
    'name' => 'Test_Group',
    'notify' => 'admin@example.org',
    'post_URL' => 'http://example.org/post',
    'title' => 'Test Group',
    'version' => 3,
  );

  require_once 'api/api.php';
  $result = civicrm_api('uf_group', 'delete', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_group_delete_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'values' => TRUE,
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testUFGroupDelete and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/UFGroupTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

