<?php
// $Id$



/*
 
 */
function custom_value_create_example() {
  $params = array(
    'custom_1' => 'customString',
    'version' => 3,
    'entity_id' => 1,
  );

  require_once 'api/api.php';
  $result = civicrm_api('custom_value', 'create', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function custom_value_create_expectedresult() {

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
* testCreateCustomValue and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/CustomValueTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

