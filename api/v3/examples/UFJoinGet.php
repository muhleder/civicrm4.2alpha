<?php
// $Id$



/*
 
 */
function uf_join_get_example() {
  $params = array(
    'entity_table' => 'civicrm_contribution_page',
    'entity_id' => 1,
    'version' => 3,
    'sequential' => 1,
  );

  require_once 'api/api.php';
  $result = civicrm_api('uf_join', 'get', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_join_get_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 1,
    'values' => array(
      '0' => array(
        'id' => '1',
        'is_active' => '1',
        'module' => 'CiviContribute',
        'entity_table' => 'civicrm_contribution_page',
        'entity_id' => '1',
        'weight' => '1',
        'uf_group_id' => '11',
      ),
    ),
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetUFJoinId and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/UFJoinTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

