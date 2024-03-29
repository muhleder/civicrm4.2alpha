<?php
// $Id$



/*
 test demonstrates the syntax to create 2 chained entities
 */
function contact_create_example() {
  $params = array(
    'first_name' => 'abc3',
    'last_name' => 'xyz3',
    'contact_type' => 'Individual',
    'email' => 'man3@yahoo.com',
    'version' => 3,
    'api.contribution.create' => array(
      'receive_date' => '2010-01-01',
      'total_amount' => '100',
      'contribution_type_id' => 1,
      'payment_instrument_id' => 1,
      'non_deductible_amount' => '10',
      'fee_amount' => '50',
      'net_amount' => '90',
      'trxn_id' => 15345,
      'invoice_id' => 67990,
      'source' => 'SSF',
      'contribution_status_id' => 1,
    ),
    'api.website.create' => array(
      'url' => 'http://civicrm.org',
    ),
    'api.website.create.2' => array(
      'url' => 'http://chained.org',
    ),
  );

  require_once 'api/api.php';
  $result = civicrm_api('contact', 'create', $params);

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_create_expectedresult() {

  $expectedResult = array(
    'is_error' => 0,
    'version' => 3,
    'count' => 1,
    'id' => 1,
    'values' => array(
      '1' => array(
        'id' => 1,
        'contact_type' => 'Individual',
        'contact_sub_type' => 'null',
        'do_not_email' => '',
        'do_not_phone' => '',
        'do_not_mail' => '',
        'do_not_sms' => '',
        'do_not_trade' => '',
        'is_opt_out' => '',
        'legal_identifier' => '',
        'external_identifier' => '',
        'sort_name' => 'xyz3, abc3',
        'display_name' => 'abc3 xyz3',
        'nick_name' => '',
        'legal_name' => '',
        'image_URL' => '',
        'preferred_communication_method' => '',
        'preferred_language' => 'en_US',
        'preferred_mail_format' => '',
        'api_key' => '',
        'first_name' => 'abc3',
        'middle_name' => '',
        'last_name' => 'xyz3',
        'prefix_id' => '',
        'suffix_id' => '',
        'email_greeting_id' => '',
        'email_greeting_custom' => '',
        'email_greeting_display' => '',
        'postal_greeting_id' => '',
        'postal_greeting_custom' => '',
        'postal_greeting_display' => '',
        'addressee_id' => '',
        'addressee_custom' => '',
        'addressee_display' => '',
        'job_title' => '',
        'gender_id' => '',
        'birth_date' => '',
        'is_deceased' => '',
        'deceased_date' => '',
        'household_name' => '',
        'primary_contact_id' => '',
        'organization_name' => '',
        'sic_code' => '',
        'user_unique_id' => '',
        'api.contribution.create' => array(
          'is_error' => 0,
          'version' => 3,
          'count' => 1,
          'id' => 1,
          'values' => array(
            '0' => array(
              'id' => 1,
              'contact_id' => 1,
              'contribution_type_id' => 1,
              'contribution_page_id' => '',
              'payment_instrument_id' => 1,
              'receive_date' => '20100101000000',
              'non_deductible_amount' => '10',
              'total_amount' => '100',
              'fee_amount' => '50',
              'net_amount' => '90',
              'trxn_id' => 15345,
              'invoice_id' => 67990,
              'currency' => 'USD',
              'cancel_date' => '',
              'cancel_reason' => '',
              'receipt_date' => '',
              'thankyou_date' => '',
              'source' => 'SSF',
              'amount_level' => '',
              'contribution_recur_id' => '',
              'honor_contact_id' => '',
              'is_test' => '',
              'is_pay_later' => '',
              'contribution_status_id' => 1,
              'honor_type_id' => '',
              'address_id' => '',
              'check_number' => 'null',
              'campaign_id' => '',
            ),
          ),
        ),
        'api.website.create' => array(
          'is_error' => 0,
          'version' => 3,
          'count' => 1,
          'id' => 1,
          'values' => array(
            '0' => array(
              'id' => 1,
              'contact_id' => 1,
              'url' => 'http://civicrm.org',
              'website_type_id' => '',
            ),
          ),
        ),
        'api.website.create.2' => array(
          'is_error' => 0,
          'version' => 3,
          'count' => 1,
          'id' => 2,
          'values' => array(
            '0' => array(
              'id' => 2,
              'contact_id' => 1,
              'url' => 'http://chained.org',
              'website_type_id' => '',
            ),
          ),
        ),
      ),
    ),
  );

  return $expectedResult;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testCreateIndividualWithContributionDottedSyntax and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/ContactTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/

