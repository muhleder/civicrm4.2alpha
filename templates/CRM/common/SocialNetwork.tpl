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
{* Adds social networking buttons (Facebook like, Twitter tweet, and Google +1) to public pages (online contributions, event info) *}
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

<div class="crm-section crm-socialnetwork help">
    <h2>{ts}Help spread the word{/ts}</h2>
    <div class="description">
        {ts}Please help us and let your friends, colleagues and followers know about our page{/ts}
        {if $title}: <span class="bold">{$title}</span>{else}.{/if}
    </div>
    <div class="crm-fb-tweet-buttons">
        {if $emailMode eq true}
            {*use images for email*}
            <a href="//twitter.com/share?url={$url}&amp;text={$title}" id="crm_tweet">
                <img title="Twitter Tweet Button" src="{$config->userFrameworkResourceURL}/i/tweet.png" width="55px" height="20px"  alt="Tweet Button">
            </a>
            <a href="//www.facebook.com/plugins/like.php?href={$url}" target="_blank">
                <img title="Facebook Like Button" src="{$config->userFrameworkResourceURL}/i/fblike.png" alt="Facebook Button" />
            </a>
        {else}
            {*use advanced buttons for pages*}
            <div class="label">
                <iframe allowtransparency="true" frameborder="0" scrolling="no"
                src="//platform.twitter.com/widgets/tweet_button.html?text={$title}&amp;url={$url}" 
                style="width:100px; height:20px;">
                </iframe>
            </div>
            <div class="label">
                <g:plusone href={$url}></g:plusone>
            </div>
            <div class="label">
                <iframe src="//www.facebook.com/plugins/like.php?app_id=240719639306341&amp;href={$url}&amp;send=false&amp;layout=standard&amp;show_faces=false&amp;action=like&amp;colorscheme=light" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px; height:30px;" allowTransparency="true"> 
                </iframe>
            </div>
        {/if}
    </div>
    {if $pageURL}
        {if $emailMode neq true}
        <br/>
        {/if}
        <br/>
        <div class="clear"></div>
        <div>
            <span class="bold">{ts}You can also share the below link in an email or on your website.{/ts}</span>
            <br/>
            {ts 1=$pageURL}<a href="%1">%1</a>{/ts}
        </div>
    {/if}
</div>


