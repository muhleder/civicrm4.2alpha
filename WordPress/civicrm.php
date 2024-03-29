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

/*
Plugin Name: CiviCRM
Plugin URI: http://civicrm.org/
Description: CiviCRM WP Plugin
Author: CiviCRM LLC
Version: 4.1.0
Author URI: http://civicrm.org/
License: AGPL3
*/

// there is no session handling in WP hence we start it for CiviCRM pages
if ( ! session_id( ) ) {
    session_start( );
}

//this is require for ajax calls in civicrm
if ( civicrm_wp_in_civicrm() ) {
    $_GET['noheader'] = true;
} else {
    $_GET['mode'] = 'wordpress';
}

function civicrm_wp_add_menu_items( ) {
    add_menu_page( 'CiviCRM', 'CiviCRM', 'access_civicrm_nav_link', 'CiviCRM', 'civicrm_wp_invoke' );
    add_options_page( 'CiviCRM Settings', 'CiviCRM Settings', 'manage_options', 'civicrm-settings', 'civicrm_db_settings');
}

function civicrm_db_settings( ) {
    $installFile = 
            WP_PLUGIN_DIR . DIRECTORY_SEPARATOR .         
            'civicrm' . DIRECTORY_SEPARATOR .
            'civicrm' . DIRECTORY_SEPARATOR .
            'install' . DIRECTORY_SEPARATOR .
            'index.php';
    include( $installFile );
}

function civicrm_wp_set_title( $title = '' ) {
    global $civicrm_wp_title;
    return empty( $civicrm_wp_title ) ? $title : $civicrm_wp_title;
}

function civicrm_setup_warning( ) {
    $installLink = admin_url() . "options-general.php?page=civicrm-settings";
    echo '<div id="civicrm-warning" class="updated fade"><p><strong>' .
    t( 'CiviCRM is almost ready.' ). '</strong> ' .
    t( 'You must <a href="!1">configure CiviCRM</a> for it to work.', array( '!1' => $installLink) )    .
    '</p></div>';
}

function civicrm_remove_wp_magic_quotes( ) {
    $_GET     = stripslashes_deep( $_GET     );
    $_POST    = stripslashes_deep( $_POST    );
    $_COOKIE  = stripslashes_deep( $_COOKIE  );
    $_REQUEST = stripslashes_deep( $_REQUEST );
}

function civicrm_wp_initialize( ) {

    static $initialized    = false;
    static $failure        = false;

    if ( $failure ) {
        return false;
    }

    if ( ! $initialized ) {

        // Check for php version and ensure its greater than 5.
        // do a fatal exit if
        if ( (int ) substr( PHP_VERSION, 0, 1 ) < 5 ) {
            echo "CiviCRM requires PHP Version 5.2 or greater. You are running PHP Version " . PHP_VERSION . "<p>";
            exit( );
        }

        $settingsFile = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR .
                        'civicrm' . DIRECTORY_SEPARATOR .
                        'civicrm.settings.php';
        
        if( !file_exists($settingsFile) ){
            $error = false;
        } else {
            $error = include_once( $settingsFile );
        }
        
        // autoload
        require_once 'CRM/Core/ClassLoader.php';
        $classLoader = new CRM_Core_ClassLoader();
        $classLoader->register();

        // get ready for problems
        $installLink    = admin_url() . "options-general.php?page=civicrm-settings";
        $docLinkInstall = "http://wiki.civicrm.org/confluence/display/CRMDOC/WordPress+Installation+Guide";
        $docLinkTrouble = "http://wiki.civicrm.org/confluence/display/CRMDOC/Installation+and+Configuration+Trouble-shooting";
        $forumLink      = "http://forum.civicrm.org/index.php/board,6.0.html";

        $errorMsgAdd = t("Please review the <a href='!1'>WordPress Installation Guide</a> and the <a href='!2'>Trouble-shooting page</a> for assistance. If you still need help installing, you can often find solutions to your issue by searching for the error message in the <a href='!3'>installation support section of the community forum</a>.</strong></p>", 
                         array('!1' => $docLinkInstall, '!2' => $docLinkTrouble, '!3' => $forumLink ) );
        
        $installMessage = t("Click <a href='!1'>here</a> for fresh install.", array( '!1' => $installLink ) );  
            
        if ( $error == false ) {
            header( 'Location: ' . admin_url() . 'options-general.php?page=civicrm-settings' );
            return false;
        }
        
        // this does pretty much all of the civicrm initialization
        if ( !file_exists( $civicrm_root.'CRM/Core/Config.php' )  ){
            $error = false;
        } else {
            $error = include_once( 'CRM/Core/Config.php' );
        }

        if ( $error == false ) {
            $failure = true;
            //FIX ME
            wp_die( "<strong><p class='error'>" . 
                                t("Oops! - The path for including CiviCRM code files is not set properly. Most likely there is an error in the <em>civicrm_root</em> setting in your CiviCRM settings file (!1).", 
                                   array( '!1' => $settingsFile ) ) .
                                "</p><p class='error'> &raquo; " . 
                                t("civicrm_root is currently set to: <em>!1</em>.", array( '!1' => $civicrm_root ) ) . 
                                "</p><p class='error'>" .  $errorMsgAdd . "</p></strong>" );
            return false;
        }

        $initialized = true;

        // initialize the system by creating a config object
        $config = CRM_Core_Config::singleton();

        // sync the logged in user with WP
        global $current_user;
        if ( $current_user ) {
            require_once 'CRM/Core/BAO/UFMatch.php';
            CRM_Core_BAO_UFMatch::synchronize( $current_user, false, 'WordPress',
                                               civicrm_get_ctype( 'Individual' ) );
        }

    }

    return true;
}

/**
 * Function to get the contact type
 * @param string $default contact type
 *
 * @return $ctype contact type
 */
function civicrm_get_ctype( $default = null ) 
{
    // here we are creating a new contact
    // get the contact type from the POST variables if any

    if ( isset( $_REQUEST['ctype'] ) ) {
        $ctype = $_REQUEST['ctype'];
    } else if ( isset( $_REQUEST['edit'] ) &&
                isset( $_REQUEST['edit']['ctype'] ) ) {
        $ctype = $_REQUEST['edit']['ctype'];
    } else {
        $ctype = $default;
    }

    if ( $ctype != 'Individual'   &&
         $ctype != 'Organization' &&
         $ctype != 'Household' ) {
        $ctype = $default;
    }
    return $ctype; 
}

function civicrm_wp_invoke( ) {
    static $alreadyInvoked = false;
    if ( $alreadyInvoked ) {
        return;
    }

    $alreadyInvoked = true;
    if ( ! civicrm_wp_initialize( ) ) {
        return '';
    }

    // CRM-95XX
    // At this point we are calling a civicrm function
    // Since WP messes up and always quotes the request, we need to reverse
    // what it just did
    civicrm_remove_wp_magic_quotes( );

    if ( isset( $_GET['q'] ) ) {
        $args = explode( '/', trim( $_GET['q'] ) );
    } else {
        $_GET['q'] = 'civicrm/dashboard';
        $_GET['reset'] = 1;
        $args = array( 'civicrm', 'dashboard' );
    }
   
    global $current_user;
    get_currentuserinfo( );
    
    /* bypass synchronize if running upgrade 
     * to avoid any serious non-recoverable error 
     * which might hinder the upgrade process. 
     */
    require_once 'CRM/Utils/Array.php';
    if ( CRM_Utils_Array::value( 'q', $_GET ) != 'civicrm/upgrade' ) {
        require_once 'CRM/Core/BAO/UFMatch.php';
        CRM_Core_BAO_UFMatch::synchronize( $current_user, false, 'WordPress', 'Individual', true );
    }

    require_once 'CRM/Core/Invoke.php';
    CRM_Core_Invoke::invoke( $args );
}

function civicrm_wp_scripts( ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }
    
    require_once 'CRM/Core/Smarty.php';
    $template = CRM_Core_Smarty::singleton( );
    $buffer = $template->fetch( 'CRM/common/jquery.files.tpl' );
    $lines  = preg_split( '/\s+/', $buffer );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        if ( strpos( $line, '.js' ) !== false ) {
            wp_enqueue_script( $line, WP_PLUGIN_URL . "/civicrm/civicrm/$line" );
        }
    }
    
    // add Common.js
    wp_enqueue_script( 'js/Common.js', WP_PLUGIN_URL . '/civicrm/civicrm/js/Common.js' );
    return;
}

function civicrm_wp_styles( ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }
    
    require_once 'CRM/Core/Smarty.php';
    $template = CRM_Core_Smarty::singleton( );
    $buffer = $template->fetch( 'CRM/common/jquery.files.tpl' );
    $lines  = preg_split( '/\s+/', $buffer );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        if ( strpos( $line, '.css' ) !== false ) {
            wp_register_style( $line, WP_PLUGIN_URL . "/civicrm/civicrm/$line" );
            wp_enqueue_style( $line );
        }
    }

    wp_register_style( 'civicrm/css/deprecate.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/deprecate.css" );
    wp_enqueue_style( 'civicrm/css/deprecate.css' );
    wp_register_style( 'civicrm/css/civicrm.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/civicrm.css" );
    wp_enqueue_style( 'civicrm/css/civicrm.css' );
    wp_register_style( 'civicrm/css/extras.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/extras.css" );
    wp_enqueue_style( 'civicrm/css/extras.css' );

    return;
}

function civicrm_wp_frontend( $shortcode = false ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }

    // set the frontend part for civicrm code
    $config = CRM_Core_Config::singleton( );
    $config->userFrameworkFrontend = true;

    if ( isset( $_GET['q'] ) ) {
        $args = explode( '/', trim( $_GET['q'] ) );
    }

    if ( $shortcode ) {
        civicrm_turn_comments_off( );
        civicrm_set_post_blank( );
    } else {
        add_filter('get_header', 'civicrm_turn_comments_off');
        add_filter('get_header', 'civicrm_set_post_blank');
    }
    
    // check permission
    if ( ! civicrm_check_permission( $args ) ) {
        if ( $shortcode ) {
            civicrm_set_frontendmessage( );
        } else {
            add_filter('the_content', 'civicrm_set_frontendmessage');
        }
        return;
    }
    
    require_once 'wp-includes/pluggable.php';
    
    // this places civicrm inside frontend theme
    // wp documentation rocks if you know what you are looking for
    // but best way is to check other plugin implementation :) 

    if ( $shortcode ) {
        civicrm_wp_invoke( );
    } else {
        add_filter('the_content', 'civicrm_wp_invoke');
    }
}

function civicrm_set_blank() {
    return;
}

function civicrm_set_frontendmessage() {
    return ts('You do not have permission to execute this url.');
}

function civicrm_set_post_blank(){
    global $post;
    $post->post_type = ''; //to hide posted on 
    $post->post_title = '';//to hide post title
    add_action('edit_post_link' , 'civicrm_set_blank');//hide the edit link
}

function civicrm_turn_comments_off() {
    global $post;
    $post->comment_status="closed";
}

function civicrm_check_permission( $args ) {
    if ( $args[0] != 'civicrm' ) {
        return false;
    }

    $config = CRM_Core_Config::singleton( );
    
    // set frontend true 
    $config->userFrameworkFrontend = true;

    require_once 'CRM/Utils/Array.php';
    // all profile and file urls, as well as user dashboard and tell-a-friend are valid
    $arg1 = CRM_Utils_Array::value( 1, $args );
    $validPaths = array( 'profile', 'user', 'dashboard', 'friend', 'file', 'ajax' );
    if ( in_array( $arg1 , $validPaths ) ) {
        return true;
    }
    
    $arg2 = CRM_Utils_Array::value( 2, $args );
    $arg3 = CRM_Utils_Array::value( 3, $args );

    // allow editing of related contacts
    if ( $arg1 == 'contact' &&
         $arg2 == 'relatedcontact' ) {
        return true;
    }

    // a contribution page 
    if ( in_array( 'CiviContribute', $config->enableComponents ) ) {
        if ( $arg1 == 'contribute' &&
            in_array( $arg2, array( 'transact', 'campaign', 'pcp') ) ) {
            return true;
        }
        
        if ( $arg1 == 'pcp' &&
            in_array( $arg2, array( 'info') ) ) {
            return true;
        }
    }

    // an event registration page is valid
    if ( in_array( 'CiviEvent', $config->enableComponents ) ) {
        if ( $arg1 == 'event' &&
             in_array( $arg2, array( 'register', 'info', 'participant', 'ical', 'confirm' ) ) ) {
            return true;
        }

        // also allow events to be mapped
        if ( $arg1 == 'contact' &&
             $arg2 == 'map'     &&
             $arg3 == 'event'   ) {
            return true;
        }

        if ( $arg1 == 'pcp' &&
            in_array( $arg2, array( 'info') ) ) {
            return true;
        }
    }
    
    // allow mailing urls to be processed
    if ( $arg1 == 'mailing' &&
         in_array( 'CiviMail', $config->enableComponents ) ) {
        if ( in_array( $arg2,
                       array( 'forward', 'unsubscribe', 'resubscribe', 'optout', 'subscribe', 'confirm' ) ) ) {
            return true;
        }
    }

    // allow petition sign in, CRM-7401
    if ( in_array( 'CiviCampaign', $config->enableComponents ) ) {
        if ( $arg1 == 'petition' &&
             $arg2 == 'sign' ) {
            return true;
        }
    }

    return false;
}

function wp_civicrm_capability( ){
    global $wp_roles;
    if ( !isset( $wp_roles ) ){
        $wp_roles = new WP_Roles();
    }
    
    //access civicrm page menu link to particular roles
    $roles = array( 'super admin', 'administrator', 'editor' );
    
    foreach( $roles as $role ){
        if ( is_array( $wp_roles->get_role( $role )->capabilities ) && !array_key_exists( 'access_civicrm_nav_link', $wp_roles->get_role( $role )->capabilities ) ){
            $wp_roles->add_cap( $role, 'access_civicrm_nav_link' );
        }
    }
}

function civicrm_wp_main( ) {
    add_action('init', 'wp_civicrm_capability');
    if ( is_admin() ) {
        add_action( 'admin_menu', 'civicrm_wp_add_menu_items' );

        //Adding "embed form" button
        if ( in_array( basename($_SERVER['PHP_SELF']),
                       array('post.php', 'page.php', 'page-new.php', 'post-new.php') ) ) {
            add_action('media_buttons_context', 'civicrm_add_form_button');
            add_action('admin_footer'         , 'civicrm_add_form_button_html' );
        }

        // check if settings file exist, do not show configuration link on
        // install / settings page
        if ( isset( $_GET['page'] ) && $_GET['page'] != 'civicrm-settings' ) {
            $settingsFile = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 
                'civicrm' . DIRECTORY_SEPARATOR .'civicrm.settings.php';
        
            if ( !file_exists( $settingsFile ) ) {
                add_action( 'admin_notices', 'civicrm_setup_warning' );
            }
        }
    }

    add_action( 'user_register'   , 'civicrm_user_register'  );
    add_action( 'profile_update'  , 'civicrm_profile_update' );

    add_shortcode( 'civicrm', 'civicrm_shortcode_handler' );

    if ( !is_admin( ) ) {
        add_filter('get_header', 'civicrm_wp_shortcode_includes');
    }
    
    if ( ! civicrm_wp_in_civicrm( ) ) {
        return;
    }

    if ( ! is_admin( ) ) {
        add_action( 'wp_print_styles' , 'civicrm_wp_styles' );
 
        add_action('wp_footer', 'civicrm_buffer_end');

        // we do this here rather than as an action, since we dont control
        // the order
        civicrm_buffer_start( );

        civicrm_wp_frontend();
    } else {
        add_action( 'admin_print_styles' , 'civicrm_wp_styles' );
    }
    add_action( 'wp_print_scripts', 'civicrm_wp_scripts' );
}

function civicrm_add_form_button( $context ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return '';
    }

    $config = CRM_Core_Config::singleton( );
    $imageBtnURL = $config->resourceBase . 'i/widget/logo.png';
    $out = '<a href="#TB_inline?width=480&inlineId=civicrm_frontend_pages" class="thickbox" id="add_civi" title="' . __("Add CiviCRM Public Pages", 'CiviCRM') . '"><img src="'.$imageBtnURL.'" hieght="15" width="15" alt="' . __("Add CiviCRM Public Pages", 'CiviCRM') . '" /></a>';
    return $context . $out;
}

function civicrm_add_form_button_html( ) {
    $title = _e( "Please select a CiviCRM front-end page type.", "CiviCRM" );

    $now = date( "Ymdhis" );

    $sql = "
SELECT id, title
FROM   civicrm_contribution_page
WHERE  is_active = 1
AND    (
         ( start_date IS NULL AND end_date IS NULL )
OR       ( start_date <= $now AND end_date IS NULL )
OR       ( start_date IS NULL AND end_date >= $now )
OR       ( start_date <= $now AND end_date >= $now )
       )
";

    $dao = CRM_Core_DAO::executeQuery( $sql );
    $contributionPages = array();
    while ( $dao->fetch() ) {
        $contributionPages[$dao->id] = $dao->title;
    }

    $sql = "
SELECT id, title
FROM   civicrm_event
WHERE  is_active = 1
AND ( is_template = 0 OR is_template IS NULL )
AND    (
         ( start_date IS NULL AND end_date IS NULL )
OR       ( start_date <= $now AND end_date IS NULL )
OR       ( start_date IS NULL AND end_date >= $now )
OR       ( start_date <= $now AND end_date >= $now )
OR       ( start_date >= $now )
       )
";

    $dao = CRM_Core_DAO::executeQuery( $sql );
    $eventPages = array();
    while ( $dao->fetch() ) {
        $eventPages[$dao->id] = $dao->title;
    }
?>
        <script>
            function InsertCiviFrontPages( ) {
                var form_id = jQuery("#add_civicomponent_id").val();
                if (form_id == ""){
                    alert ('Please select a frontend element.');
                    return;
                }

                var action;
                var mode;
                var pid;
                var component = jQuery("#add_civicomponent_id").val( );
                switch ( component ) {
                    case 'contribution':
                        var pid  = jQuery("#add_contributepage_id").val();
                        var mode = jQuery("input[name='component_mode']:checked").val( );
                        break;
                    case 'event':
                        var pid    = jQuery("#add_eventpage_id").val();
                        var action = jQuery("input[name='event_action']:checked").val( );
                        var mode   = jQuery("input[name='component_mode']:checked").val( );
                        break;
                    case 'user-dashboard':
                        break;
                }
                
                // [ civicrm component=contribution/event/profile id=N mode=test/live action=info/register/create/search/edit/view ] 
                var shortcode = '[civicrm component="' + component + '"';
                
                if ( pid ) {
                    shortcode = shortcode + ' id="'+ pid +'"';
                }

                if ( mode ) {
                    shortcode = shortcode + ' mode="'+ mode +'"';
                }

                if ( action ) {
                    shortcode = shortcode + ' action="'+ action +'"';
                }

                shortcode = shortcode + ']';
                window.send_to_editor( shortcode );
            }

            jQuery(function() {
                jQuery('#add_civicomponent_id').change(function(){
                    var component = jQuery(this).val();
                    switch ( component ) {
                        case 'contribution':
                            jQuery('#contribution-section').show();
                            jQuery('#event-section').hide();
                            jQuery('#component-section').show();
                            jQuery('#action-section-event').hide();
                            break;
                        case 'event':
                            jQuery('#contribution-section').hide();
                            jQuery('#event-section').show();
                            jQuery('#component-section').show();
                            jQuery('#action-section-event').show();
                            break;
                        case 'user-dashboard':
                            jQuery('#contribution-section').hide();
                            jQuery('#event-section').hide();
                            jQuery('#component-section').hide();
                            jQuery('#action-section-event').hide();
                            break;
                    }
                });
            });
        </script>

        <div id="civicrm_frontend_pages" style="display:none;">
            <div class="wrap">
                <div>
                    <div style="padding:15px 15px 0 15px;">
                        <h3 style="color:#5A5A5A!important; font-family:Georgia,Times New Roman,Times,serif!important; font-size:1.8em!important; font-weight:normal!important;">
                        <?php echo $title; ?>
                        </h3>
                        <span>
                            <?php echo $title; ?> 
                        </span>
                    </div>
                    <div style="padding:15px 15px 0 15px;">
                        <select id="add_civicomponent_id">
                            <option value="">  <?php _e("Select a frontend element."); ?>  </option>
                            <option value="contribution">Contribution Page</option>
                            <option value="event">Event Page</option>
                            <option value="user-dashboard">User Dashboard</option>
                        </select>

                         <span id="contribution-section" style="display:none;">
                            <select id="add_contributepage_id">
                            <?php
                                foreach($contributionPages as $key => $value ) {
                                    ?>
                                    <option value="<?php echo absint( $key ) ?>"><?php echo esc_html( $value ) ?></option>
                                    <?php
                                }
                            ?>
                            </select>
                        </span>
                        
                        <span id="event-section" style="display:none;">
                            <select id="add_eventpage_id">
                            <?php
                                foreach($eventPages as $key => $value ) {
                                    ?>
                                    <option value="<?php echo absint( $key ) ?>"><?php echo esc_html( $value ) ?></option>
                                    <?php
                                }
                            ?>
                            </select>
                        </span>
                        <br>
                        <span id="action-section-event" style="display:none;">
                           <div style="padding:15px 15px 0 15px;"> 
                            <input type="radio" name="event_action" value="info" checked="checked" /> Event Info Page
                            <input type="radio" name="event_action" value="register" /> Event Registration Page
                           </div>
                        </span>
                        <br/>
                        <span id="component-section" style="display:none;">
                           <div style="padding:15px 15px 0 15px;"> 
                            <input type="radio" name="component_mode" value="live" checked="checked"/> Live Page
                            <input type="radio" name="component_mode" value="test" /> Test Drive
                           </div>
                        </span>
                        <br/>
                        <div style="padding:8px 0 0 0; font-size:11px; font-style:italic; color:#5A5A5A"><?php _e("Can't find your form? Make sure it is active.", "gravityforms"); ?></div>
                    </div>
                    <div style="padding:15px;">
                        <input type="button" class="button-primary" value="Insert Form" onclick="InsertCiviFrontPages();"/>&nbsp;&nbsp;&nbsp;
                    <a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel"); ?></a>
                    </div>
                </div>
            </div>
        </div>

<?php

}

function civicrm_shortcode_handler( $atts ) {
    extract( shortcode_atts( array( 'component' => 'contribution',
                                    'action'    => null,
                                    'mode'      => null,
                                    'id'        => null,
                                    'cid'       => null,
                                    'gid'       => null,
                                    'cs'        => null ),
                             $atts ) );

    $args = array( 'reset' => 1,
                   'id'    => $id );
    
    switch ( $component ) {
    case 'contribution':
        $args['q' ] = 'civicrm/contribute/transact';
        break;

    case 'event':
        switch ( $action ) {
        case 'register':
            $args['q' ]     = 'civicrm/event/register';
            if ( $mode == 'preview' ) {
                $args['action'] = $mode;
            }
            break;

        case 'info':
            $args['q' ] = 'civicrm/event/info';
            break;

        default:
            echo 'Do not know how to handle this shortcode<p>';
            return;
        }
        break;

    case 'user-dashboard':
        $args['q'] = 'civicrm/user';
        unset($args['id']);
        break;
        
    default:
        echo 'Do not know how to handle this shortcode<p>';
        return;
        
    }

    foreach ( $args as $key => $value ) {
        if ( $value !== null ) {
            $_GET[$key] = $value;
        }
    }

    return civicrm_wp_frontend( true );
}

function civicrm_wp_in_civicrm( ) {
    return ( isset( $_GET['page'] ) &&
             $_GET['page'] == 'CiviCRM' ) ? true : false;
}

function civicrm_wp_shortcode_includes( ){
    global $post;
    if ( preg_match( '/\[civicrm/', $post->post_content ) ) {
        add_action( 'wp_print_styles' , 'civicrm_wp_styles' );
        add_action( 'wp_print_scripts', 'civicrm_wp_scripts' );
    }
}

function wp_get_breadcrumb( ) {
    global $wp_set_breadCrumb;
    return $wp_set_breadCrumb;
}

function wp_set_breadcrumb( $breadCrumb ) {
    global $wp_set_breadCrumb;
    $wp_set_breadCrumb = $breadCrumb;
    return $wp_set_breadCrumb;
}

function t( $str, $sub = null ) {
    if(is_array($sub))
        $str = str_replace( array_keys($sub), array_values($sub), $str);
    return $str;
}

function civicrm_user_register( $userID ) {
    _civicrm_update_user( $userID );
}

function civicrm_profile_update( $userID ) {
    _civicrm_update_user( $userID );
}

function _civicrm_update_user( $userID ) {
    $user = get_userdata( $userID );
    if ( $user ) {
        civicrm_wp_initialize( );

        require_once 'CRM/Core/BAO/UFMatch.php';
        CRM_Core_BAO_UFMatch::synchronize( $user,
                                           true,
                                           'WordPress',
                                           'Individual' );
    }
}

function civicrm_buffer_start() {
    ob_start( "civicrm_buffer_callback" );
}

function civicrm_buffer_end() {
    ob_end_flush();
}
 
function civicrm_buffer_callback($buffer) {
    // modify buffer here, and then return the updated code
    return $buffer;
}

civicrm_wp_main( );
