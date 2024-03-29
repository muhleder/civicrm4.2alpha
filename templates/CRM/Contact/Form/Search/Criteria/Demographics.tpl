{*
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
*}
<div id="demographics" class="form-item">
    <table class="form-layout">
       <tr>
       	  <tr><td><label>{ts}Birth Dates{/ts}</label></td></tr>
	  {include file="CRM/Core/DateRange.tpl" fieldName="birth_date" from='_low' to='_high'}
       </tr>
       <tr>
	 <td>
       	   {$form.is_deceased.label}<br />
           {$form.is_deceased.html}
             <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('is_deceased', 'Advanced'); return false;" >{ts}clear{/ts}</a>)</span>
         </td>
      </tr>
      <tr>
          <tr><td><label>{ts}Deceased Dates{/ts}</label></td></tr>
	  {include file="CRM/Core/DateRange.tpl" fieldName="deceased_date" from='_low' to='_high'}
      </tr>
      <tr>
         <td>
            {$form.gender.label}<br />
            {$form.gender.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('gender', 'Advanced'); return false;" >{ts}clear{/ts}</a>)</span>
         </td>
      </tr>
    </table>            
</div>

