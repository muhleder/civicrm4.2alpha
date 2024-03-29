<?php
// $Id$

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * new version of civicrm apis. See blog post at
 * http://civicrm.org/node/131
 * @todo Write sth
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Contact
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id: Contact.php 30879 2010-11-22 15:45:55Z shot $
 *
 */

/**
 * Create or update a contact (note you should always call this via civicrm_api() & never directly)
 *
 * @param  array   $params   input parameters
 *
 * Allowed @params array keys are:
 * {@getfields contact_create}
 *
 *
 * @example ContactCreate.php Example of Create Call
 *
 * @return array  API Result Array
 *
 * @static void
 * @access public
 */
function civicrm_api3_contact_create($params) {

  $contactID = CRM_Utils_Array::value('contact_id', $params, CRM_Utils_Array::value('id', $params));
  $dupeCheck = CRM_Utils_Array::value('dupe_check', $params, FALSE);
  $values    = _civicrm_api3_contact_check_params($params, $dupeCheck);
  if ($values) {
    return $values;
  }

  if (empty($contactID)) {


    // If we get here, we're ready to create a new contact
    if (($email = CRM_Utils_Array::value('email', $params)) && !is_array($params['email'])) {
      require_once 'CRM/Core/BAO/LocationType.php';
      $defLocType = CRM_Core_BAO_LocationType::getDefault();
      $params['email'] = array(1 => array('email' => $email,
          'is_primary' => 1,
          'location_type_id' => ($defLocType->id) ? $defLocType->id : 1,
        ),
      );
    }
  }

  if ($homeUrl = CRM_Utils_Array::value('home_url', $params)) {
    require_once 'CRM/Core/PseudoConstant.php';
    $websiteTypes = CRM_Core_PseudoConstant::websiteType();
    $params['website'] = array(1 => array('website_type_id' => key($websiteTypes),
        'url' => $homeUrl,
      ),
    );
  }

  if (isset($params['suffix_id']) &&
    !(is_numeric($params['suffix_id']))
  ) {
    $params['suffix_id'] = array_search($params['suffix_id'], CRM_Core_PseudoConstant::individualSuffix());
  }

  if (isset($params['prefix_id']) &&
    !(is_numeric($params['prefix_id']))
  ) {
    $params['prefix_id'] = array_search($params['prefix_id'], CRM_Core_PseudoConstant::individualPrefix());
  }

  if (isset($params['gender_id'])
    && !(is_numeric($params['gender_id']))
  ) {
    $params['gender_id'] = array_search($params['gender_id'], CRM_Core_PseudoConstant::gender());
  }

  $error = _civicrm_api3_greeting_format_params($params);
  if (civicrm_error($error)) {
    return $error;
  }

  $values = array();
  $entityId = $contactID;

  if (!CRM_Utils_Array::value('contact_type', $params) &&
    $entityId
  ) {
    $params['contact_type'] = CRM_Contact_BAO_Contact::getContactType($entityId);
  }

  if (!($csType = CRM_Utils_Array::value('contact_sub_type', $params)) &&
    $entityId
  ) {
    require_once 'CRM/Contact/BAO/Contact.php';
    $csType = CRM_Contact_BAO_Contact::getContactSubType($entityId);
  }

  _civicrm_api3_custom_format_params($params, $values, $params['contact_type'], $entityId);

  $params = array_merge($params, $values);

  $contact = &_civicrm_api3_contact_update($params, $contactID);

  if (is_a($contact, 'CRM_Core_Error')) {
    return civicrm_api3_create_error($contact->_errors[0]['message']);
  }
  else {
    $values = array();
    _civicrm_api3_object_to_array_unique_fields($contact, $values[$contact->id]);
  }

  return civicrm_api3_create_success($values, $params, 'Contact', 'create');

  return civicrm_api3_contact_update($params, $create_new);
}

/*
 * Adjust Metadata for Create action
 * 
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_contact_create_spec(&$params) {
  $params['contact_type']['api.required'] = 1;
}

/**
 * Retrieve one or more contacts, given a set of search params
 *
 * @param  array  input parameters
 *
 * @return array API Result Array
 * (@getfields contact_get}
 * @static void
 * @access public
 *
 * @example ContactGet.php Standard GET example
 *
 * @todo EM 7 Jan 11 - does this return the number of contacts if required (replacement for deprecated contact_search_count function - if so is this tested?
 */
function civicrm_api3_contact_get($params) {

  if (isset($params['showAll'])) {
    if (strtolower($params['showAll']) == "active") {
      $params['contact_is_deleted'] = 0;
    }
    if (strtolower($params['showAll']) == "trash") {
      $params['contact_is_deleted'] = 1;
    }
    if (strtolower($params['showAll']) == "all" && isset($params['contact_is_deleted'])) {
      unset($params['contact_is_deleted']);
    }
  }
  // CRM-9890 get options from params should be the function that does all this sorting.
  // we don't need to redefine these into variables below - can just use them in the array
  // but it's going to make it easier to copy into the other functions so leave for now
  //& tidy up in a later re-factor
  $options          = _civicrm_api3_get_options_from_params($params, TRUE);
  $sort             = CRM_Utils_Array::value('sort', $options, NULL);
  $offset           = CRM_Utils_Array::value('offset', $options);
  $rowCount         = CRM_Utils_Array::value('limit', $options);
  $smartGroupCache  = CRM_Utils_Array::value('smartGroupCache', $params);
  $inputParams      = CRM_Utils_Array::value('input_params', $options, array());
  $returnProperties = CRM_Utils_Array::value('return', $options, NULL);


  if (array_key_exists('filter_group_id', $params)) {
    $params['filter.group_id'] = $params['filter_group_id'];
    unset($params['filter_group_id']);
  }
  // filter.group_id works both for 1,2,3 and array (1,2,3)
  if (array_key_exists('filter.group_id', $params)) {
    if (is_array($params['filter.group_id'])) {
      $groups = $params['filter.group_id'];
    }
    else $groups = explode(',', $params['filter.group_id']);
    unset($params['filter.group_id']);
    $groups = array_flip($groups);
    $groups[key($groups)] = 1;
    $inputParams['group'] = $groups;
  }

  require_once 'CRM/Contact/BAO/Query.php';
  $newParams = CRM_Contact_BAO_Query::convertFormValues($inputParams);
  list($contacts, $options) = CRM_Contact_BAO_Query::apiQuery($newParams,
    $returnProperties,
    NULL,
    $sort,
    $offset,
    $rowCount,
    $smartGroupCache
  );
  // CRM-7929 Quick fix by colemanw
  // TODO: Figure out what function is responsible for prepending 'individual_' to these keys
  // and sort it out there rather than going to all this trouble here.
  $returnContacts = array();
  if (is_array($contacts)) {
    foreach ($contacts as $cid => $contact) {
      if (is_array($contact)) {
        $returnContacts[$cid] = array();
        foreach ($contact as $key => $value) {
          $key = str_replace(array('individual_prefix', 'individual_suffix'), array('prefix', 'suffix'), $key);
          $returnContacts[$cid][$key] = $value;
        }
      }
    }
  }
  return civicrm_api3_create_success($returnContacts, $params, 'contact');
}
/*
 * Adjust Metadata for Get action
 * 
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_contact_get_spec(&$params) {
  $params['contact_is_deleted']['api.default'] = 0;
}

/**
 * Delete a contact with given contact id
 *
 * @param  array   	  $params (reference ) input parameters, contact_id element required
 *
 * @return array API Result Array
 * @access public
 *
 * @example ContactDelete.php
 * {@getfields contact_delete}
 */
function civicrm_api3_contact_delete($params) {


  require_once 'CRM/Contact/BAO/Contact.php';
  civicrm_api3_verify_mandatory($params, NULL, array('id'));
  $contactID = CRM_Utils_Array::value('id', $params);


  $session = CRM_Core_Session::singleton();
  if ($contactID == $session->get('userID')) {
    return civicrm_api3_create_error('This contact record is linked to the currently logged in user account - and cannot be deleted.');
  }
  $restore = CRM_Utils_Array::value('restore', $params) ? $params['restore'] : FALSE;
  $skipUndelete = CRM_Utils_Array::value('skip_undelete', $params) ? $params['skip_undelete'] : FALSE;
  if (CRM_Contact_BAO_Contact::deleteContact($contactID, $restore, $skipUndelete)) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error('Could not delete contact');
  }
}

function _civicrm_api3_contact_check_params(&$params, $dupeCheck = TRUE, $dupeErrorArray = FALSE, $requiredCheck = TRUE, $dedupeRuleGroupID = NULL) {
  if (isset($params['id']) && is_numeric($params['id'])) {
    $requiredCheck = FALSE;
  }
  if ($requiredCheck) {
    if (isset($params['id'])) {
      $required = array('Individual', 'Household', 'Organization');
    }
    $required = array(
      'Individual' => array(
        array('first_name', 'last_name'),
        'email',
      ),
      'Household' => array(
        'household_name',
      ),
      'Organization' => array(
        'organization_name',
      ),
    );


    // contact_type has a limited number of valid values
    $fields = CRM_Utils_Array::value($params['contact_type'], $required);
    if ($fields == NULL) {
      return civicrm_api3_create_error("Invalid Contact Type: {$params['contact_type']}");
    }

    if ($csType = CRM_Utils_Array::value('contact_sub_type', $params)) {
      if (!(CRM_Contact_BAO_ContactType::isExtendsContactType($csType, $params['contact_type']))) {
        return civicrm_api3_create_error("Invalid or Mismatched Contact SubType: " . implode(', ', (array)$csType));
      }
    }

    if (!CRM_Utils_Array::value('contact_id', $params) && CRM_Utils_Array::value('id', $params)) {
      $valid = FALSE;
      $error = '';
      foreach ($fields as $field) {
        if (is_array($field)) {
          $valid = TRUE;
          foreach ($field as $element) {
            if (!CRM_Utils_Array::value($element, $params)) {
              $valid = FALSE;
              $error .= $element;
              break;
            }
          }
        }
        else {
          if (CRM_Utils_Array::value($field, $params)) {
            $valid = TRUE;
          }
        }
        if ($valid) {
          break;
        }
      }

      if (!$valid) {
        return civicrm_api3_create_error("Required fields not found for {$params['contact_type']} : $error");
      }
    }
  }

  if ($dupeCheck) {
    // check for record already existing
    require_once 'CRM/Dedupe/Finder.php';
    $dedupeParams = CRM_Dedupe_Finder::formatParams($params, $params['contact_type']);

    // CRM-6431
    // setting 'check_permission' here means that the dedupe checking will be carried out even if the
    // person does not have permission to carry out de-dupes
    // this is similar to the front end form
    if (isset($params['check_permission'])) {
      $dedupeParams['check_permission'] = $params['check_permission'];
    }

    $ids = implode(',', CRM_Dedupe_Finder::dupesByParams($dedupeParams, $params['contact_type'], 'Strict', array(), $dedupeRuleGroupID));

    if ($ids != NULL) {
      if ($dupeErrorArray) {
        $error = CRM_Core_Error::createError("Found matching contacts: $ids",
          CRM_Core_Error::DUPLICATE_CONTACT,
          'Fatal', $ids
        );
        return civicrm_api3_create_error($error->pop());
      }

      return civicrm_api3_create_error("Found matching contacts: $ids");
    }
  }

  //check for organisations with same name
  if (CRM_Utils_Array::value('current_employer', $params)) {
    $organizationParams = array();
    $organizationParams['organization_name'] = $params['current_employer'];

    require_once 'CRM/Dedupe/Finder.php';
    $dedupParams = CRM_Dedupe_Finder::formatParams($organizationParams, 'Organization');

    $dedupParams['check_permission'] = FALSE;
    $dupeIds = CRM_Dedupe_Finder::dupesByParams($dedupParams, 'Organization', 'Fuzzy');

    // check for mismatch employer name and id
    if (CRM_Utils_Array::value('employer_id', $params)
      && !in_array($params['employer_id'], $dupeIds)
    ) {
      return civicrm_api3_create_error('Employer name and Employer id Mismatch');
    }

    // show error if multiple organisation with same name exist
    if (!CRM_Utils_Array::value('employer_id', $params)
      && (count($dupeIds) > 1)
    ) {
      return civicrm_api3_create_error('Found more than one Organisation with same Name.');
    }
  }

  return NULL;
}

/**
 * Takes an associative array and creates a contact object and all the associated
 * derived objects (i.e. individual, location, email, phone etc)
 *
 * @param array $params (reference ) an assoc array of name/value pairs
 * @param  int     $contactID        if present the contact with that ID is updated
 *
 * @return object CRM_Contact_BAO_Contact object
 * @access public
 * @static
 */
function _civicrm_api3_contact_update($params, $contactID = NULL) {
  require_once 'CRM/Core/Transaction.php';
  $transaction = new CRM_Core_Transaction();

  if ($contactID) {
    $params['contact_id'] = $contactID;
  }
  require_once 'CRM/Contact/BAO/Contact.php';

  $contact = CRM_Contact_BAO_Contact::create($params);

  $transaction->commit();

  return $contact;
}

/**
 * Validate the addressee or email or postal greetings
 *
 * @param  $params                   Associative array of property name/value
 *                                   pairs to insert in new contact.
 *
 * @return array (reference )        null on success, error message otherwise
 *
 * @access public
 */
function _civicrm_api3_greeting_format_params($params) {
  $greetingParams = array('', '_id', '_custom');
  foreach (array('email', 'postal', 'addressee') as $key) {
    $greeting = '_greeting';
    if ($key == 'addressee') {
      $greeting = '';
    }

    $formatParams = FALSE;
    // unset display value from params.
    if (isset($params["{$key}{$greeting}_display"])) {
      unset($params["{$key}{$greeting}_display"]);
    }

    // check if greetings are present in present
    foreach ($greetingParams as $greetingValues) {
      if (array_key_exists("{$key}{$greeting}{$greetingValues}", $params)) {
        $formatParams = TRUE;
        break;
      }
    }

    if (!$formatParams) {

      continue;

    }

    // format params
    if (CRM_Utils_Array::value('contact_type', $params) == 'Organization' && $key != 'addressee') {
      return civicrm_api3_create_error(ts('You cannot use email/postal greetings for contact type %1.',
          array(1 => $params['contact_type'])
        ));
    }

    $nullValue = FALSE;
    $filter = array('contact_type' => $params['contact_type'],
      'greeting_type' => "{$key}{$greeting}",
    );

    $greetings      = CRM_Core_PseudoConstant::greeting($filter);
    $greetingId     = CRM_Utils_Array::value("{$key}{$greeting}_id", $params);
    $greetingVal    = CRM_Utils_Array::value("{$key}{$greeting}", $params);
    $customGreeting = CRM_Utils_Array::value("{$key}{$greeting}_custom", $params);

    if (!$greetingId && $greetingVal) {
      $params["{$key}{$greeting}_id"] = CRM_Utils_Array::key($params["{$key}{$greeting}"], $greetings);
    }

    if ($customGreeting && $greetingId &&
      ($greetingId != array_search('Customized', $greetings))
    ) {
      return civicrm_api3_create_error(ts('Provide either %1 greeting id and/or %1 greeting or custom %1 greeting',
          array(1 => $key)
        ));
    }

    if ($greetingVal && $greetingId &&
      ($greetingId != CRM_Utils_Array::key($greetingVal, $greetings))
    ) {
      return civicrm_api3_create_error(ts('Mismatch in %1 greeting id and %1 greeting',
          array(1 => $key)
        ));
    }

    if ($greetingId) {

      if (!array_key_exists($greetingId, $greetings)) {
        return civicrm_api3_create_error(ts('Invalid %1 greeting Id', array(1 => $key)));
      }

      if (!$customGreeting && ($greetingId == array_search('Customized', $greetings))) {
        return civicrm_api3_create_error(ts('Please provide a custom value for %1 greeting',
            array(1 => $key)
          ));
      }
    }
    elseif ($greetingVal) {

      if (!in_array($greetingVal, $greetings)) {
        return civicrm_api3_create_error(ts('Invalid %1 greeting', array(1 => $key)));
      }

      $greetingId = CRM_Utils_Array::key($greetingVal, $greetings);
    }

    if ($customGreeting) {
      $greetingId = CRM_Utils_Array::key('Customized', $greetings);
    }

    $customValue = $params['contact_id'] ? CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
      $params['contact_id'],
      "{$key}{$greeting}_custom"
    ) : FALSE;

    if (array_key_exists("{$key}{$greeting}_id", $params) && empty($params["{$key}{$greeting}_id"])) {
      $nullValue = TRUE;
    }
    elseif (array_key_exists("{$key}{$greeting}", $params) && empty($params["{$key}{$greeting}"])) {
      $nullValue = TRUE;
    }
    elseif ($customValue && array_key_exists("{$key}{$greeting}_custom", $params)
      && empty($params["{$key}{$greeting}_custom"])
    ) {
      $nullValue = TRUE;
    }

    $params["{$key}{$greeting}_id"] = $greetingId;

    if (!$customValue && !$customGreeting && array_key_exists("{$key}{$greeting}_custom", $params)) {
      unset($params["{$key}{$greeting}_custom"]);
    }

    if ($nullValue) {
      $params["{$key}{$greeting}_id"] = '';
      $params["{$key}{$greeting}_custom"] = '';
    }

    if (isset($params["{$key}{$greeting}"])) {
      unset($params["{$key}{$greeting}"]);
    }
  }
}

/**
 * Contact quick search api
 *
 * @access public
 *
 * {@example ContactQuicksearch.php 0}
 *
 */
function civicrm_api3_contact_quicksearch($params) {
  civicrm_api3_verify_mandatory($params, NULL, array('name'));

  $name = CRM_Utils_Array::value('name', $params);

  // get the autocomplete options from settings
  require_once 'CRM/Core/BAO/Setting.php';
  $acpref = explode(CRM_Core_DAO::VALUE_SEPARATOR,
    CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
      'contact_autocomplete_options'
    )
  );

  // get the option values for contact autocomplete
  $acOptions = CRM_Core_OptionGroup::values('contact_autocomplete_options', FALSE, FALSE, FALSE, NULL, 'name');

  $list = array();
  foreach ($acpref as $value) {
    if ($value && CRM_Utils_Array::value($value, $acOptions)) {
      $list[$value] = $acOptions[$value];
    }
  }

  $select = $actualSelectElements = array('sort_name');
  $where  = '';
  $from   = array();
  foreach ($list as $value) {
    $suffix = substr($value, 0, 2) . substr($value, -1);
    switch ($value) {
      case 'street_address':
      case 'city':
        $selectText = $value;
        $value      = "address";
        $suffix     = 'sts';
      case 'phone':
      case 'email':
        $actualSelectElements[] = $select[] = ($value == 'address') ? $selectText : $value;
        $from[$value] = "LEFT JOIN civicrm_{$value} {$suffix} ON ( cc.id = {$suffix}.contact_id AND {$suffix}.is_primary = 1 ) ";
        break;

      case 'country':
      case 'state_province':
        $select[] = "{$suffix}.name as {$value}";
        $actualSelectElements[] = "{$suffix}.name";
        if (!in_array('address', $from)) {
          $from['address'] = 'LEFT JOIN civicrm_address sts ON ( cc.id = sts.contact_id AND sts.is_primary = 1) ';
        }
        $from[$value] = " LEFT JOIN civicrm_{$value} {$suffix} ON ( sts.{$value}_id = {$suffix}.id  ) ";
        break;
    }
  }

  $config = CRM_Core_Config::singleton();
  $as     = $select;
  $select = implode(', ', $select);
  if (!empty($select)) {
    $select = ", $select";
  }
  $actualSelectElements = implode(', ', $actualSelectElements);
  $selectAliases = $from;
  unset($selectAliases['address']);
  $selectAliases = implode(', ', array_keys($selectAliases));
  if (!empty($selectAliases)) {
    $selectAliases = ", $selectAliases";
  }
  $from = implode(' ', $from);
  $limit = CRM_Utils_Array::value('limit', $params, 10);

  // add acl clause here
  require_once 'CRM/Contact/BAO/Contact/Permission.php';
  list($aclFrom, $aclWhere) = CRM_Contact_BAO_Contact_Permission::cacheClause('cc');

  if ($aclWhere) {
    $where .= " AND $aclWhere ";
  }

  if (CRM_Utils_Array::value('org', $params)) {
    $where .= " AND contact_type = \"Organization\"";
    //set default for current_employer
    if ($orgId = CRM_Utils_Array::value('id', $params)) {
      $where .= " AND cc.id = {$orgId}";
    }

    // CRM-7157, hack: get current employer details when
    // employee_id is present.
    $currEmpDetails = array();
    if (CRM_Utils_Array::value('employee_id', $params)) {
      if ($currentEmployer = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
          CRM_Utils_Array::value('employee_id', $params),
          'employer_id'
        )) {
        if ($config->includeWildCardInName) {
          $strSearch = "%$name%";
        }
        else {
          $strSearch = "$name%";
        }

        // get current employer details
        $dao = CRM_Core_DAO::executeQuery("SELECT cc.id as id, CONCAT_WS( ' :: ', {$actualSelectElements} ) as data, sort_name
                    FROM civicrm_contact cc {$from} WHERE cc.contact_type = \"Organization\" AND cc.id = {$currentEmployer} AND cc.sort_name LIKE '$strSearch'");
        if ($dao->fetch()) {
          $currEmpDetails = array('id' => $dao->id,
            'data' => $dao->data,
          );
        }
      }
    }
  }

  if (CRM_Utils_Array::value('cid', $params)) {
    $where .= " AND cc.id <> {$params['cid']}";
  }

  //contact's based of relationhip type
  $relType = NULL;
  if (CRM_Utils_Array::value('rel', $params)) {
    $relation = explode('_', CRM_Utils_Array::value('rel', $params));
    $relType  = CRM_Utils_Type::escape($relation[0], 'Integer');
    $rel      = CRM_Utils_Type::escape($relation[2], 'String');
  }

  if ($config->includeWildCardInName) {
    $strSearch = "%$name%";
  }
  else {
    $strSearch = "$name%";
  }
  $includeEmailFrom = $includeNickName = $exactIncludeNickName = '';
  if ($config->includeNickNameInName) {
    $includeNickName = " OR nick_name LIKE '$strSearch'";
    $exactIncludeNickName = " OR nick_name LIKE '$name'";
  }

  if ($config->includeEmailInName) {
    if (!in_array('email', $list)) {
      $includeEmailFrom = "LEFT JOIN civicrm_email eml ON ( cc.id = eml.contact_id AND eml.is_primary = 1 )";
    }
    $whereClause = " WHERE ( email LIKE '$strSearch' OR sort_name LIKE '$strSearch' $includeNickName ) {$where} ";
    $exactWhereClause = " WHERE ( email LIKE '$name' OR sort_name LIKE '$name' $exactIncludeNickName ) {$where} ";
  }
  else {
    $whereClause = " WHERE ( sort_name LIKE '$strSearch' $includeNickName ) {$where} ";
    $exactWhereClause = " WHERE ( sort_name LIKE '$name' $exactIncludeNickName ) {$where} ";
  }
  $additionalFrom = '';
  if ($relType) {
    $additionalFrom = "
            INNER JOIN civicrm_relationship_type r ON ( 
                r.id = {$relType}
                AND ( cc.contact_type = r.contact_type_{$rel} OR r.contact_type_{$rel} IS NULL )
                AND ( cc.contact_sub_type = r.contact_sub_type_{$rel} OR r.contact_sub_type_{$rel} IS NULL )
            )";
  }

  //CRM-5954
  $query = "
        SELECT DISTINCT(id), data, sort_name {$selectAliases}
        FROM   (
            ( SELECT 0 as exactFirst, cc.id as id, CONCAT_WS( ' :: ', {$actualSelectElements} ) as data {$select}
            FROM   civicrm_contact cc {$from}
    {$aclFrom}
    {$additionalFrom} {$includeEmailFrom}
    {$exactWhereClause}
    LIMIT 0, {$limit} )
    UNION
    ( SELECT 1 as exactFirst, cc.id as id, CONCAT_WS( ' :: ', {$actualSelectElements} ) as data {$select}
    FROM   civicrm_contact cc {$from}
    {$aclFrom}
    {$additionalFrom} {$includeEmailFrom}
    {$whereClause}
    ORDER BY sort_name
    LIMIT 0, {$limit} )
) t
ORDER BY exactFirst, sort_name
LIMIT    0, {$limit}
    ";
  // send query to hook to be modified if needed
  require_once 'CRM/Utils/Hook.php';
  CRM_Utils_Hook::contactListQuery($query,
    $name,
    CRM_Utils_Array::value('context', $params),
    CRM_Utils_Array::value('id', $params)
  );

  $dao = CRM_Core_DAO::executeQuery($query);

  $contactList = array();
  $listCurrentEmployer = TRUE;
  while ($dao->fetch()) {
    $t = array('id' => $dao->id);
    foreach ($as as $k) {
      $t[$k] = $dao->$k;
    }
    $t['data'] = $dao->data;
    $contactList[] = $t;
    if (CRM_Utils_Array::value('org', $params) &&
      !empty($currEmpDetails) &&
      $dao->id == $currEmpDetails['id']
    ) {
      $listCurrentEmployer = FALSE;
    }
  }

  //return organization name if doesn't exist in db
  if (empty($contactList)) {
    if (CRM_Utils_Array::value('org', $params)) {
      if ($listCurrentEmployer && !empty($currEmpDetails)) {
        $contactList = array('data' => $currEmpDetails['data'],
          'id' => $currEmpDetails['id'],
        );
      }
      else {
        $contactList = array('data' => CRM_Utils_Array::value('s', $params),
          'id' => CRM_Utils_Array::value('s', $params),
        );
      }
    }
  }

  return civicrm_api3_create_success($contactList, $params);
}

/**
 * Merges given pair of duplicate contacts.
 *
 * @param  array   $params   input parameters
 *
 * Allowed @params array keys are:
 * {int     main_id     main contact id with whom merge has to happen}
 * {int     other_id    duplicate contact which would be deleted after merge operation}
 * {string  mode        helps decide how to behave when there are conflicts.
 *                      A 'safe' value skips the merge if there are no conflicts. Does a force merge otherwise.}
 * {boolean auto_flip   wether to let api decide which contact to retain and which to delete.}
 *
 * @return array  API Result Array
 *
 * @static void
 * @access public
 */
function civicrm_api3_contact_merge($params) {
  $mode = CRM_Utils_Array::value('mode', $params, 'safe');
  $autoFlip = CRM_Utils_Array::value('auto_flip', $params, TRUE);

  require_once 'CRM/Dedupe/Merger.php';
  $dupePairs = array(array('srcID' => CRM_Utils_Array::value('main_id', $params),
      'dstID' => CRM_Utils_Array::value('other_id', $params),
    ));
  $result = CRM_Dedupe_Merger::merge($dupePairs, array(), $mode, $autoFlip);

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}

