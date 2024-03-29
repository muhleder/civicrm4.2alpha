<?php
// $Id$



/*
 
 */
function tag_create_example() {
  $params = array(
    'name' => 'New Tag3',
    'description' => 'This is description for New Tag 02',
    'version' => 3,
  );

  require_once 'api/api.php';
  $result = civicrm_api('tag', 'create', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function tag_create_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 8,
    'values' => array(
      '8' => array(
        'id' => 8,
        'name' => 'New Tag3',
        'description' => 'This is description for New Tag 02',
        'parent_id' => '',
        'is_selectable' => '',
        'is_reserved' => '',
        'is_tagset' => '',
        'used_for' => 'civicrm_contact',
        'created_id' => '',
        'created_date' => '20111031165700',
      ),
    ),
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testCreate and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/TagTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

