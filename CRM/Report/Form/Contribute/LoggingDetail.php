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


class CRM_Report_Form_Contribute_LoggingDetail extends CRM_Logging_ReportDetail
{
    function __construct()
    {
        $logging = new CRM_Logging_Schema;
        $this->tables[] = 'civicrm_contribution';
        $this->tables   = array_merge($this->tables, array_keys($logging->customDataLogTables()));

        $this->detail  = 'logging/contribute/detail';
        $this->summary = 'logging/contribute/summary';

        parent::__construct();
    }

    function buildQuickForm()
    {
        parent::buildQuickForm();

        // link back to summary report
        $this->assign('backURL', CRM_Report_Utils_Report::getNextUrl('logging/contribute/summary', 'reset=1', false, true));
    }

    protected function whoWhomWhenSql()
    {
        return "
            SELECT who.id who_id, who.display_name who_name, whom.id whom_id, whom.display_name whom_name
            FROM `{$this->db}`.log_civicrm_contribution l
            LEFT JOIN civicrm_contact who ON (l.log_user_id = who.id)
            LEFT JOIN civicrm_contact whom ON (l.contact_id = whom.id)
            WHERE log_action = 'Update' AND log_conn_id = %1 AND log_date = %2 ORDER BY log_date DESC LIMIT 1
        ";
    }
}
