<?php
// $Id$



/*
 
 */
function membership_payment_create_example() {
  $params = array(
    'contribution_id' => 1,
    'membership_id' => 1,
    'version' => 3,
  );

  require_once 'api/api.php';
  $result = civicrm_api('membership_payment', 'create', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_payment_create_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 1,
    'values' => array(
      '1' => array(
        'id' => 1,
        'membership_id' => 1,
        'contribution_id' => 1,
      ),
    ),
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testCreate and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/MembershipPaymentTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

