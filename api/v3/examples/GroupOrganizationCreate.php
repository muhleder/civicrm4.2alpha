<?php
// $Id$



/*
 
 */
function group_organization_create_example() {
  $params = array(
    'organization_id' => 1,
    'group_id' => 1,
    'version' => 3,
  );

  require_once 'api/api.php';
  $result = civicrm_api('group_organization', 'create', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_organization_create_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 3,
    'id' => 3,
    'values' => array(
      'id' => 3,
      'group_id' => 1,
      'organization_id' => 1,
    ),
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGroupOrganizationCreate and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/GroupOrganizationTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

