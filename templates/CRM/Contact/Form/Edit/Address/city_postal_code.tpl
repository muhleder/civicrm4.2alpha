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
<tr><td colspan="3" style="padding:0;">
<table style="border:none;">
<tr>
    {if !empty($form.address.$blockId.city)}
       <td>
          {$form.address.$blockId.city.label}<br />
          {$form.address.$blockId.city.html}
       </td>
    {/if}
    {if !empty($form.address.$blockId.postal_code)}
       <td>
          {$form.address.$blockId.postal_code.label}&nbsp;<span>{ts}Suffix{/ts}</span><br />
          {$form.address.$blockId.postal_code.html}&nbsp;&nbsp;
          {$form.address.$blockId.postal_code_suffix.html}<br />
          <span class="description font-italic" style="white-space:nowrap;">{ts}Enter optional 'add-on' code after the dash ('plus 4' code for U.S. addresses).{/ts}</span>
       </td>
    {/if}
    <td colspan="2">&nbsp;&nbsp;</td>
</tr>
</table>
</td></tr>
