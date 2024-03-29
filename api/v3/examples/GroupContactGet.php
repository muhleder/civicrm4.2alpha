<?php
// $Id$



/*
 
 */
function group_contact_get_example() {
  $params = array(
    'contact_id' => 1,
    'version' => 3,
  );

  require_once 'api/api.php';
  $result = civicrm_api('group_contact', 'get', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_get_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 1,
    'values' => array(
      '1' => array(
        'id' => '1',
        'group_id' => '1',
        'title' => 'New Test Group Created',
        'visibility' => 'Public Pages',
        'is_hidden' => 0,
        'in_date' => '2011-10-31 16:55:02',
        'in_method' => 'API',
      ),
    ),
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGet and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/GroupContactTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

