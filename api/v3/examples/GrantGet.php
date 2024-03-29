<?php
// $Id$



/*
 
 */
function grant_get_example() {
  $params = array(
    'version' => 3,
    'contact_id' => 1,
    'application_received_date' => 'now',
    'decision_date' => 'next Monday',
    'amount_total' => '500',
    'status_id' => 1,
    'rationale' => 'Just Because',
    'currency' => 'USD',
    'grant_type_id' => 1,
  );

  require_once 'api/api.php';
  $result = civicrm_api('grant', 'get', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function grant_get_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 2,
    'values' => array(
      '2' => array(
        'id' => '2',
        'contact_id' => '1',
        'application_received_date' => '2012-02-10',
        'decision_date' => '2012-02-13',
        'grant_type_id' => '1',
        'amount_total' => '500.00',
        'currency' => 'USD',
        'rationale' => 'Just Because',
        'status_id' => '1',
      ),
    ),
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetGrant and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/GrantTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

