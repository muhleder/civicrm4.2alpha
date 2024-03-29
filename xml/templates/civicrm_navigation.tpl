-- +--------------------------------------------------------------------+
-- | CiviCRM version 4.1                                                |
-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC (c) 2004-2011                                |
-- +--------------------------------------------------------------------+
-- | This file is a part of CiviCRM.                                    |
-- |                                                                    |
-- | CiviCRM is free software; you can copy, modify, and distribute it  |
-- | under the terms of the GNU Affero General Public License           |
-- | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
-- |                                                                    |
-- | CiviCRM is distributed in the hope that it will be useful, but     |
-- | WITHOUT ANY WARRANTY; without even the implied warranty of         |
-- | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
-- | See the GNU Affero General Public License for more details.        |
-- |                                                                    |
-- | You should have received a copy of the GNU Affero General Public   |
-- | License and the CiviCRM Licensing Exception along                  |
-- | with this program; if not, contact CiviCRM LLC                     |
-- | at info[AT]civicrm[DOT]org. If you have questions about the        |
-- | GNU Affero General Public License or the licensing of CiviCRM,     |
-- | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
-- +--------------------------------------------------------------------+
-- Navigation Menu, Preferences and Mail Settings

SELECT @domainID := id FROM civicrm_domain where name = 'Default Domain Name';

-- Initial default state of system preferences

-- Initial default state of system settings
INSERT INTO civicrm_setting
  ( domain_id, contact_id, is_domain, group_name, name, value )
VALUES
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'contact_view_options', '{serialize}123456789101113{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'contact_edit_options', '{serialize}1234567891011{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'advanced_search_options', '{serialize}123456789101112131516171819{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'user_dashboard_options', '{serialize}1234578{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'address_options', '{serialize}123456891011{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'address_format', '{serialize}{literal}{contact.address_name}
{contact.street_address}
{contact.supplemental_address_1}
{contact.supplemental_address_2}
{contact.city}{, }{contact.state_province}{ }{contact.postal_code}
{contact.country}{/literal}{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'mailing_format', '{serialize}{literal}{contact.addressee}
{contact.street_address}
{contact.supplemental_address_1}
{contact.supplemental_address_2}
{contact.city}{, }{contact.state_province}{ }{contact.postal_code}
{contact.country}{/literal}{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'display_name_format', '{serialize}{literal}{contact.individual_prefix}{ }{contact.first_name}{ }{contact.last_name}{ }{contact.individual_suffix}{/literal}{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'sort_name_format', '{serialize}{literal}{contact.last_name}{, }{contact.first_name}{/literal}{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'editor_id', '{serialize}2{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'contact_ajax_check_similar', '{serialize}1{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'activity_assignee_notification', '{serialize}1{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'contact_autocomplete_options', '{serialize}12{/serialize}' ),
  ( @domainID, NULL, 1, 'CiviCRM Preferences', 'contact_reference_options', '{serialize}12{/serialize}' ),
  ( @domainID, NULL, 1, 'Address Standardization Preferences', 'address_standardization_provider', NULL ),
  ( @domainID, NULL, 1, 'Address Standardization Preferences', 'address_standardization_userid', NULL ),
  ( @domainID, NULL, 1, 'Address Standardization Preferences', 'address_standardization_url', NULL ),
  ( @domainID, NULL, 1, 'Campaign Preferences', 'tag_unconfirmed', '{serialize}Unconfirmed{/serialize}' ),
  ( @domainID, NULL, 1, 'Campaign Preferences', 'petition_contacts', '{serialize}Petition Contacts{/serialize}' ),
  ( @domainID, NULL, 1, 'Event Preferences'   , 'enable_cart', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Mailing Preferences', 'profile_double_optin', '{serialize}1{/serialize}' ),
  ( @domainID, NULL, 1, 'Mailing Preferences', 'profile_add_to_group_double_optin', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Mailing Preferences', 'track_civimail_replies', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Mailing Preferences', 'civimail_workflow', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Mailing Preferences', 'civimail_server_wide_lock', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Mailing Preferences', 'mailing_backend', {literal}'a:1:{s:15:"outBound_option";s:1:"3";}'{/literal} ),
  ( @domainID, NULL, 1, 'Member Preferences' , 'default_renewal_contribution_page', NULL ),
  ( @domainID, NULL, 1, 'Multi Site Preferences', 'is_enabled', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Multi Site Preferences', 'uniq_email_per_site', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Multi Site Preferences', 'domain_group_id', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Multi Site Preferences', 'event_price_set_domain_id', '{serialize}0{/serialize}' ),
  ( @domainID, NULL, 1, 'Directory Preferences', 'uploadDir'          , NULL ),
  ( @domainID, NULL, 1, 'Directory Preferences', 'imageUploadDir'     , NULL ),
  ( @domainID, NULL, 1, 'Directory Preferences', 'customFileUploadDir', NULL ),
  ( @domainID, NULL, 1, 'Directory Preferences', 'customTemplateDir'  , NULL ),
  ( @domainID, NULL, 1, 'Directory Preferences', 'customPHPPathDir'   , NULL ),
  ( @domainID, NULL, 1, 'Directory Preferences', 'extensionsDir'      , NULL ),
  ( @domainID, NULL, 1, 'URL Preferences', 'userFrameworkResourceURL' , NULL ),
  ( @domainID, NULL, 1, 'URL Preferences', 'imageUploadURL'           , NULL ),
  ( @domainID, NULL, 1, 'URL Preferences', 'customCSSURL'             , NULL );

-- mail settings 

INSERT INTO civicrm_mail_settings (domain_id, name, is_default, domain) VALUES (@domainID, 'default', true, 'FIXME.ORG');

-- activity and case dashlets 

INSERT INTO `civicrm_dashboard` 
    ( `domain_id`, `label`, `url`, `permission`, `permission_operator`, `column_no`, `is_minimized`, `is_active`, `weight`, `fullscreen_url`, `is_fullscreen`, `is_reserved`) 
    VALUES 
    ( @domainID, '{ts escape="sql"}Activities{/ts}', 'civicrm/dashlet/activity&reset=1&snippet=5', 'access CiviCRM', NULL, 0, 0, 1, 1, 'civicrm/dashlet/activity&reset=1&snippet=5&context=dashletFullscreen', 1, 1),
    ( @domainID, '{ts escape="sql"}My Cases{/ts}', 'civicrm/dashlet/myCases&reset=1&snippet=5', 'access my cases and activities', NULL , 0, 0, 1, 2, 'civicrm/dashlet/myCases&reset=1&snippet=5&context=dashletFullscreen', 1, 1),
    ( @domainID, '{ts escape="sql"}All Cases{/ts}', 'civicrm/dashlet/allCases&reset=1&snippet=5', 'access all cases and activities', NULL , 0, 0, 1, 3, 'civicrm/dashlet/allCases&reset=1&snippet=5&context=dashletFullscreen', 1, 1),
    ( @domainID, '{ts escape="sql"}Case Dashboard Dashlet{/ts}', 'civicrm/dashlet/casedashboard&reset=1&snippet=5', 'access CiviCase', NULL , 0, 0, 1, 4, 'civicrm/dashlet/casedashboard&reset=1&snippet=5&context=dashletFullscreen', 1, 1);

-- navigation 

INSERT INTO civicrm_navigation
 ( domain_id, label, name, url, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
 ( @domainID, '{ts escape="sql" skip="true"}Home{/ts}', 'Home', 'civicrm/dashboard&reset=1', NULL, '', NULL, 1, NULL, 0);
 
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    (  @domainID, NULL, '{ts escape="sql" skip="true"}Search{/ts}',  'Search...',    NULL, '',  NULL, '1', NULL, 10 );

SET @searchlastID:=LAST_INSERT_ID();
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/contact/search&reset=1',                          '{ts escape="sql" skip="true"}Find Contacts{/ts}',      'Find Contacts', NULL, '',                      @searchlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/contact/search/advanced&reset=1',                 '{ts escape="sql" skip="true"}Advanced Search{/ts}',    'Advanced Search', NULL, '', @searchlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/contact/search/custom&csid=15&reset=1',           '{ts escape="sql" skip="true"}Full-text Search{/ts}',   'Full-text Search', NULL, '',                   @searchlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/contact/search/builder&reset=1',                  '{ts escape="sql" skip="true"}Search Builder{/ts}',     'Search Builder', NULL, '',                     @searchlastID, '1', '1',  4 ), 
    ( @domainID, 'civicrm/case/search&reset=1',                             '{ts escape="sql" skip="true"}Find Cases{/ts}',         'Find Cases', 'access my cases and activities,access all cases and activities', 'OR',            @searchlastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/contribute/search&reset=1',                       '{ts escape="sql" skip="true"}Find Contributions{/ts}', 'Find Contributions', 'access CiviContribute', '',  @searchlastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/mailing&reset=1',                                 '{ts escape="sql" skip="true"}Find Mailings{/ts}',      'Find Mailings', 'access CiviMail', '',         @searchlastID, '1', NULL, 7 ), 
    ( @domainID, 'civicrm/member/search&reset=1',                           '{ts escape="sql" skip="true"}Find Memberships{/ts}',   'Find Memberships', 'access CiviMember', '',        @searchlastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/event/search&reset=1',                            '{ts escape="sql" skip="true"}Find Participants{/ts}',  'Find Participants',  'access CiviEvent', '',   @searchlastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/pledge/search&reset=1',                           '{ts escape="sql" skip="true"}Find Pledges{/ts}',       'Find Pledges', 'access CiviPledge', '',        @searchlastID, '1', NULL, 10 ),
    ( @domainID, 'civicrm/activity/search&reset=1',                         '{ts escape="sql" skip="true"}Find Activities{/ts}',    'Find Activities', NULL,  '',                   @searchlastID, '1', '1',  11 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/contact/search/custom/list&reset=1',              '{ts escape="sql" skip="true"}Custom Searches{/ts}', 'Custom Searches', NULL, '',                 @searchlastID, '1', NULL, 12 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL,  '{ts escape="sql" skip="true"}Contacts{/ts}', 'Contacts', NULL, '', NULL, '1', NULL, 20 );

SET @contactlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES        
    ( @domainID, 'civicrm/contact/add&reset=1&ct=Individual',       '{ts escape="sql" skip="true"}New Individual{/ts}',         'New Individual',       'add contacts',     '',             @contactlastID, '1', NULL,  1 ),
    ( @domainID, 'civicrm/contact/add&reset=1&ct=Household',        '{ts escape="sql" skip="true"}New Household{/ts}',          'New Household',        'add contacts',     '',             @contactlastID, '1', NULL,  2 ),
       ( @domainID, 'civicrm/contact/add&reset=1&ct=Organization',  '{ts escape="sql" skip="true"}New Organization{/ts}',       'New Organization',     'add contacts',     '',             @contactlastID, '1', 1,     3 );


INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/activity&reset=1&action=add&context=standalone',  '{ts escape="sql" skip="true"}New Activity{/ts}',           'New Activity',         NULL,               '',             @contactlastID, '1', NULL,  4 ), 
    ( @domainID, 'civicrm/activity/email/add&atype=3&action=add&reset=1&context=standalone', '{ts escape="sql" skip="true"}New Email{/ts}',   'New Email',      NULL,               '',             @contactlastID, '1', '1',   5 ), 
    ( @domainID, 'civicrm/import/contact&reset=1',                          '{ts escape="sql" skip="true"}Import Contacts{/ts}',        'Import Contacts',      'import contacts',  '',             @contactlastID, '1', NULL,  6 ), 
    ( @domainID, 'civicrm/import/activity&reset=1',                         '{ts escape="sql" skip="true"}Import Activities{/ts}',      'Import Activities',    'import contacts',  '',             @contactlastID, '1', '1',   7 ), 
    ( @domainID, 'civicrm/group/add&reset=1',                               '{ts escape="sql" skip="true"}New Group{/ts}',              'New Group',            'edit groups',      '',             @contactlastID, '1', NULL,  8 ), 
    ( @domainID, 'civicrm/group&reset=1',                                   '{ts escape="sql" skip="true"}Manage Groups{/ts}',          'Manage Groups',        'access CiviCRM',   '',             @contactlastID, '1', '1',   9 ), 
    ( @domainID, 'civicrm/admin/tag&reset=1&action=add',                    '{ts escape="sql" skip="true"}New Tag{/ts}',                'New Tag',              'administer CiviCRM', '',           @contactlastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/tag&reset=1',                               '{ts escape="sql" skip="true"}Manage Tags (Categories){/ts}', 'Manage Tags (Categories)', 'administer CiviCRM', '',     @contactlastID, '1','1', 11 ),
    ( @domainID, 'civicrm/contact/deduperules&reset=1',  '{ts escape="sql" skip="true"}Find and Merge Duplicate Contacts{/ts}', 'Find and Merge Duplicate Contacts', 'administer dedupe rules,merge duplicate contacts', 'OR', @contactlastID, '1', NULL, 12 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,  '{ts escape="sql" skip="true"}Contributions{/ts}', 'Contributions', 'access CiviContribute', '',      NULL,           '1', NULL,  30 );

SET @contributionlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/contribute&reset=1',                              '{ts escape="sql" skip="true"}Dashboard{/ts}',              'Dashboard',              'access CiviContribute', '', @contributionlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/contribute/add&reset=1&action=add&context=standalone', '{ts escape="sql" skip="true"}New Contribution{/ts}',  'New Contribution',       'access CiviContribute,edit contributions', 'AND', @contributionlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/contribute/search&reset=1',                       '{ts escape="sql" skip="true"}Find Contributions{/ts}',     'Find Contributions',     'access CiviContribute', '', @contributionlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/contribute/import&reset=1',                       '{ts escape="sql" skip="true"}Import Contributions{/ts}',   'Import Contributions',   'access CiviContribute,edit contributions', 'AND', @contributionlastID, '1', '1',  4 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID,NULL, '{ts escape="sql" skip="true"}Pledges{/ts}',  'Pledges', 'access CiviPledge', '', @contributionlastID, '1',  1,   5 );
    
SET @pledgelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/pledge&reset=1',                                  '{ts escape="sql" skip="true"}Dashboard{/ts}',                  'Dashboard',                 'access CiviPledge',  '',  @pledgelastID,       '1', NULL, 1 ), 
    ( @domainID, 'civicrm/pledge/add&reset=1&action=add&context=standalone', '{ts escape="sql" skip="true"}New Pledge{/ts}',                'New Pledge',                'access CiviPledge,edit pledges',  'AND',  @pledgelastID,       '1', NULL, 2 ),
    ( @domainID, 'civicrm/pledge/search&reset=1',                           '{ts escape="sql" skip="true"}Find Pledges{/ts}',               'Find Pledges',              'access CiviPledge',  '',  @pledgelastID,       '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/contribute/add&reset=1&action=add',         '{ts escape="sql" skip="true"}New Contribution Page{/ts}',      'New Contribution Page',     'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/contribute&reset=1',                        '{ts escape="sql" skip="true"}Manage Contribution Pages{/ts}',  'Manage Contribution Pages', 'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', '1',  7 ), 
    ( @domainID, 'civicrm/admin/pcp?reset=1&page_type=contribute',          '{ts escape="sql" skip="true"}Personal Campaign Pages{/ts}',    'Personal Campaign Pages',   'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/contribute/managePremiums&reset=1',         '{ts escape="sql" skip="true"}Premiums (Thank-you Gifts){/ts}', 'Premiums',                  'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', 1,    9 ),
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',                  '{ts escape="sql" skip="true"}New Price Set{/ts}',              'New Price Set',             'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', NULL, 10 ),
    ( @domainID, 'civicrm/admin/price&reset=1',                             '{ts escape="sql" skip="true"}Manage Price Sets{/ts}',          'Manage Price Sets',         'access CiviContribute,administer CiviCRM', 'AND',  @contributionlastID, '1', NULL, 11 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Events{/ts}',  'Events', 'access CiviEvent', '', NULL, '1', NULL, 40 );

SET @eventlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/event&reset=1',                                   '{ts escape="sql" skip="true"}Dashboard{/ts}',          'CiviEvent Dashboard',  'access CiviEvent', '',    @eventlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/participant/add&reset=1&action=add&context=standalone', '{ts escape="sql" skip="true"}Register Event Participant{/ts}', 'Register Event Participant', 'access CiviEvent,edit event participants', 'AND', @eventlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/event/search&reset=1',                            '{ts escape="sql" skip="true"}Find Participants{/ts}',  'Find Participants',    'access CiviEvent', '',    @eventlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/event/import&reset=1',                            '{ts escape="sql" skip="true"}Import Participants{/ts}','Import Participants',  'access CiviEvent,edit event participants', 'AND',    @eventlastID, '1', '1',  4 ), 
    ( @domainID, 'civicrm/event/add&reset=1&action=add',                    '{ts escape="sql" skip="true"}New Event{/ts}',          'New Event',            'access CiviEvent,edit all events', 'AND',    @eventlastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/event/manage&reset=1',                            '{ts escape="sql" skip="true"}Manage Events{/ts}',      'Manage Events',        'access CiviEvent,edit all events', 'AND',    @eventlastID, '1', 1, 6 ), 
    ( @domainID, 'civicrm/admin/pcp?reset=1&page_type=event',               '{ts escape="sql" skip="true"}Personal Campaign Pages{/ts}',    'Personal Campaign Pages',   'access CiviEvent,administer CiviCRM', 'AND', @eventlastID, '1', 1, 7 ),
    ( @domainID, 'civicrm/admin/eventTemplate&reset=1',                     '{ts escape="sql" skip="true"}Event Templates{/ts}',    'Event Templates',      'access CiviEvent,edit all events', 'AND',    @eventlastID, '1', 1, 8 ), 
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',                  '{ts escape="sql" skip="true"}New Price Set{/ts}',      'New Price Set',        'access CiviEvent,edit all events', 'AND',    @eventlastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/admin/price&reset=1',                             '{ts escape="sql" skip="true"}Manage Price Sets{/ts}',  'Manage Price Sets',    'access CiviEvent,edit all events', 'AND',    @eventlastID, '1', NULL, 10 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Mailings{/ts}', 'Mailings', 'access CiviMail,create mailings,approve mailings,schedule mailings', 'OR', NULL, '1', NULL, 50 );

SET @mailinglastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/mailing/send&reset=1',                            '{ts escape="sql" skip="true"}New Mailing{/ts}', 'New Mailing',                                          'access CiviMail,create mailings', 'OR', @mailinglastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/mailing/browse/unscheduled&reset=1&scheduled=false', '{ts escape="sql" skip="true"}Draft and Unscheduled Mailings{/ts}', 'Draft and Unscheduled Mailings', 'access CiviMail,create mailings,schedule mailings', 'OR', @mailinglastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/mailing/browse/scheduled&reset=1&scheduled=true', '{ts escape="sql" skip="true"}Scheduled and Sent Mailings{/ts}', 'Scheduled and Sent Mailings',          'access CiviMail,approve mailings,create mailings,schedule mailings', 'OR', @mailinglastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/mailing/browse/archived&reset=1',                 '{ts escape="sql" skip="true"}Archived Mailings{/ts}', 'Archived Mailings',                              'access CiviMail,create mailings', 'OR', @mailinglastID, '1', 1,    4 ), 
    ( @domainID, 'civicrm/admin/component&reset=1',                         '{ts escape="sql" skip="true"}Headers, Footers, and Automated Messages{/ts}', 'Headers, Footers, and Automated Messages', 'access CiviMail,administer CiviCRM', 'AND', @mailinglastID, '1', NULL, 5 ),
    ( @domainID, 'civicrm/admin/messageTemplates&reset=1',                  '{ts escape="sql" skip="true"}Message Templates{/ts}', 'Message Templates',                 'administer CiviCRM', '', @mailinglastID, '1', NULL, 6 ),
    ( @domainID, 'civicrm/admin/options/from_email&group=from_email_address&reset=1', '{ts escape="sql" skip="true"}From Email Addresses{/ts}', 'From Email Addresses', 'administer CiviCRM', '', @mailinglastID, '1', 1, 7 ),
    ( @domainID, 'civicrm/sms/send?reset=1', '{ts escape="sql" skip="true"}New SMS{/ts}', 'New SMS', 'administer CiviCRM', NULL, @mailinglastID, '1', 1, 8 );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Memberships{/ts}', 'Memberships', 'access CiviMember', '', NULL, '1', NULL, 60 );

SET @memberlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/member&reset=1',                              '{ts escape="sql" skip="true"}Dashboard{/ts}',           'Dashboard',       'access CiviMember', '', @memberlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/member/add&reset=1&action=add&context=standalone', '{ts escape="sql" skip="true"}New Membership{/ts}', 'New Membership',  'access CiviMember,edit memberships', 'AND', @memberlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/member/search&reset=1',                       '{ts escape="sql" skip="true"}Find Memberships{/ts}',    'Find Memberships','access CiviMember', '', @memberlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/member/import&reset=1',                       '{ts escape="sql" skip="true"}Import Memberships{/ts}',  'Import Members',  'access CiviMember,edit memberships', 'AND', @memberlastID, '1', 1, 4 ),
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',              '{ts escape="sql" skip="true"}New Price Set{/ts}',       'New Price Set',   'access CiviMember,administer CiviCRM', 'AND',  @memberlastID, '1', NULL, 5 ),
    ( @domainID, 'civicrm/admin/price&reset=1',                         '{ts escape="sql" skip="true"}Manage Price Sets{/ts}',   'Manage Price Sets', 'access CiviMember,administer CiviCRM', 'AND',  @memberlastID, '1', NULL, 6 );


INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Campaigns{/ts}', 'Campaigns', 'interview campaign contacts,release campaign contacts,reserve campaign contacts,manage campaign,administer CiviCampaign,gotv campaign contacts', 'OR', NULL, '1', NULL, 70 );

SET @campaignlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/campaign&reset=1',        '{ts escape="sql" skip="true"}Dashboard{/ts}', 'Dashboard', 'manage campaign,administer CiviCampaign', 'OR', @campaignlastID, '1', NULL, 1 );
SET @campaigndashboardlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/campaign&reset=1&subPage=survey',        '{ts escape="sql" skip="true"}Surveys{/ts}', 'Survey Dashboard', 'manage campaign,administer CiviCampaign', 'OR', @campaigndashboardlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/campaign&reset=1&subPage=petition',        '{ts escape="sql" skip="true"}Petitions{/ts}', 'Petition Dashboard', 'manage campaign,administer CiviCampaign', 'OR', @campaigndashboardlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/campaign&reset=1&subPage=campaign',        '{ts escape="sql" skip="true"}Campaigns{/ts}', 'Campaign Dashboard', 'manage campaign,administer CiviCampaign', 'OR', @campaigndashboardlastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/campaign/add&reset=1',        '{ts escape="sql" skip="true"}New Campaign{/ts}', 'New Campaign', 'manage campaign,administer CiviCampaign', 'OR', @campaignlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/survey/add&reset=1',        '{ts escape="sql" skip="true"}New Survey{/ts}', 'New Survey', 'manage campaign,administer CiviCampaign', 'OR', @campaignlastID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/petition/add&reset=1',        '{ts escape="sql" skip="true"}New Petition{/ts}', 'New Petition', 'manage campaign,administer CiviCampaign', 'OR', @campaignlastID, '1', NULL, 4 ),
    ( @domainID, 'civicrm/survey/search&reset=1&op=reserve', '{ts escape="sql" skip="true"}Reserve Respondents{/ts}', 'Reserve Respondents', 'administer CiviCampaign,manage campaign,reserve campaign contacts', 'OR', @campaignlastID, '1', NULL, 5 ),
    ( @domainID, 'civicrm/survey/search&reset=1&op=interview', '{ts escape="sql" skip="true"}Interview Respondents{/ts}', 'Interview Respondents', 'administer CiviCampaign,manage campaign,interview campaign contacts', 'OR', @campaignlastID, '1', NULL, 6 ),
    ( @domainID, 'civicrm/survey/search&reset=1&op=release', '{ts escape="sql" skip="true"}Release Respondents{/ts}', 'Release Respondents', 'administer CiviCampaign,manage campaign,release campaign contacts', 'OR', @campaignlastID, '1', NULL, 7 ),
    ( @domainID, 'civicrm/campaign/gotv&reset=1', '{ts escape="sql" skip="true"}GOTV (Voter Tracking){/ts}', 'Voter Listing', 'administer CiviCampaign,manage campaign,release campaign contacts,gotv campaign contacts', 'OR', @campaignlastID, '1', NULL, 8 ),
    ( @domainID, 'civicrm/campaign/vote&reset=1', '{ts escape="sql" skip="true"}Conduct Survey{/ts}', 'Conduct Survey', 'administer CiviCampaign,manage campaign,reserve campaign contacts,interview campaign contacts', 'OR', @campaignlastID, '1', NULL, 9 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Cases{/ts}', 'Cases', 'access my cases and activities,access all cases and activities', 'OR', NULL, '1', NULL, 80 );

SET @caselastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/case&reset=1',        '{ts escape="sql" skip="true"}Dashboard{/ts}', 'Dashboard', 'access my cases and activities,access all cases and activities', 'OR',       @caselastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/case/add&reset=1&action=add&atype=13&context=standalone', '{ts escape="sql" skip="true"}New Case{/ts}', 'New Case', 'add cases,access all cases and activities', 'OR', @caselastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/case/search&reset=1', '{ts escape="sql" skip="true"}Find Cases{/ts}', 'Find Cases', 'access my cases and activities,access all cases and activities', 'OR',     @caselastID, '1', 1, 3 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Grants{/ts}', 'Grants', 'access CiviGrant', '', NULL, '1', NULL, 90 );

SET @grantlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES        
    ( @domainID, 'civicrm/grant&reset=1',           '{ts escape="sql" skip="true"}Dashboard{/ts}', 'Dashboard', 'access CiviGrant', '',       @grantlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/grant/add&reset=1&action=add&context=standalone', '{ts escape="sql" skip="true"}New Grant{/ts}', 'New Grant', 'access CiviGrant,edit grants', 'AND', @grantlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/grant/search&reset=1',    '{ts escape="sql" skip="true"}Find Grants{/ts}', 'Find Grants', 'access CiviGrant', '',   @grantlastID, '1', 1, 3 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Administer{/ts}', 'Administer', 'administer CiviCRM', '', NULL, '1', NULL, 100 );

SET @adminlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin&reset=1', '{ts escape="sql" skip="true"}Administration Console{/ts}', 'Administration Console', 'administer CiviCRM', '', @adminlastID, '1', NULL, 1 );

SET @adminConsolelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/configtask&reset=1', '{ts escape="sql" skip="true"}Configuration Checklist{/ts}', 'Configuration Checklist', 'administer CiviCRM', '', @adminConsolelastID, '1', NULL, 1 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Customize Data and Screens{/ts}', 'Customize Data and Screens', 'administer CiviCRM', '', @adminlastID, '1', NULL, 3 );

SET @CustomizelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/custom/group&reset=1',      '{ts escape="sql" skip="true"}Custom Fields{/ts}', 'Custom Fields',                             'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/uf/group&reset=1',          '{ts escape="sql" skip="true"}Profiles{/ts}', 'Profiles',                                       'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/tag&reset=1',               '{ts escape="sql" skip="true"}Tags (Categories){/ts}', 'Tags (Categories)',                     'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/options/activity_type&reset=1&group=activity_type', '{ts escape="sql" skip="true"}Activity Types{/ts}', 'Activity Types',   'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 4 ), 
    ( @domainID, 'civicrm/admin/reltype&reset=1',           '{ts escape="sql" skip="true"}Relationship Types{/ts}', 'Relationship Types',                   'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/admin/options/subtype&reset=1',   '{ts escape="sql" skip="true"}Contact Types{/ts}','Contact Types',                              'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 6 ),
    ( @domainID, 'civicrm/admin/setting/preferences/display&reset=1',   '{ts escape="sql" skip="true"}Display Preferences{/ts}', 'Display Preferences',     'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/admin/setting/search&reset=1',    '{ts escape="sql" skip="true"}Search Preferences{/ts}',    'Search Preferences',                'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/menu&reset=1',              '{ts escape="sql" skip="true"}Navigation Menu{/ts}', 'Navigation Menu',                         'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/options/wordreplacements&reset=1','{ts escape="sql" skip="true"}Word Replacements{/ts}','Word Replacements',                'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 12 ),
    ( @domainID, 'civicrm/admin/options/custom_search&reset=1&group=custom_search', '{ts escape="sql" skip="true"}Manage Custom Searches{/ts}', 'Manage Custom Searches', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 13 ),
    ( @domainID, 'civicrm/admin/extensions&reset=1',        '{ts escape="sql" skip="true"}Manage Extensions{/ts}', 'Manage Extensions', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 14 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Dropdown Options{/ts}', 'Dropdown Options', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 8 );
    
SET @optionListlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES         
    ( @domainID, 'civicrm/admin/options/gender&reset=1&group=gender',                                                  '{ts escape="sql" skip="true"}Gender Options{/ts}',         'Gender Options',                           'administer CiviCRM', '',   @optionListlastID, '1', NULL,  1 ), 
    ( @domainID, 'civicrm/admin/options/individual_prefix&group=individual_prefix&reset=1',                            '{ts escape="sql" skip="true"}Individual Prefixes (Ms, Mr...){/ts}', 'Individual Prefixes (Ms, Mr...)', 'administer CiviCRM', '',   @optionListlastID, '1', NULL,  2 ), 
    ( @domainID, 'civicrm/admin/options/individual_suffix&group=individual_suffix&reset=1',                            '{ts escape="sql" skip="true"}Individual Suffixes (Jr, Sr...){/ts}', 'Individual Suffixes (Jr, Sr...)', 'administer CiviCRM', '',   @optionListlastID, '1', NULL,  3 ), 
    ( @domainID, 'civicrm/admin/options/instant_messenger_service&group=instant_messenger_service&reset=1',            '{ts escape="sql" skip="true"}Instant Messenger Services{/ts}',     'Instant Messenger Services',       'administer CiviCRM', '',   @optionListlastID, '1', NULL,  4 ), 
    ( @domainID, 'civicrm/admin/locationType&reset=1',                                                                 '{ts escape="sql" skip="true"}Location Types (Home, Work...){/ts}', 'Location Types (Home, Work...)',   'administer CiviCRM', '',   @optionListlastID, '1', NULL,  5 ), 
    ( @domainID, 'civicrm/admin/options/mobile_provider&group=mobile_provider&reset=1',                                '{ts escape="sql" skip="true"}Mobile Phone Providers{/ts}', 'Mobile Phone Providers',                   'administer CiviCRM', '',   @optionListlastID, '1', NULL,  6 ), 
    ( @domainID, 'civicrm/admin/options/phone_type&group=phone_type&reset=1',                                          '{ts escape="sql" skip="true"}Phone Types{/ts}',            'Phone Types',                              'administer CiviCRM', '',   @optionListlastID, '1', NULL,  7 ),  
    ( @domainID, 'civicrm/admin/options/website_type&group=website_type&reset=1',                                      '{ts escape="sql" skip="true"}Website Types{/ts}',          'Website Types',                            'administer CiviCRM', '',   @optionListlastID, '1', NULL,  8 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Communications{/ts}', 'Communications', 'administer CiviCRM', '', @adminlastID, '1', NULL, 4 );

SET @communicationslastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/domain&action=update&reset=1',         '{ts escape="sql" skip="true"}Organization Address and Contact Info{/ts}', 'Organization Address and Contact Info',                                      'administer CiviCRM', '', @communicationslastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/options/from_email_address&group=from_email_address&reset=1', '{ts escape="sql" skip="true"}FROM Email Addresses{/ts}', 'FROM Email Addresses',                                                 'administer CiviCRM', '', @communicationslastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/messageTemplates&reset=1',             '{ts escape="sql" skip="true"}Message Templates{/ts}',      'Message Templates',                                                                         'administer CiviCRM', '', @communicationslastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/scheduleReminders&reset=1',              '{ts escape="sql" skip="true"}Schedule Reminders{/ts}',    'Schedule Reminders',                                                                       'administer CiviCRM', '', @communicationslastID, '1', NULL, 4 ),
    ( @domainID, 'civicrm/admin/options/preferred_communication_method&group=preferred_communication_method&reset=1',  '{ts escape="sql" skip="true"}Preferred Communication Methods{/ts}', 'Preferred Communication Methods',  'administer CiviCRM', '', @communicationslastID, '1', NULL, 5 ),
    ( @domainID, 'civicrm/admin/labelFormats&reset=1',                                  '{ts escape="sql" skip="true"}Label Formats{/ts}',                 'Label Formats',                                                     'administer CiviCRM', '', @communicationslastID, '1', NULL, 6 ),
    ( @domainID, 'civicrm/admin/pdfFormats&reset=1',                                    '{ts escape="sql" skip="true"}Print Page (PDF) Formats{/ts}',      'Print Page (PDF) Formats',                                          'administer CiviCRM', '', @communicationslastID, '1', NULL, 7 ), 
    ( @domainID, 'civicrm/admin/options/email_greeting&group=email_greeting&reset=1',   '{ts escape="sql" skip="true"}Email Greeting Formats{/ts}',        'Email Greeting Formats',                                            'administer CiviCRM', '', @communicationslastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/options/postal_greeting&group=postal_greeting&reset=1', '{ts escape="sql" skip="true"}Postal Greeting Formats{/ts}',       'Postal Greeting Formats',                                           'administer CiviCRM', '', @communicationslastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/admin/options/addressee&group=addressee&reset=1',             '{ts escape="sql" skip="true"}Addressee Formats{/ts}',             'Addressee Formats',                                                 'administer CiviCRM', '', @communicationslastID, '1', NULL, 10 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Localization{/ts}', 'Localization', 'administer CiviCRM', '', @adminlastID, '1', NULL, 6 );

SET @locallastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/setting/localization&reset=1',          '{ts escape="sql" skip="true"}Languages, Currency, Locations{/ts}', 'Languages, Currency, Locations',   'administer CiviCRM', '', @locallastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/setting/preferences/address&reset=1',   '{ts escape="sql" skip="true"}Address Settings{/ts}',               'Address Settings',                 'administer CiviCRM', '', @locallastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/setting/date&reset=1',                  '{ts escape="sql" skip="true"}Date Formats{/ts}',                   'Date Formats',                     'administer CiviCRM', '', @locallastID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/admin/options/languages&group=languages&reset=1', '{ts escape="sql" skip="true"}Preferred Language Options{/ts}', 'Preferred Language Options',       'administer CiviCRM', '', @locallastID, '1', NULL, 4 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,                                                  '{ts escape="sql" skip="true"}Users and Permissions{/ts}',          'Users and Permissions',            'administer CiviCRM', '', @adminlastID, '1', NULL, 7 );

SET @usersPermslastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/access&reset=1',       '{ts escape="sql" skip="true"}Permissions (Access Control){/ts}',    'Permissions (Access Control)',     'administer CiviCRM', '', @usersPermslastID, '1', NULL, 1 ),
    ( @domainID, 'civicrm/admin/synchUser&reset=1',    '{ts escape="sql" skip="true"}Synchronize Users to Contacts{/ts}',   'Synchronize Users to Contacts',    'administer CiviCRM', '', @usersPermslastID, '1', NULL, 2 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, NULL,                                 '{ts escape="sql" skip="true"}System Settings{/ts}',                 'System Settings',                      'administer CiviCRM', '', @adminlastID, '1', NULL, 8 );

SET @systemSettingslastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/setting/component&reset=1', '{ts escape="sql" skip="true"}Enable CiviCRM Components{/ts}', 'Enable Components',                                 'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/setting/smtp&reset=1',                  '{ts escape="sql" skip="true"}Outbound Email (SMTP/Sendmail){/ts}', 'Outbound Email',                   'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/paymentProcessor&reset=1',              '{ts escape="sql" skip="true"}Payment Processors{/ts}', 'Payment Processors',                           'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/setting/mapping&reset=1',               '{ts escape="sql" skip="true"}Mapping and Geocoding{/ts}', 'Mapping and Geocoding',                     'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 4 ), 
    ( @domainID, 'civicrm/admin/setting/misc&reset=1',                  '{ts escape="sql" skip="true"}Undelete, Logging and ReCAPTCHA{/ts}', 'Undelete, Logging and ReCAPTCHA', 'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/admin/setting/path&reset=1',                  '{ts escape="sql" skip="true"}Directories{/ts}',        'Directories',                                  'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/setting/url&reset=1',                   '{ts escape="sql" skip="true"}Resource URLs{/ts}',      'Resource URLs',                                'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 7 ), 
    ( @domainID, 'civicrm/admin/setting/updateConfigBackend&reset=1',   '{ts escape="sql" skip="true"}Cleanup Caches and Update Paths{/ts}', 'Cleanup Caches and Update Paths', 'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 8 ),
    ( @domainID, 'civicrm/admin/setting/uf&reset=1',                    '{ts escape="sql" skip="true"}CMS Database Integration{/ts}',    'CMS Integration',                     'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/admin/options/safe_file_extension&group=safe_file_extension&reset=1', '{ts escape="sql" skip="true"}Safe File Extensions{/ts}', 'Safe File Extensions','administer CiviCRM', '',@systemSettingslastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/options?reset=1',                       '{ts escape="sql" skip="true"}Option Groups{/ts}', 'Option Groups',                                     'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/mapping&reset=1',                       '{ts escape="sql" skip="true"}Import/Export Mappings{/ts}', 'Import/Export Mappings',                   'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 12 ), 
    ( @domainID, 'civicrm/admin/setting/debug&reset=1',                 '{ts escape="sql" skip="true"}Debugging and Error Handling{/ts}','Debugging and Error Handling',        'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 13 ),
    ( @domainID, 'civicrm/admin/setting/preferences/multisite&reset=1', '{ts escape="sql" skip="true"}Multi Site Settings{/ts}', 'Multi Site Settings',                         'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 14 ),
    ( @domainID, 'civicrm/admin/job&reset=1',                           '{ts escape="sql" skip="true"}Scheduled Jobs{/ts}', 'Scheduled Jobs',                                   'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 15 ),
    ( @domainID, 'civicrm/admin/sms/provider?reset=1', '{ts escape="sql" skip="true"}SMS Providers{/ts}', 'SMS Providers',                                  'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 16 );


-- begin component admin menus
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,                                             '{ts escape="sql" skip="true"}CiviCampaign{/ts}',              'CiviCampaign',              'administer CiviCampaign,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 9 );

SET @adminCampaignlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/admin/campaign/surveyType&reset=1',                            '{ts escape="sql" skip="true"}Survey Types{/ts}',  'Survey Types', 'administer CiviCampaign',    '', @adminCampaignlastID, '1', NULL, 1 ),
    ( @domainID, 'civicrm/admin/options/campaign_type&group=campaign_type&reset=1',      '{ts escape="sql" skip="true"}Campaign Types{/ts}',  'Campaign Types', 'administer CiviCampaign',    '', @adminCampaignlastID, '1', NULL, 2 ),
    ( @domainID, 'civicrm/admin/options/campaign_status&group=campaign_status&reset=1',  '{ts escape="sql" skip="true"}Campaign Status{/ts}',  'Campaign Status', 'administer CiviCampaign',    '', @adminCampaignlastID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/admin/options/engagement_index&group=engagement_index&reset=1', '{ts escape="sql" skip="true"}Engagement Index{/ts}',  'Engagement Index', 'administer CiviCampaign', '', @adminCampaignlastID, '1', NULL, 4 ),
    ( @domainID, 'civicrm/admin/setting/preferences/campaign&reset=1',                   '{ts escape="sql" skip="true"}CiviCampaign Component Settings{/ts}', 'CiviCampaign Component Settings','administer CiviCampaign', '', @adminCampaignlastID, '1', NULL, 5 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,  '{ts escape="sql" skip="true"}CiviCase{/ts}', 'CiviCase', 'administer CiviCase', NULL, @adminlastID, '1', NULL, 10 );

SET @adminCaselastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/options/case_type&group=case_type&reset=1',            '{ts escape="sql" skip="true"}Case Types{/ts}',      'Case Types',      'administer CiviCase', NULL, @adminCaselastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/options/redaction_rule&group=redaction_rule&reset=1',  '{ts escape="sql" skip="true"}Redaction Rules{/ts}', 'Redaction Rules', 'administer CiviCase', NULL, @adminCaselastID, '1', NULL, 2 ),
    ( @domainID, 'civicrm/admin/options/case_status&group=case_status&reset=1',  '{ts escape="sql" skip="true"}Case Statuses{/ts}', 'Case Statuses', 'administer CiviCase', NULL, @adminCaselastID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/admin/options/encounter_medium&group=encounter_medium&reset=1',  '{ts escape="sql" skip="true"}Encounter Medium{/ts}', 'Encounter Medium', 'administer CiviCase', NULL, @adminCaselastID, '1', NULL, 4 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES 
    ( @domainID, NULL,  '{ts escape="sql" skip="true"}CiviContribute{/ts}', 'CiviContribute', 'access CiviContribute,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 11 );
    
SET @adminContributelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/contribute&reset=1&action=add',            '{ts escape="sql" skip="true"}New Contribution Page{/ts}',      'New Contribution Page',     'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/contribute&reset=1',                       '{ts escape="sql" skip="true"}Manage Contribution Pages{/ts}',  'Manage Contribution Pages', 'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', '1',  7 ), 
    ( @domainID, 'civicrm/admin/pcp?reset=1&page_type=contribute',                              '{ts escape="sql" skip="true"}Personal Campaign Pages{/ts}',    'Personal Campaign Pages',   'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/contribute/managePremiums&reset=1',        '{ts escape="sql" skip="true"}Premiums (Thank-you Gifts){/ts}', 'Premiums',                  'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', 1,    9 ), 
    ( @domainID, 'civicrm/admin/contribute/contributionType&reset=1',      '{ts escape="sql" skip="true"}Contribution Types{/ts}',         'Contribution Types',        'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 10), 
    ( @domainID, 'civicrm/admin/options/payment_instrument&group=payment_instrument&reset=1',  '{ts escape="sql" skip="true"}Payment Instruments{/ts}',    'Payment Instruments',   'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/options/accept_creditcard&group=accept_creditcard&reset=1',    '{ts escape="sql" skip="true"}Accepted Credit Cards{/ts}',  'Accepted Credit Cards', 'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', 1, 12 ),
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',                  '{ts escape="sql" skip="true"}New Price Set{/ts}',              'New Price Set',             'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 13 ),
    ( @domainID, 'civicrm/admin/price&reset=1',                             '{ts escape="sql" skip="true"}Manage Price Sets{/ts}',          'Manage Price Sets',         'access CiviContribute,administer CiviCRM', 'AND', @adminContributelastID, '1', NULL, 14 ),
    ( @domainID, 'civicrm/admin/paymentProcessor&reset=1',                  '{ts escape="sql" skip="true"}Payment Processors{/ts}',         'Payment Processors',        'administer CiviCRM', '',                          @adminContributelastID, '1', NULL, 15  );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES         
    ( @domainID, NULL, '{ts escape="sql" skip="true"}CiviEvent{/ts}', 'CiviEvent', 'access CiviEvent,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 12 );

SET @adminEventlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/event/add&reset=1&action=add',                   '{ts escape="sql" skip="true"}New Event{/ts}',          'New Event',                        'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/event/manage&reset=1',                           '{ts escape="sql" skip="true"}Manage Events{/ts}',      'Manage Events',                    'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', 1,    2 ), 
    ( @domainID, 'civicrm/admin/pcp?reset=1&page_type=event',                              '{ts escape="sql" skip="true"}Personal Campaign Pages{/ts}',    'Personal Campaign Pages',   'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', 1, 3 ),
    ( @domainID, 'civicrm/admin/eventTemplate&reset=1',                    '{ts escape="sql" skip="true"}Event Templates{/ts}',    'Event Templates',                  'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', 1,    4 ), 
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',                 '{ts escape="sql" skip="true"}New Price Set{/ts}',      'New Price Set',                    'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/admin/price&reset=1',                            '{ts escape="sql" skip="true"}Manage Price Sets{/ts}',  'Manage Price Sets',                'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', 1,    6 ),
    ( @domainID, 'civicrm/admin/options/event_type&group=event_type&reset=1',  '{ts escape="sql" skip="true"}Event Types{/ts}',    'Event Types',                      'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 7 ), 
    ( @domainID, 'civicrm/admin/participant_status&reset=1',                   '{ts escape="sql" skip="true"}Participant Statuses{/ts}', 'Participant Statuses',       'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/options/participant_role&group=participant_role&reset=1', '{ts escape="sql" skip="true"}Participant Roles{/ts}', 'Participant Roles',  'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 9 ),
    ( @domainID, 'civicrm/admin/options/participant_listing&group=participant_listing&reset=1', '{ts escape="sql" skip="true"}Participant Listing Options{/ts}', 'Participant Listing Options', 'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/options/event_badge&group=event_badge&reset=1', '{ts escape="sql" skip="true"}Event Badge Formats{/ts}', 'Event Badge Formats', 'access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/paymentProcessor&reset=1',                  '{ts escape="sql" skip="true"}Payment Processors{/ts}', 'Payment Processors',              'administer CiviCRM', '',                     @adminEventlastID, '1', NULL, 12),
    ( @domainID, 'civicrm/admin/setting/preferences/event&reset=1',         '{ts escape="sql" skip="true"}CiviEvent Component Settings{/ts}', 'CiviEvent Component Settings','access CiviEvent,administer CiviCRM', 'AND', @adminEventlastID, '1', NULL, 13 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}CiviGrant{/ts}', 'CiviGrant', 'access CiviGrant,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 13 );

SET @adminGrantlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/admin/options/grant_type&group=grant_type&reset=1', '{ts escape="sql" skip="true"}Grant Types{/ts}', 'Grant Types', 'access CiviGrant,administer CiviCRM', 'AND', @adminGrantlastID, '1', NULL, 1 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}CiviMail{/ts}', 'CiviMail', 'access CiviMail,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 14 );

SET @adminMailinglastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/component&reset=1',            '{ts escape="sql" skip="true"}Headers, Footers, and Automated Messages{/ts}', 'Headers, Footers, and Automated Messages', 'access CiviMail,administer CiviCRM', 'AND', @adminMailinglastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/messageTemplates&reset=1',     '{ts escape="sql" skip="true"}Message Templates{/ts}', 'Message Templates', 'administer CiviCRM', '',   @adminMailinglastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/options/from_email&group=from_email_address&reset=1', '{ts escape="sql" skip="true"}From Email Addresses{/ts}', 'From Email Addresses', 'administer CiviCRM', '', @adminMailinglastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/mailSettings&reset=1',         '{ts escape="sql" skip="true"}Mail Accounts{/ts}', 'Mail Accounts', 'access CiviMail,administer CiviCRM', 'AND',           @adminMailinglastID, '1', NULL, 4 ),
    ( @domainID, 'civicrm/admin/mail&reset=1',                 '{ts escape="sql" skip="true"}Mailer Settings{/ts}', 'Mailer Settings', 'access CiviMail,administer CiviCRM', 'AND',           @adminMailinglastID, '1', NULL, 5 ),
    ( @domainID, 'civicrm/admin/setting/preferences/mailing&reset=1', '{ts escape="sql" skip="true"}CiviMail Component Settings{/ts}', 'CiviMail Component Settings','access CiviMail,administer CiviCRM', 'AND', @adminMailinglastID, '1', NULL, 6 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}CiviMember{/ts}', 'CiviMember', 'access CiviMember,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 15 );

SET @adminMemberlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/admin/member/membershipType&reset=1',    '{ts escape="sql" skip="true"}Membership Types{/ts}',        'Membership Types',        'access CiviMember,administer CiviCRM', 'AND', @adminMemberlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/member/membershipStatus&reset=1',  '{ts escape="sql" skip="true"}Membership Status Rules{/ts}', 'Membership Status Rules', 'access CiviMember,administer CiviCRM', 'AND', @adminMemberlastID, '1', 1, 2 ),
    ( @domainID, 'civicrm/admin/price&reset=1&action=add',         '{ts escape="sql" skip="true"}New Price Set{/ts}',           'New Price Set',           'access CiviMember,administer CiviCRM', 'AND', @adminMemberlastID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/admin/price&reset=1',                    '{ts escape="sql" skip="true"}Manage Price Sets{/ts}',       'Manage Price Sets',       'access CiviMember,administer CiviCRM', 'AND', @adminMemberlastID, '1', NULL, 4 ),
    ( @domainID, 'civicrm/admin/setting/preferences/member&reset=1', '{ts escape="sql" skip="true"}CiviMember Component Settings{/ts}', 'CiviMember Component Settings','access CiviMember,administer CiviCRM', 'AND', @adminMemberlastID, '1', NULL, 5 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,                                             '{ts escape="sql" skip="true"}CiviReport{/ts}',              'CiviReport',              'access CiviReport,administer CiviCRM', 'AND', @adminlastID, '1', NULL, 16 );

SET @adminReportlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/report/list&reset=1',                            '{ts escape="sql" skip="true"}Reports Listing{/ts}',  'Reports Listing', 'access CiviReport',    '', @adminReportlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/report/template/list&reset=1',             '{ts escape="sql" skip="true"}Create Reports from Templates{/ts}', 'Create Reports from Templates', 'administer Reports', '', @adminReportlastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/report/options/report_template&reset=1',   '{ts escape="sql" skip="true"}Manage Templates{/ts}', 'Manage Templates', 'administer Reports',  '', @adminReportlastID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/admin/report/register&reset=1',                  '{ts escape="sql" skip="true"}Register Report{/ts}',  'Register Report',  'administer Reports',  '', @adminReportlastID, '1', NULL, 4 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Help{/ts}', 'Help', NULL, '',  NULL, '1', NULL, 110);

SET @adminHelplastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'http://documentation.civicrm.org',   '{ts escape="sql" skip="true"}Documentation{/ts}',      'Documentation',    NULL, 'AND', @adminHelplastID, '1', NULL, 1 ), 
    ( @domainID, 'http://forum.civicrm.org',           '{ts escape="sql" skip="true"}Community Forums{/ts}',   'Community Forums', NULL, 'AND', @adminHelplastID, '1', NULL, 2 ), 
    ( @domainID, 'http://civicrm.org/participate',     '{ts escape="sql" skip="true"}Participate{/ts}',        'Participate',      NULL, 'AND', @adminHelplastID, '1', NULL, 3 ), 
    ( @domainID, 'http://civicrm.org/aboutcivicrm',    '{ts escape="sql" skip="true"}About{/ts}',              'About',            NULL, 'AND', @adminHelplastID, '1', NULL, 4 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Reports{/ts}', 'Reports', 'access CiviReport', '',  NULL, '1', NULL, 95 );

SET @reportlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/report/list&reset=1', '{ts escape="sql" skip="true"}Reports Listing{/ts}', 'Reports Listing', 'access CiviReport', '', @reportlastID, '1', 1,    1 );
    
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/report/template/list&reset=1',  '{ts escape="sql" skip="true"}Create Reports from Templates{/ts}',  'Create Reports from Templates', 'administer Reports',  '', @reportlastID, '1', 1, 2 );
    
-- sample reports with navigation menus

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    (  @domainID, 'Constituent Report (Summary)', 'contact/summary', 'Provides a list of address and telephone information for constituent records in your system.', 'administer CiviCRM', '{literal}a:31:{s:6:"fields";a:4:{s:9:"sort_name";s:1:"1";s:14:"street_address";s:1:"1";s:4:"city";s:1:"1";s:10:"country_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:92:"Provides a list of address and telephone information for constituent records in your system.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}Constituent Report (Summary){/ts}',       '{literal}Constituent Report (Summary){/literal}',     'administer CiviCRM',       '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Constituent Report (Detail)', 'contact/detail', 'Provides contact-related information on contributions, memberships, events and activities.', 'administer CiviCRM', '{literal}a:25:{s:6:"fields";a:30:{s:9:"sort_name";s:1:"1";s:10:"country_id";s:1:"1";s:15:"contribution_id";s:1:"1";s:12:"total_amount";s:1:"1";s:20:"contribution_type_id";s:1:"1";s:12:"receive_date";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:13:"membership_id";s:1:"1";s:18:"membership_type_id";s:1:"1";s:21:"membership_start_date";s:1:"1";s:19:"membership_end_date";s:1:"1";s:20:"membership_status_id";s:1:"1";s:14:"participant_id";s:1:"1";s:8:"event_id";s:1:"1";s:21:"participant_status_id";s:1:"1";s:7:"role_id";s:1:"1";s:25:"participant_register_date";s:1:"1";s:9:"fee_level";s:1:"1";s:10:"fee_amount";s:1:"1";s:15:"relationship_id";s:1:"1";s:20:"relationship_type_id";s:1:"1";s:12:"contact_id_b";s:1:"1";s:2:"id";s:1:"1";s:16:"activity_type_id";s:1:"1";s:7:"subject";s:1:"1";s:17:"source_contact_id";s:1:"1";s:18:"activity_date_time";s:1:"1";s:18:"activity_status_id";s:1:"1";s:17:"target_contact_id";s:1:"1";s:19:"assignee_contact_id";s:1:"1";}s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:90:"Provides contact-related information on contributions, memberships, events and activities.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}Constituent Report (Detail){/ts}',        '{literal}Constituent Report (Detail){/literal}',      'administer CiviCRM',       '',  @reportlastID,  '1', NULL, @instanceID+2  );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Activity Report ', 'activity', 'Provides a list of constituent activity including activity statistics for one/all contacts during a given date range(required)', 'administer CiviCRM', '{literal}a:26:{s:6:"fields";a:6:{s:16:"contact_assignee";s:1:"1";s:14:"contact_target";s:1:"1";s:16:"activity_type_id";s:1:"1";s:16:"activity_subject";s:1:"1";s:18:"activity_date_time";s:1:"1";s:9:"status_id";s:1:"1";}s:17:"contact_source_op";s:3:"has";s:20:"contact_source_value";s:0:"";s:19:"contact_assignee_op";s:3:"has";s:22:"contact_assignee_value";s:0:"";s:17:"contact_target_op";s:3:"has";s:20:"contact_target_value";s:0:"";s:15:"current_user_op";s:2:"eq";s:18:"current_user_value";s:1:"0";s:27:"activity_date_time_relative";s:10:"this.month";s:23:"activity_date_time_from";s:0:"";s:21:"activity_date_time_to";s:0:"";s:19:"activity_subject_op";s:3:"has";s:22:"activity_subject_value";s:0:"";s:19:"activity_type_id_op";s:2:"in";s:22:"activity_type_id_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:11:"description";s:126:"Provides a list of constituent activity including activity statistics for one/all contacts during a given date range(required)";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";s:9:"group_bys";N;s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Activity Report{/ts}',                    'activity',                                            'administer CiviCRM',       '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    (`domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Current Employer Report', 'contact/currentEmployer', 'Provides detail list of employer employee relationships along with employment details.', 'administer CiviCRM', '{literal}a:33:{s:6:"fields";a:5:{s:17:"organization_name";s:1:"1";s:9:"sort_name";s:1:"1";s:9:"job_title";s:1:"1";s:10:"start_date";s:1:"1";s:5:"email";s:1:"1";}s:20:"organization_name_op";s:3:"has";s:23:"organization_name_value";s:0:"";s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:19:"start_date_relative";s:1:"0";s:15:"start_date_from";s:0:"";s:13:"start_date_to";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:98:"Provides detail list of employer employee relationships along with employment details Ex Join Date";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Current Employer Report{/ts}',                    'Current Employer Report',  'administer CiviCRM',       '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Relationship Report', 'contact/relationship', 'Gives relationship details between two contacts', 'administer CiviCRM', '{literal}a:28:{s:6:"fields";a:4:{s:11:"sort_name_a";s:1:"1";s:11:"sort_name_b";s:1:"1";s:9:"label_a_b";s:1:"1";s:9:"label_b_a";s:1:"1";}s:14:"sort_name_a_op";s:3:"has";s:17:"sort_name_a_value";s:0:"";s:14:"sort_name_b_op";s:3:"has";s:17:"sort_name_b_value";s:0:"";s:17:"contact_type_a_op";s:2:"in";s:20:"contact_type_a_value";a:0:{}s:17:"contact_type_b_op";s:2:"in";s:20:"contact_type_b_value";a:0:{}s:12:"is_active_op";s:2:"eq";s:15:"is_active_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:19:"Relationship Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:18:"administer CiviCRM";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Relationship Report{/ts}',                'Relationship Report',                                 'administer CiviCRM',       '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Contribution Summary Report', 'contribute/summary', 'Groups and totals contributions by criteria including contact, time period, contribution type, contributor location, etc.', 'access CiviContribute', '{literal}a:42:{s:6:"fields";a:1:{s:12:"total_amount";s:1:"1";}s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:13:"total_sum_min";s:0:"";s:13:"total_sum_max";s:0:"";s:12:"total_sum_op";s:3:"lte";s:15:"total_sum_value";s:0:"";s:15:"total_count_min";s:0:"";s:15:"total_count_max";s:0:"";s:14:"total_count_op";s:3:"lte";s:17:"total_count_value";s:0:"";s:13:"total_avg_min";s:0:"";s:13:"total_avg_max";s:0:"";s:12:"total_avg_op";s:3:"lte";s:15:"total_avg_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:9:"group_bys";a:1:{s:12:"receive_date";s:1:"1";}s:14:"group_bys_freq";a:1:{s:12:"receive_date";s:5:"MONTH";}s:11:"description";s:80:"Shows contribution statistics by month / week / year .. country / state .. type.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}Contribution Summary Report{/ts}',             '{literal}Contribution Summary Report{/literal}',           'access CiviContribute',    '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Contribution Detail Report', 'contribute/detail', 'Lists specific contributions by criteria including contact, time period, contribution type, contributor location, etc. Contribution summary report points to this report for contribution details.', 'access CiviContribute', '{literal}a:34:{s:6:"fields";a:7:{s:9:"sort_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";s:10:"country_id";s:1:"1";s:20:"contribution_type_id";s:1:"1";s:12:"receive_date";s:1:"1";s:12:"total_amount";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:13:"ordinality_op";s:2:"in";s:16:"ordinality_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:126:"Lists detailed contribution(s) for one / all contacts. Contribution summary report points to this report for specific details.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}Contribution Detail Report{/ts}',              '{literal}Contribution Detail Report{/literal}',            'access CiviContribute',    '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'Repeat Contribution Report', 'contribute/repeat', 'Given two date ranges, shows contacts who contributed in both the date ranges with the amount contributed in each and the percentage increase / decrease.', 'access CiviContribute', '{literal}a:29:{s:6:"fields";a:3:{s:9:"sort_name";s:1:"1";s:13:"total_amount1";s:1:"1";s:13:"total_amount2";s:1:"1";}s:22:"receive_date1_relative";s:13:"previous.year";s:18:"receive_date1_from";s:0:"";s:16:"receive_date1_to";s:0:"";s:22:"receive_date2_relative";s:9:"this.year";s:18:"receive_date2_from";s:0:"";s:16:"receive_date2_to";s:0:"";s:17:"total_amount1_min";s:0:"";s:17:"total_amount1_max";s:0:"";s:16:"total_amount1_op";s:3:"lte";s:19:"total_amount1_value";s:0:"";s:17:"total_amount2_min";s:0:"";s:17:"total_amount2_max";s:0:"";s:16:"total_amount2_op";s:3:"lte";s:19:"total_amount2_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:9:"group_bys";a:1:{s:2:"id";s:1:"1";}s:11:"description";s:140:"Given two date ranges, shows contacts (and their contributions) who contributed in both the date ranges with percentage increase / decrease.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}Repeat Contribution Report{/ts}',   '{literal}Repeat Contribution Report{/literal}', 'access CiviContribute',    '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    ( @domainID, 'SYBUNT Report', 'contribute/sybunt', 'Some year(s) but not this year. Provides a list of constituents who donated at some time in the history of your organization but did not donate during the time period you specify.', 'access CiviContribute', '{literal}a:18:{s:6:"fields";a:3:{s:9:"sort_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"yid_op";s:2:"eq";s:9:"yid_value";s:4:"2011";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:179:"Some year(s) but not this year. Provides a list of constituents who donated at some time in the history of your organization but did not donate during the time period you specify.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}SYBUNT Report{/ts}',                      'SYBUNT Report',                                       'access CiviContribute',    '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'LYBUNT Report', 'contribute/lybunt', 'Last year but not this year. Provides a list of constituents who donated last year but did not donate during the time period you specify as the current year.', 'access CiviContribute', '{literal}a:19:{s:6:"fields";a:3:{s:9:"sort_name";s:1:"1";s:5:"email";s:1:"1";s:5:"phone";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"yid_op";s:2:"eq";s:9:"yid_value";s:4:"2011";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:157:"Last year but not this year. Provides a list of constituents who donated last year but did not donate during the time period you specify as the current year.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}LYBUNT Report{/ts}',                      'LYBUNT Report',                                       'access CiviContribute',    '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Contributions by Organization Report', 'contribute/organizationSummary', 'Displays a detailed list of contributions grouped by organization, which includes contributions made by employees for the organisation.', 'access CiviContribute', '{literal}a:20:{s:6:"fields";a:5:{s:17:"organization_name";s:1:"1";s:9:"sort_name";s:1:"1";s:12:"total_amount";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:12:"receive_date";s:1:"1";}s:20:"organization_name_op";s:3:"has";s:23:"organization_name_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:5:"4_b_a";s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:11:"description";s:193:"Displays a detailed contribution report for Organization relationships with contributors, as to if contribution done was  from an employee of some organization or from that Organization itself.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Contributions by Organization Report{/ts}', '{literal}Contributions by Organization Report{/literal}', 'access CiviContribute', '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Contributions by Household Report', 'contribute/householdSummary', 'Displays a detailed list of contributions grouped by household which includes contributions made by members of the household.', 'access CiviContribute', '{literal}a:21:{s:6:"fields";a:5:{s:14:"household_name";s:1:"1";s:9:"sort_name";s:1:"1";s:12:"total_amount";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:12:"receive_date";s:1:"1";}s:17:"household_name_op";s:3:"has";s:20:"household_name_value";s:0:"";s:23:"relationship_type_id_op";s:2:"eq";s:26:"relationship_type_id_value";s:5:"6_b_a";s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:11:"description";s:213:"Provides a detailed report for Contributions made by contributors(Or Household itself) who are having a relationship with household (For ex a Contributor is Head of Household for some household or is a member of.)";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Contributions by Household Report{/ts}',    '{literal}Contributions by Household Report{/literal}',    'access CiviContribute', '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Top Donors Report', 'contribute/topDonor', 'Provides a list of the top donors during a time period you define. You can include as many donors as you want (for example, top 100 of your donors).', 'access CiviContribute', '{literal}a:20:{s:6:"fields";a:2:{s:12:"display_name";s:1:"1";s:12:"total_amount";s:1:"1";}s:21:"receive_date_relative";s:9:"this.year";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:15:"total_range_min";s:0:"";s:15:"total_range_max";s:0:"";s:14:"total_range_op";s:2:"eq";s:17:"total_range_value";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:11:"description";s:148:"Provides a list of the top donors during a time period you define. You can include as many donors as you want (for example, top 100 of your donors).";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Top Donors Report{/ts}',                  'Top Donors Report',            'access CiviContribute',   '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Soft Credit Report', 'contribute/softcredit', 'Shows contributions made by contacts that have been soft-credited to other contacts.', 'access CiviContribute', '{literal}a:23:{s:6:"fields";a:5:{s:21:"display_name_creditor";s:1:"1";s:24:"display_name_constituent";s:1:"1";s:14:"email_creditor";s:1:"1";s:14:"phone_creditor";s:1:"1";s:12:"total_amount";s:1:"1";}s:5:"id_op";s:2:"in";s:8:"id_value";a:0:{}s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:20:"Soft Credit details.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}Soft Credit Report{/ts}',                 'Soft Credit Report',                                  'access CiviContribute',    '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Personal Campaign Page Summary', 'contribute/pcp', 'Summarizes amount raised and number of contributors for each Personal Campaign Page.', 'access CiviContribute', '{literal}a:22:{s:6:"fields";a:8:{s:9:"sort_name";s:1:"1";s:10:"page_title";s:1:"1";s:5:"title";s:1:"1";s:11:"goal_amount";s:1:"1";s:8:"amount_1";s:1:"1";s:8:"amount_2";s:1:"1";s:7:"soft_id";s:1:"1";s:12:"receive_date";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"page_title_op";s:3:"has";s:16:"page_title_value";s:0:"";s:8:"title_op";s:3:"has";s:11:"title_value";s:0:"";s:12:"amount_2_min";s:0:"";s:12:"amount_2_max";s:0:"";s:11:"amount_2_op";s:3:"lte";s:14:"amount_2_value";s:0:"";s:11:"description";s:35:"Shows Personal Campaign Page Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}Personal Campaign Page Summary{/ts}',                 'Personal Campaign Page Summary',                                  'access CiviContribute',    '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Pledge Summary Report', 'pledge/detail', 'Summary of pledges including amount pledged, pledge status, next payment date, balance due, total amount paid etc.', 'access CiviPledge', '{literal}a:27:{s:6:"fields";a:4:{s:9:"sort_name";s:1:"1";s:10:"country_id";s:1:"1";s:6:"amount";s:1:"1";s:9:"status_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:27:"pledge_create_date_relative";s:1:"0";s:23:"pledge_create_date_from";s:0:"";s:21:"pledge_create_date_to";s:0:"";s:17:"pledge_amount_min";s:0:"";s:17:"pledge_amount_max";s:0:"";s:16:"pledge_amount_op";s:3:"lte";s:19:"pledge_amount_value";s:0:"";s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:13:"Pledge Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviPledge";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Pledge Summary Report{/ts}',              'Pledge Summary Report',        'access CiviPledge',       '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Pledged But not Paid Report', 'pledge/pbnp', 'Pledged but not Paid Report', 'access CiviPledge', '{literal}a:17:{s:6:"fields";a:5:{s:9:"sort_name";s:1:"1";s:18:"pledge_create_date";s:1:"1";s:6:"amount";s:1:"1";s:14:"scheduled_date";s:1:"1";s:10:"country_id";s:1:"1";}s:27:"pledge_create_date_relative";s:1:"0";s:23:"pledge_create_date_from";s:0:"";s:21:"pledge_create_date_to";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:27:"Pledged but not Paid Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviPledge";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Pledged But not Paid Report{/ts}',        'Pledged But not Paid Report',  'access CiviPledge',       '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Bookkeeping Transactions Report', 'contribute/bookkeeping', 'Shows Bookkeeping Transactions Report', 'access CiviContribute', '{literal}a:25:{s:6:"fields";a:10:{s:9:"sort_name";s:1:"1";s:12:"receive_date";s:1:"1";s:12:"total_amount";s:1:"1";s:20:"contribution_type_id";s:1:"1";s:7:"trxn_id";s:1:"1";s:10:"invoice_id";s:1:"1";s:12:"check_number";s:1:"1";s:21:"payment_instrument_id";s:1:"1";s:22:"contribution_status_id";s:1:"1";s:2:"id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:21:"receive_date_relative";s:1:"0";s:17:"receive_date_from";s:0:"";s:15:"receive_date_to";s:0:"";s:23:"contribution_type_id_op";s:2:"in";s:26:"contribution_type_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:1:{i:0;s:1:"1";}s:16:"total_amount_min";s:0:"";s:16:"total_amount_max";s:0:"";s:15:"total_amount_op";s:3:"lte";s:18:"total_amount_value";s:0:"";s:11:"description";s:37:"Shows Bookkeeping Transactions Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:21:"access CiviContribute";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql" skip="true"}Bookkeeping Transactions Report{/ts}', '{literal}Bookkeeping Transactions Report{/literal}', 'access CiviContribute', '', @reportlastID, '1', NULL, @instanceID+2 );

UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Membership Report (Summary)', 'member/summary', 'Provides a summary of memberships by type and join date.', 'access CiviMember', '{literal}a:18:{s:6:"fields";a:2:{s:18:"membership_type_id";s:1:"1";s:12:"total_amount";s:1:"1";}s:18:"join_date_relative";s:1:"0";s:14:"join_date_from";s:0:"";s:12:"join_date_to";s:0:"";s:21:"membership_type_id_op";s:2:"in";s:24:"membership_type_id_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:25:"contribution_status_id_op";s:2:"in";s:28:"contribution_status_id_value";a:0:{}s:9:"group_bys";a:2:{s:9:"join_date";s:1:"1";s:18:"membership_type_id";s:1:"1";}s:14:"group_bys_freq";a:1:{s:9:"join_date";s:5:"MONTH";}s:11:"description";s:56:"Provides a summary of memberships by type and join date.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),     '{ts escape="sql" skip="true"}Membership Report (Summary){/ts}',        '{literal}Membership Report (Summary){/literal}',      'access CiviMember',        '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Membership Report (Detail)', 'member/detail', 'Provides a list of members along with their membership status and membership details (Join Date, Start Date, End Date).', 'access CiviMember', '{literal}a:28:{s:6:"fields";a:5:{s:9:"sort_name";s:1:"1";s:18:"membership_type_id";s:1:"1";s:21:"membership_start_date";s:1:"1";s:19:"membership_end_date";s:1:"1";s:4:"name";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:18:"join_date_relative";s:1:"0";s:14:"join_date_from";s:0:"";s:12:"join_date_to";s:0:"";s:23:"owner_membership_id_min";s:0:"";s:23:"owner_membership_id_max";s:0:"";s:22:"owner_membership_id_op";s:3:"lte";s:25:"owner_membership_id_value";s:0:"";s:6:"tid_op";s:2:"in";s:9:"tid_value";a:0:{}s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:119:"Provides a list of members along with their membership status and membership details (Join Date, Start Date, End Date).";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Membership Report (Detail){/ts}',         '{literal}Membership Report (Detail){/literal}',       'access CiviMember',        '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Membership Report (Lapsed)', 'member/lapse', 'Provides a list of memberships that lapsed or will lapse before the date you specify.', 'access CiviMember', '{literal}a:16:{s:6:"fields";a:5:{s:9:"sort_name";s:1:"1";s:18:"membership_type_id";s:1:"1";s:19:"membership_end_date";s:1:"1";s:4:"name";s:1:"1";s:10:"country_id";s:1:"1";}s:6:"tid_op";s:2:"in";s:9:"tid_value";a:0:{}s:28:"membership_end_date_relative";s:1:"0";s:24:"membership_end_date_from";s:0:"";s:22:"membership_end_date_to";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"description";s:85:"Provides a list of memberships that lapsed or will lapse before the date you specify.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:17:"access CiviMember";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Membership Report (Lapsed){/ts}',         '{literal}Membership Report (Lapsed){/literal}',       'access CiviMember',        '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Event Participant Report (List)', 'event/participantListing', 'Provides lists of participants for an event.', 'access CiviEvent', '{literal}a:27:{s:6:"fields";a:4:{s:9:"sort_name";s:1:"1";s:8:"event_id";s:1:"1";s:9:"status_id";s:1:"1";s:7:"role_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:8:"email_op";s:3:"has";s:11:"email_value";s:0:"";s:11:"event_id_op";s:2:"in";s:14:"event_id_value";a:0:{}s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"rid_op";s:2:"in";s:9:"rid_value";a:0:{}s:34:"participant_register_date_relative";s:1:"0";s:30:"participant_register_date_from";s:0:"";s:28:"participant_register_date_to";s:0:"";s:6:"eid_op";s:2:"in";s:9:"eid_value";a:0:{}s:11:"custom_4_op";s:2:"in";s:14:"custom_4_value";a:0:{}s:16:"blank_column_end";s:0:"";s:11:"description";s:44:"Provides lists of participants for an event.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";s:6:"groups";s:0:"";s:7:"options";N;s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Event Participant Report (List){/ts}',    '{literal}Event Participant Report (List){/literal}',  'access CiviEvent',         '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Event Income Report (Summary)', 'event/summary', 'Provides an overview of event income. You can include key information such as event ID, registration, attendance, and income generated to help you determine the success of an event.', 'access CiviEvent', '{literal}a:18:{s:6:"fields";a:2:{s:5:"title";s:1:"1";s:13:"event_type_id";s:1:"1";}s:5:"id_op";s:2:"in";s:8:"id_value";a:0:{}s:16:"event_type_id_op";s:2:"in";s:19:"event_type_id_value";a:0:{}s:25:"event_start_date_relative";s:1:"0";s:21:"event_start_date_from";s:0:"";s:19:"event_start_date_to";s:0:"";s:23:"event_end_date_relative";s:1:"0";s:19:"event_end_date_from";s:0:"";s:17:"event_end_date_to";s:0:"";s:11:"description";s:181:"Provides an overview of event income. You can include key information such as event ID, registration, attendance, and income generated to help you determine the success of an event.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Event Income Report (Summary){/ts}',      '{literal}Event Income Report (Summary){/literal}',    'access CiviEvent',         '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Event Income Report (Detail)', 'event/income', 'Helps you to analyze the income generated by an event. The report can include details by participant type, status and payment method.', 'access CiviEvent', '{literal}a:7:{s:5:"id_op";s:2:"in";s:8:"id_value";N;s:11:"description";s:133:"Helps you to analyze the income generated by an event. The report can include details by participant type, status and payment method.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Event Income Report (Detail){/ts}',       '{literal}Event Income Report (Detail){/literal}',     'access CiviEvent',         '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES    
    ( @domainID, 'Attendee List', 'event/participantListing', 'Provides lists of event attendees.', 'access CiviEvent', '{literal}a:27:{s:6:"fields";a:4:{s:9:"sort_name";s:1:"1";s:8:"event_id";s:1:"1";s:9:"status_id";s:1:"1";s:7:"role_id";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:8:"email_op";s:3:"has";s:11:"email_value";s:0:"";s:11:"event_id_op";s:2:"in";s:14:"event_id_value";a:0:{}s:6:"sid_op";s:2:"in";s:9:"sid_value";a:0:{}s:6:"rid_op";s:2:"in";s:9:"rid_value";a:0:{}s:34:"participant_register_date_relative";s:1:"0";s:30:"participant_register_date_from";s:0:"";s:28:"participant_register_date_to";s:0:"";s:6:"eid_op";s:2:"in";s:9:"eid_value";a:0:{}s:11:"custom_4_op";s:2:"in";s:14:"custom_4_value";a:0:{}s:16:"blank_column_end";s:0:"";s:11:"description";s:44:"Provides lists of participants for an event.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviEvent";s:6:"groups";s:0:"";s:7:"options";N;s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'),    '{ts escape="sql" skip="true"}Attendee List{/ts}',                      'Attendee List',                                       'access CiviEvent',         '',  @reportlastID,  '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Grant Report (Detail)', 'grant/detail', 'Grant Report Detail', 'access CiviGrant', '{literal}a:40:{s:6:"fields";a:2:{s:9:"sort_name";s:1:"1";s:25:"application_received_date";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:12:"gender_id_op";s:2:"in";s:15:"gender_id_value";a:0:{}s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:13:"grant_type_op";s:2:"in";s:16:"grant_type_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:18:"amount_granted_min";s:0:"";s:18:"amount_granted_max";s:0:"";s:17:"amount_granted_op";s:3:"lte";s:20:"amount_granted_value";s:0:"";s:20:"amount_requested_min";s:0:"";s:20:"amount_requested_max";s:0:"";s:19:"amount_requested_op";s:3:"lte";s:22:"amount_requested_value";s:0:"";s:34:"application_received_date_relative";s:1:"0";s:30:"application_received_date_from";s:0:"";s:28:"application_received_date_to";s:0:"";s:28:"money_transfer_date_relative";s:1:"0";s:24:"money_transfer_date_from";s:0:"";s:22:"money_transfer_date_to";s:0:"";s:23:"grant_due_date_relative";s:1:"0";s:19:"grant_due_date_from";s:0:"";s:17:"grant_due_date_to";s:0:"";s:11:"description";s:19:"Grant Report Detail";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviGrant";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql" skip="true"}Grant Report (Detail){/ts}', 'Grant Report Detail', 'access CiviGrant', '',@reportlastID, '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Grant Report (Statistics)', 'grant/statistics', 'Grant Report Statistics', 'access CiviGrant', '{literal}a:42:{s:6:"fields";a:2:{s:18:"summary_statistics";s:1:"1";s:13:"grant_type_id";s:1:"1";}s:34:"application_received_date_relative";s:1:"0";s:30:"application_received_date_from";s:0:"";s:28:"application_received_date_to";s:0:"";s:22:"decision_date_relative";s:1:"0";s:18:"decision_date_from";s:0:"";s:16:"decision_date_to";s:0:"";s:28:"money_transfer_date_relative";s:1:"0";s:24:"money_transfer_date_from";s:0:"";s:22:"money_transfer_date_to";s:0:"";s:23:"grant_due_date_relative";s:1:"0";s:19:"grant_due_date_from";s:0:"";s:17:"grant_due_date_to";s:0:"";s:13:"grant_type_op";s:2:"in";s:16:"grant_type_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:20:"amount_requested_min";s:0:"";s:20:"amount_requested_max";s:0:"";s:19:"amount_requested_op";s:3:"lte";s:22:"amount_requested_value";s:0:"";s:18:"amount_granted_min";s:0:"";s:18:"amount_granted_max";s:0:"";s:17:"amount_granted_op";s:3:"lte";s:20:"amount_granted_value";s:0:"";s:24:"grant_report_received_op";s:2:"eq";s:27:"grant_report_received_value";s:0:"";s:12:"gender_id_op";s:2:"in";s:15:"gender_id_value";a:0:{}s:15:"contact_type_op";s:2:"in";s:18:"contact_type_value";a:0:{}s:12:"region_id_op";s:2:"in";s:15:"region_id_value";a:0:{}s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:11:"description";s:23:"Grant Report Statistics";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:1:"0";s:9:"parent_id";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql" skip="true"}Grant Report (Statistics){/ts}', 'Grant Report Statistics', 'access CiviGrant', '',@reportlastID, '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mail Bounce Report', 'Mailing/bounce', 'Bounce Report for mailings', 'access CiviMail', '{literal}a:33:{s:6:"fields";a:5:{s:2:"id";s:1:"1";s:10:"first_name";s:1:"1";s:9:"last_name";s:1:"1";s:11:"bounce_name";s:1:"1";s:5:"email";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:15:"mailing_name_op";s:2:"in";s:18:"mailing_name_value";a:0:{}s:19:"bounce_type_name_op";s:2:"eq";s:22:"bounce_type_name_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:9:"order_bys";a:1:{i:1;a:1:{s:6:"column";s:1:"-";}}s:11:"description";s:26:"Bounce Report for mailings";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:15:"access CiviMail";s:6:"groups";s:0:"";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql" skip="true"}Mail Bounce Report{/ts}', '{literal}Mail Bounce Report{/literal}', 'access CiviMail', '',@reportlastID, '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mail Summary Report', 'Mailing/summary','Summary statistics for mailings','access CiviMail','{literal}a:25:{s:6:"fields";a:5:{s:4:"name";s:1:"1";s:11:"queue_count";s:1:"1";s:15:"delivered_count";s:1:"1";s:12:"bounce_count";s:1:"1";s:10:"open_count";s:1:"1";}s:15:"is_completed_op";s:2:"eq";s:18:"is_completed_value";s:1:"1";s:15:"mailing_name_op";s:2:"in";s:18:"mailing_name_value";a:0:{}s:9:"status_op";s:3:"has";s:12:"status_value";s:8:"Complete";s:11:"is_test_min";s:0:"";s:11:"is_test_max";s:0:"";s:10:"is_test_op";s:3:"lte";s:13:"is_test_value";s:1:"0";s:19:"start_date_relative";s:9:"this.year";s:15:"start_date_from";s:0:"";s:13:"start_date_to";s:0:"";s:17:"end_date_relative";s:9:"this.year";s:13:"end_date_from";s:0:"";s:11:"end_date_to";s:0:"";s:11:"description";s:31:"Summary statistics for mailings";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:15:"access CiviMail";s:6:"groups";s:0:"";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql" skip="true"}Mail Summary Report{/ts}', '{literal}Mail Summary Report{/literal}', 'access CiviMail', '',@reportlastID, '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mail Opened Report', 'Mailing/opened', 'Display contacts who opened emails from a mailing', 'access CiviMail', '{literal}a:31:{s:6:"fields";a:5:{s:2:"id";s:1:"1";s:10:"first_name";s:1:"1";s:9:"last_name";s:1:"1";s:12:"mailing_name";s:1:"1";s:5:"email";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:15:"mailing_name_op";s:2:"in";s:18:"mailing_name_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:9:"order_bys";a:1:{i:1;a:1:{s:6:"column";s:1:"-";}}s:11:"description";s:49:"Display contacts who opened emails from a mailing";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:15:"access CiviMail";s:6:"groups";s:0:"";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql" skip="true"}Mail Opened Report{/ts}', '{literal}Mail Opened Report{/literal}', 'access CiviMail', '',@reportlastID, '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mail Clickthrough Report', 'Mailing/clicks', 'Display clicks from each mailing', 'access CiviMail', '{literal}a:31:{s:6:"fields";a:6:{s:2:"id";s:1:"1";s:10:"first_name";s:1:"1";s:9:"last_name";s:1:"1";s:12:"mailing_name";s:1:"1";s:5:"email";s:1:"1";s:3:"url";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:15:"mailing_name_op";s:2:"in";s:18:"mailing_name_value";a:0:{}s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:9:"order_bys";a:1:{i:1;a:1:{s:6:"column";s:1:"-";}}s:11:"description";s:32:"Display clicks from each mailing";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:15:"access CiviMail";s:6:"groups";s:0:"";s:6:"charts";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql" skip="true"}Mail Clickthrough Report{/ts}', '{literal}Mail Clickthrough Report{/literal}', 'access CiviMail', '',@reportlastID, '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;


-- walk list survey report.
INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES
    (  @domainID, 'Survey Report (Detail)', 'survey/detail', 'Detailed report for canvassing, phone-banking, walk lists or other surveys.', '', '{literal}a:39:{s:6:"fields";a:2:{s:9:"sort_name";s:1:"1";s:6:"result";s:1:"1";}s:22:"assignee_contact_id_op";s:2:"eq";s:25:"assignee_contact_id_value";s:0:"";s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:17:"street_number_min";s:0:"";s:17:"street_number_max";s:0:"";s:16:"street_number_op";s:3:"lte";s:19:"street_number_value";s:0:"";s:14:"street_name_op";s:3:"has";s:17:"street_name_value";s:0:"";s:15:"postal_code_min";s:0:"";s:15:"postal_code_max";s:0:"";s:14:"postal_code_op";s:3:"lte";s:17:"postal_code_value";s:0:"";s:7:"city_op";s:3:"has";s:10:"city_value";s:0:"";s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:12:"survey_id_op";s:2:"in";s:15:"survey_id_value";a:0:{}s:12:"status_id_op";s:2:"eq";s:15:"status_id_value";s:1:"1";s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:75:"Detailed report for canvassing, phone-banking, walk lists or other surveys.";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:1:"0";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID, '&reset=1'), '{ts escape="sql" skip="true"}Survey Report (Detail){/ts}', 'Survey Report (Detail)', 'administer CiviCampaign,manage campaign,interview campaign contacts', 'OR', @reportlastID, '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

-- mailing detail report
INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mailing Detail Report', 'mailing/detail', 'Provides reporting on Intended and Successful Deliveries, Unsubscribes and Opt-outs, Replies and Forwards.', '', '{literal}a:30:{s:6:"fields";a:6:{s:9:"sort_name";s:1:"1";s:12:"mailing_name";s:1:"1";s:11:"delivery_id";s:1:"1";s:14:"unsubscribe_id";s:1:"1";s:9:"optout_id";s:1:"1";s:5:"email";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"mailing_id_op";s:2:"in";s:16:"mailing_id_value";a:0:{}s:18:"delivery_status_op";s:2:"eq";s:21:"delivery_status_value";s:0:"";s:18:"is_unsubscribed_op";s:2:"eq";s:21:"is_unsubscribed_value";s:0:"";s:12:"is_optout_op";s:2:"eq";s:15:"is_optout_value";s:0:"";s:13:"is_replied_op";s:2:"eq";s:16:"is_replied_value";s:0:"";s:15:"is_forwarded_op";s:2:"eq";s:18:"is_forwarded_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:9:"order_bys";a:1:{i:1;a:2:{s:6:"column";s:9:"sort_name";s:5:"order";s:3:"ASC";}}s:11:"description";s:21:"Mailing Detail Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:1:"0";s:9:"parent_id";s:0:"";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');
SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql" skip="true"}Mailing Detail Report{/ts}', 'Mailing Detail Report', 'administer CiviMail', 'OR', @reportlastID, '1', NULL, @instanceID+2 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;