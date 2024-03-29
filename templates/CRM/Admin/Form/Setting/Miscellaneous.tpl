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
<div class="crm-block crm-form-block crm-miscellaneous-form-block">
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <table class="form-layout">
        <tr class="crm-miscellaneous-form-block-dashboardCacheTimeout">
            <td class="label">{$form.dashboardCacheTimeout.label}</td>
            <td>{$form.dashboardCacheTimeout.html}<br />
                <span class="description">{ts}The number of minutes to cache dashlet content on dashboard.{/ts}</span></td>
        </tr>
    </table>

    <table class="form-layout">
        <tr class="crm-miscellaneous-form-block-checksumTimeout">
            <td class="label">{$form.checksumTimeout.label}</td>
            <td>{$form.checksumTimeout.html}<br />
                <span class="description">{ts}The number of days before a personalized (hashed) link will expire.{/ts}</span></td>
        </tr>
    </table>

    <table class="form-layout">
        <tr class="crm-miscellaneous-form-block-contactUndelete">
          <td class="label">{$form.contactUndelete.label}</td>
          <td>
            {$form.contactUndelete.html}<br />
            <p class="description">{ts}If enabled, deleted contacts will be moved to trash (instead of being destroyed). Users with the proper permission are able to search for the deleted contacts and restore them (or delete permanently).{/ts}</p>
          </td>
        </tr>
        <tr class="crm-miscellaneous-form-block-logging">
          <td class="label">{$form.logging.label}</td>
          <td>
            {$form.logging.html}<br />
          {if $validTriggerPermission}
            <p class="description">{ts}If enabled, all actions performed on non-cache tables will be logged (in the respective log_* tables).{/ts}</p>
            <div class="status message">{ts}Logging functionality is currently in beta. Please test this feature on a copy of your database prior to using it on a production site.{/ts}</div>
          {else}
            <p class="description">{ts}In order to use this functionality, the installation's database user must have privileges to create triggers (in MySQL 5.0 – and in MySQL 5.1 if binary logging is enabled – this means the SUPER privilege). This install either does not seem to have the required privilege enabled.{/ts}&nbsp;{ts}This functionality cannot be enabled on multilingual installations.{/ts}</p>
           {/if}
          </td>
        </tr>
        <tr class="crm-miscellaneous-form-block-doNotAttachPDFReceipt">
            <td class="label">{$form.doNotAttachPDFReceipt.label}</td>
            <td>{$form.doNotAttachPDFReceipt.html}<br />
                <p class="description">{ts}If enabled, CiviCRM sends PDF receipt as an attachment during event signup or online contribution.{/ts}</p>
            </td>
        </tr>
        <tr class="crm-miscellaneous-form-block-wkhtmltopdfPath">
            <td class="label">{$form.wkhtmltopdfPath.label}</td>
            <td>{$form.wkhtmltopdfPath.html}<br />
                <p class="description">{ts}If wkhtmltopdf is installed, CiviCRM will use it to generate PDF form letters.{/ts}</p>
            </td>
        </tr>
        <tr class="crm-miscellaneous-form-block-versionCheck">
            <td class="label">{$form.versionCheck.label}</td>
            <td>{$form.versionCheck.html}<br />
                <p class="description">{ts}If enabled, CiviCRM automatically checks availablity of a newer version of the software. New version alerts will be displayed on the main CiviCRM Administration page.{/ts}</p>
                <p class="description">{ts}When enabled, statistics about your CiviCRM installation are reported anonymously to the CiviCRM team to assist in prioritizing ongoing development efforts. The following information is gathered: CiviCRM version, versions of PHP, MySQL and framework (Drupal/Joomla/standalone), and default language. Counts (but no actual data) of the following record types are reported: contacts, activities, cases, relationships, contributions, contribution pages, contribution products, contribution widgets, discounts, price sets, profiles, events, participants, tell-a-friend pages, grants, mailings, memberships, membership blocks, pledges, pledge blocks and active payment processor types.{/ts}</p></td>
        </tr>
        <tr class="crm-miscellaneous-form-block-maxAttachments">
            <td class="label">{$form.maxAttachments.label}</td>
            <td>{$form.maxAttachments.html}<br />
                <span class="description">{ts}Maximum number of files (documents, images, etc.) which can attached to emails or activities.{/ts}</span></td>
        </tr>
	<tr class="crm-miscellaneous-form-block-maxFileSize">
	    <td class="label">{$form.maxFileSize.label} (in MB)</td>
            <td>{$form.maxFileSize.html}<br />
                <span class="description">{ts}Maximum Size of file (documents, images, etc.) which can attached to emails or activities.<br />Note: php.ini should support this file size.{/ts}</span></td>
        </tr>
    </table>
<h3>{ts}reCAPTCHA Keys{/ts}</h3>
    <div class="description">
        {ts}reCAPTCHA is a free service that helps prevent automated abuse of your site. To use reCAPTCHA on public-facing CiviCRM forms: sign up at <a href="http://recaptcha.net" "target=_blank">recaptcha.net</a>; enter the provided public and private reCAPTCHA keys here; then enable reCAPTCHA under Advanced Settings in any Profile.{/ts}
    </div>
    <table class="form-layout">
        <tr class="crm-miscellaneous-form-block-recaptchaPublicKey">
            <td class="label">{$form.recaptchaPublicKey.label}</td>
            <td>{$form.recaptchaPublicKey.html}</td>
        </tr>
        <tr class="crm-miscellaneous-form-block-recaptchaPrivateKey">
            <td class="label">{$form.recaptchaPrivateKey.label}</td>
            <td>{$form.recaptchaPrivateKey.html}</td>
        </tr>
        <tr class="crm-miscellaneous-form-block-recaptchaOptions">
            <td class="label">{$form.recaptchaOptions.label}</td>
            <td>{$form.recaptchaOptions.html}<br />
                <span class="description">{ts}You can specify the reCAPTCHA theme options as an comma seperated data.(eg: theme:'blackglass', lang : 'fr' ).<br />You can check the available options for reCAPTCHA here <a href="http://code.google.com/apis/recaptcha/docs/customization.html" "target=_blank">Customizing the Look and Feel of reCAPTCHA</a>.{/ts}</span></td>
        </tr>
        </table>
           <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
