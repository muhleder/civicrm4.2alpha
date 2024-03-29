<?php

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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


class CRM_Core_I18n_PseudoConstant
{
    static function &languages()
    {
        static $languages = null;
        if ($languages === null) {
            $rows = array();
            CRM_Core_OptionValue::getValues(array('name' => 'languages'), $rows, 'weight', true);

            $languages = array();
            foreach ($rows as $row) {
                $languages[$row['name']] = $row['label'];
            }
        }
        return $languages;
    }

    static function longForShort($short)
    {
        $longForShortMapping = self::longForShortMapping();
        return $longForShortMapping[$short];
    }

    static function &longForShortMapping()
    {
        static $longForShortMapping = null;
        if ($longForShortMapping === null) {
            $rows = array();
            CRM_Core_OptionValue::getValues(array('name' => 'languages'), $rows);

            $longForShortMapping = array();
            foreach ($rows as $row) {
                $longForShortMapping[$row['value']] = $row['name'];
            }
            // hand-crafted enforced overrides for language variants
            $longForShortMapping['zh'] = defined("CIVICRM_LANGUAGE_MAPPING_ZH") ? CIVICRM_LANGUAGE_MAPPING_ZH : 'zh_CN';
            $longForShortMapping['en'] = defined("CIVICRM_LANGUAGE_MAPPING_EN") ? CIVICRM_LANGUAGE_MAPPING_EN : 'en_US';
            $longForShortMapping['fr'] = defined("CIVICRM_LANGUAGE_MAPPING_FR") ? CIVICRM_LANGUAGE_MAPPING_FR : 'fr_FR';
            $longForShortMapping['pt'] = defined("CIVICRM_LANGUAGE_MAPPING_PT") ? CIVICRM_LANGUAGE_MAPPING_PT : 'pt_PT';
            $longForShortMapping['es'] = defined("CIVICRM_LANGUAGE_MAPPING_ES") ? CIVICRM_LANGUAGE_MAPPING_ES : 'es_ES';
        }
        return $longForShortMapping;
    }

    static function shortForLong($long)
    {
        return substr($long, 0, 2);
    }
}
