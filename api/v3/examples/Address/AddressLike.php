<?php
// $Id$



/*
 Demonstrates Use of Like
 */
function address_get_example() {
  $params = array(
    'street_address' => array(
      'LIKE' => '%mb%',
    ),
    'version' => 3,
    'sequential' => 1,
  );

  require_once 'api/api.php';
  $result = civicrm_api('address', 'get', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function address_get_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 9,
    'values' => array(
      '0' => array(
        'id' => '9',
        'contact_id' => '1',
        'location_type_id' => '13',
        'is_primary' => '1',
        'is_billing' => 0,
        'street_address' => 'Ambachtstraat 23',
        'street_number' => '23',
        'street_name' => 'Ambachtstraat',
        'city' => 'Brummen',
        'postal_code' => '6971 BN',
        'country_id' => '1152',
      ),
    ),
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetAddressLikeSuccess and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/AddressTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

