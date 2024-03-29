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


/**
 * Drupal specific stuff goes here
 */
class CRM_Utils_System_Drupal6 extends CRM_Utils_System_Base {

    function __construct() {
      $this->is_drupal = true;
      $this->supports_form_extensions = TRUE;
    }

    /**
     * if we are using a theming system, invoke theme, else just print the
     * content
     *
     * @param string  $type    name of theme object/file
     * @param string  $content the content that will be themed
     * @param array   $args    the args for the themeing function if any
     * @param boolean $print   are we displaying to the screen or bypassing theming?
     * @param boolean $ret     should we echo or return output
     * @param boolean $maintenance  for maintenance mode
     * 
     * @return void           prints content on stdout
     * @access public
     */
    function theme( $type, &$content, $args = null, $print = false, $ret = false, $maintenance = false ) {
        // TODO: Simplify; this was copied verbatim from CiviCRM 3.4's multi-UF theming function, but that's more complex than necessary
        if ( function_exists( 'theme' ) && ! $print ) {
            if ( $maintenance ) {
                drupal_set_breadcrumb( '' );
                drupal_maintenance_theme();
            }
            $out = theme( $type, $content, $args );
        } else {
            $out = $content;
        }
        
        if ( $ret ) {
            return $out;
        } else {
            print $out;
        }
    }

    /**
     * Function to create a user in Drupal.
     *  
     * @param array  $params associated array 
     * @param string $mail email id for cms user
     *
     * @return uid if user exists, false otherwise
     * 
     * @access public
     */
    function createUser( &$params, $mail )
    {
        $form_state = array( );
        $form_state['values']  = array (
                                    'name' => $params['cms_name'],
                                    'mail' => $params[$mail],
                                    'op'   => 'Create new account'
                                    );
        if ( !variable_get('user_email_verification', TRUE )) {
            $form_state['values']['pass']['pass1'] = $params['cms_pass'];
            $form_state['values']['pass']['pass2'] = $params['cms_pass'];
        }

        $config = CRM_Core_Config::singleton( );

        // we also need to redirect b
        $config->inCiviCRM = true;

        $form = drupal_retrieve_form('user_register', $form_state);
        $form['#post'] = $form_state['values'];
        drupal_prepare_form('user_register', $form, $form_state);

        // remove the captcha element from the form prior to processing
        unset($form['captcha']);
        
        drupal_process_form('user_register', $form, $form_state);
        
        $config->inCiviCRM = false;
        
        if ( form_get_errors( ) ) {
            return false;
        }

        // looks like we created a drupal user, lets make another db call to get the user id!
        $db_cms = DB::connect($config->userFrameworkDSN);
        if ( DB::isError( $db_cms ) ) { 
            die( "Cannot connect to UF db via $dsn, " . $db_cms->getMessage( ) ); 
        }

        //Fetch id of newly added user
        $id_sql   = "SELECT uid FROM {$config->userFrameworkUsersTableName} where name = '{$params['cms_name']}'";
        $id_query = $db_cms->query( $id_sql );
        $id_row   = $id_query->fetchRow( DB_FETCHMODE_ASSOC ) ;
        return $id_row['uid'];
    }   
    /*
     *  Change user name in host CMS
     *  
     *  @param integer $ufID User ID in CMS
     *  @param string $ufName User name
     */
    function updateCMSName( $ufID, $ufName ) 
    {
           if ( function_exists( 'user_load' ) ) { // CRM-5555
                $user = user_load( array( 'uid' => $ufID ) );
                if ($user->mail != $ufName) {
                    user_save( $user, array( 'mail' => $ufName ) );
                    $user = user_load( array( 'uid' => $ufID ) );
                }
            }
        
    }
    
    /**
     * Check if username and email exists in the drupal db
     * 
     * @params $params    array   array of name and mail values
     * @params $errors    array   array of errors
     * @params $emailName string  field label for the 'email'
     *
     * @return void
     */
    function checkUserNameEmailExists( &$params, &$errors, $emailName = 'email' )
    {
        $config  = CRM_Core_Config::singleton( );
        
        $dao = new CRM_Core_DAO( );
        $name  = $dao->escape( CRM_Utils_Array::value( 'name', $params ) );
        $email = $dao->escape( CRM_Utils_Array::value( 'mail', $params ) );
        _user_edit_validate(null, $params );
        $errors = form_get_errors( );
        
        if ( $errors ) {
                if ( CRM_Utils_Array::value( 'name', $errors ) ) {
                    $errors['cms_name'] = $errors['name'];
                } 
                if ( CRM_Utils_Array::value( 'mail', $errors ) ) {
                    $errors[$emailName] = $errors['mail'];
                }          
                // also unset drupal messages to avoid twice display of errors
                unset( $_SESSION['messages'] );
         }
        
         // drupal api sucks do the name check manually
         $nameError = user_validate_name( $params['name'] );
         if ( $nameError ) {
                $errors['cms_name'] = $nameError;
         }
        
            $sql = "
SELECT name, mail
  FROM {$config->userFrameworkUsersTableName}
 WHERE (LOWER(name) = LOWER('$name')) OR (LOWER(mail) = LOWER('$email'))";

        
        $db_cms = DB::connect($config->userFrameworkDSN);
        if ( DB::isError( $db_cms ) ) { 
            die( "Cannot connect to UF db via $dsn, " . $db_cms->getMessage( ) ); 
        }
        $query = $db_cms->query( $sql );
        $row = $query->fetchRow( );
        if ( !empty( $row ) ) {
            $dbName  = CRM_Utils_Array::value( 0, $row );
            $dbEmail = CRM_Utils_Array::value( 1, $row );
            if ( strtolower( $dbName ) == strtolower( $name ) ) {
                $errors['cms_name'] = ts( 'The username %1 is already taken. Please select another username.', 
                                          array( 1 => $name ) );
            }
            if ( strtolower( $dbEmail ) == strtolower( $email ) ) {
                $errors[$emailName] = ts( 'This email %1 is already registered. Please select another email.', 
                                          array( 1 => $email) );
            }
        }
    }
    /*
     * Function to get the drupal destination string. When this is passed in the
     * URL the user will be directed to it after filling in the drupal form
     *
     * @param object $form Form object representing the 'current' form - to which the user will be returned
     * @return string $destination destination value for URL
     *
     */
    function getLoginDestination( &$form ) {
        $args = null;

        $id = $form->get( 'id' );
        if ( $id ) {
            $args .= "&id=$id";
        } else {
            $gid =  $form->get( 'gid' );
            if ( $gid ) {
                $args .= "&gid=$gid";
            } else {
                // Setup Personal Campaign Page link uses pageId
                $pageId =  $form->get( 'pageId' );
                if ( $pageId ) {
                    $component = $form->get( 'component');
                    $args .= "&pageId=$pageId&component=$component&action=add";
                }
            }
        }
    
        $destination = null;
        if ( $args ) {
            // append destination so user is returned to form they came from after login
            $destination = CRM_Utils_System::currentPath( ) . '?reset=1' . $args;
        }
        return $destination;
      
    }   
    /**
     * sets the title of the page
     *
     * @param string $title
     * @paqram string $pageTitle
     *
     * @return void
     * @access public
     */
    function setTitle( $title, $pageTitle = null ) {
        if ( !$pageTitle ) {
            $pageTitle = $title;
        }
        if ( arg(0) == 'civicrm' )	{
            //set drupal title 
            drupal_set_title( $pageTitle ); 
        }
    }
    
    /**
     * Append an additional breadcrumb tag to the existing breadcrumb
     *
     * @param string $title
     * @param string $url   
     *
     * @return void
     * @access public
     */
    function appendBreadCrumb( $breadCrumbs ) {
        $breadCrumb = drupal_get_breadcrumb( );

        if ( is_array( $breadCrumbs ) ) {
            foreach ( $breadCrumbs as $crumbs ) {
                if ( stripos($crumbs['url'], 'id%%') ) {
                    $args = array( 'cid', 'mid' );
                    foreach ( $args as $a ) {
                        $val  = CRM_Utils_Request::retrieve( $a, 'Positive', CRM_Core_DAO::$_nullObject,
                                                             false, null, $_GET );
                        if ( $val ) {
                            $crumbs['url'] = str_ireplace( "%%{$a}%%", $val, $crumbs['url'] );
                        }
                    }
                }
                $breadCrumb[]  = "<a href=\"{$crumbs['url']}\">{$crumbs['title']}</a>";
            }
        }
        drupal_set_breadcrumb( $breadCrumb );
    }

    /**
     * Reset an additional breadcrumb tag to the existing breadcrumb
     *
     * @return void
     * @access public
     */
    function resetBreadCrumb( ) {
        $bc = array( );
        drupal_set_breadcrumb( $bc );
    }

    /**
     * Append a string to the head of the html file
     *
     * @param string $head the new string to be appended
     *
     * @return void
     * @access public
     */
    function addHTMLHead( $head ) {
      drupal_set_html_head( $head );
    }

    /** 
     * rewrite various system urls to https 
     *  
     * @param null 
     *
     * @return void 
     * @access public  
     */  
    function mapConfigToSSL( ) {
        global $base_url;
        $base_url = str_replace( 'http://', 'https://', $base_url );
    }

    /**
     * figure out the post url for the form
     *
     * @param mix $action the default action if one is pre-specified
     *
     * @return string the url to post the form
     * @access public

     */
    function postURL( $action ) {
        if ( ! empty( $action ) ) {
            return $action;
        }

        return $this->url( $_GET['q'] );
    }

    /**
     * Generate an internal CiviCRM URL (copied from DRUPAL/includes/common.inc#url)
     *
     * @param $path     string   The path being linked to, such as "civicrm/add"
     * @param $query    string   A query string to append to the link.
     * @param $absolute boolean  Whether to force the output to be an absolute link (beginning with http:).
     *                           Useful for links that will be displayed outside the site, such as in an
     *                           RSS feed.
     * @param $fragment string   A fragment identifier (named anchor) to append to the link.
     * @param $htmlize  boolean  whether to convert to html eqivalant
     * @param $frontend boolean  a gross joomla hack
     *
     * @return string            an HTML string containing a link to the given path.
     * @access public
     *
     */
    function url($path = null, $query = null, $absolute = false,
                 $fragment = null, $htmlize = true,
                 $frontend = false ) {
        $config = CRM_Core_Config::singleton( );
        $script =  'index.php';

        $path = CRM_Utils_String::stripPathChars( $path );

        if (isset($fragment)) {
            $fragment = '#'. $fragment;
        }

        if ( ! isset( $config->useFrameworkRelativeBase ) ) {
            $base = parse_url( $config->userFrameworkBaseURL );
            $config->useFrameworkRelativeBase = $base['path'];
        }
        $base = $absolute ? $config->userFrameworkBaseURL : $config->useFrameworkRelativeBase;

        $separator = $htmlize ? '&amp;' : '&';

        if (! $config->cleanURL ) {
            if ( isset( $path ) ) {
                if ( isset( $query ) ) {
                    return $base . $script .'?q=' . $path . $separator . $query . $fragment;
                } else {
                    return $base . $script .'?q=' . $path . $fragment;
                }
            } else {
                if ( isset( $query ) ) {
                    return $base . $script .'?'. $query . $fragment;
                } else {
                    return $base . $fragment;
                }
            }
        } else {
            if ( isset( $path ) ) {
                if ( isset( $query ) ) {
                    return $base . $path .'?'. $query . $fragment;
                } else {
                    return $base . $path . $fragment;
                }
            } else {
                if ( isset( $query ) ) {
                    return $base . $script .'?'. $query . $fragment;
                } else {
                    return $base . $fragment;
                }
            }
        }
    }

    /**
     * Authenticate the user against the drupal db
     *
     * @param string $name     the user name
     * @param string $password the password for the above user name
     *
     * @return mixed false if no auth
     *               array( contactID, ufID, unique string ) if success
     * @access public
     */
    function authenticate( $name, $password ) {
        require_once 'DB.php';

        $config = CRM_Core_Config::singleton( );
        
        $dbDrupal = DB::connect( $config->userFrameworkDSN );
        if ( DB::isError( $dbDrupal ) ) {
            CRM_Core_Error::fatal( "Cannot connect to drupal db via $config->userFrameworkDSN, " . $dbDrupal->getMessage( ) ); 
        }                                                      

        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        $password  = md5( $password );
        $name      = $dbDrupal->escapeSimple( $strtolower( $name ) );
        $sql = 'SELECT u.* FROM ' . $config->userFrameworkUsersTableName .
            " u WHERE LOWER(u.name) = '$name' AND u.pass = '$password' AND u.status = 1";
        $query = $dbDrupal->query( $sql );

        $user = null;
        // need to change this to make sure we matched only one row
        while ( $row = $query->fetchRow( DB_FETCHMODE_ASSOC ) ) { 
            CRM_Core_BAO_UFMatch::synchronizeUFMatch( $user, $row['uid'], $row['mail'], 'Drupal' );
            $contactID = CRM_Core_BAO_UFMatch::getContactId( $row['uid'] );
            if ( ! $contactID ) {
                return false;
            }
            return array( $contactID, $row['uid'], mt_rand() );
        }
        return false;
    }
    /*
    * Load user into session
    */
    function loadUser( $username ) {
        global $user;
        $user = user_load(array('name' => $username));
        if(empty($user->uid)) return false;

        $contact_id = CRM_Core_BAO_UFMatch::getContactId( $uid );

        // lets store contact id and user id in session
        $session = CRM_Core_Session::singleton( );
        $session->set( 'ufID'  , $uid );
        $session->set( 'userID', $contact_id );
        return true;
    }
    /**   
     * Set a message in the UF to display to a user 
     *   
     * @param string $message the message to set 
     *   
     * @access public   
     */   
    function setMessage( $message ) {
        drupal_set_message( $message );
    }

    function permissionDenied( ) {
        drupal_access_denied( );
    }

    function logout( ) {
        module_load_include( 'inc', 'user', 'user.pages' );
        return user_logout( );
    }

    function updateCategories( ) {
        // copied this from profile.module. Seems a bit inefficient, but i dont know a better way
        // CRM-3600
        cache_clear_all();
        menu_rebuild();
    }

    /**
     * Get the locale set in the hosting CMS
     * @return string  with the locale or null for none
     */
    function getUFLocale()
    {
        // return CiviCRM’s xx_YY locale that either matches Drupal’s Chinese locale
        // (for CRM-6281), Drupal’s xx_YY or is retrieved based on Drupal’s xx
        global $language;
        switch (true) {
        case $language->language == 'zh-hans':             return 'zh_CN';
        case $language->language == 'zh-hant':             return 'zh_TW';
        case preg_match('/^.._..$/', $language->language): return $language->language;
        default:
            return CRM_Core_I18n_PseudoConstant::longForShort(substr($language->language, 0, 2));
        }
    }
    
    function getVersion() {
        return defined('VERSION') ? VERSION : 'Unknown';
    }

    /**
     * load drupal bootstrap
     *
     * @param $name string  optional username for login
     * @param $pass string  optional password for login
     */
    function loadBootStrap($params = array( ), $loadUser = true, $throwError = true, $realPath = null  )
    {
       $uid = CRM_Utils_Array::value( 'uid', $params );
       $name = CRM_Utils_Array::value( 'name', $params, false ) ? $params['name'] : trim(CRM_Utils_Array::value('name', $_REQUEST));
       $pass = CRM_Utils_Array::value( 'pass', $params, false ) ? $params['pass'] : trim(CRM_Utils_Array::value('pass', $_REQUEST));
      
        //take the cms root path.
        $cmsPath = $this->cmsRootPath($realPath );
        if ( !file_exists( "$cmsPath/includes/bootstrap.inc" ) ) {
            echo '<br />Sorry, unable to locate bootstrap.inc.';
            exit( );
        }
        
        chdir($cmsPath);
        require_once 'includes/bootstrap.inc';
        @drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        
        if ( !function_exists('module_exists') || 
             !module_exists( 'civicrm' ) ) {
            echo '<br />Sorry, could not able to load drupal bootstrap.';
            exit( );
        }
            // lets also fix the clean url setting
        // CRM-6948
        $config->cleanURL = (int) variable_get('clean_url', '0');

        // we need to call the config hook again, since we now know
        // all the modules that are listening on it, does not apply
        // to J! and WP as yet
        // CRM-8655
        CRM_Utils_Hook::config( $config );

        if ( ! $loadUser ) {
            return true;
        }
        //load user, we need to check drupal permissions.
        if ( $name ) {
            $user = user_authenticate(  array( 'name' => $name, 'pass' => $pass ) );
            if ( empty( $user->uid ) ) {
                echo '<br />Sorry, unrecognized username or password.';
                exit( );
            }
        } else if ( $uid ) {
            $account = user_load( array( 'uid' => $uid ) );
            if ( empty( $account->uid ) ) {
                echo '<br />Sorry, unrecognized user id.';
                exit( );
            } else {
                global $user;
                $user = $account;
            }
        }
        
    }
    
    function cmsRootPath($scriptFilename ) 
    {
        $cmsRoot = $valid = null;

        if( !is_null( $scriptFilename ) ) {
            $path = $scriptFilename;
        } else {
            $path = $_SERVER['SCRIPT_FILENAME'];
        }
        if ( function_exists( 'drush_get_context' ) ) {
            // drush anyway takes care of multisite install etc
            return drush_get_context('DRUSH_DRUPAL_ROOT');
        }
        // CRM-7582
        $pathVars = explode( '/', 
                             str_replace('//', '/', 
                                         str_replace( '\\', '/', $path ) ) );
        
        //lets store first var,
        //need to get back for windows.
        $firstVar = array_shift( $pathVars );
        
        //lets remove sript name to reduce one iteration.
        array_pop( $pathVars );
        
        //CRM-7429 --do check for upper most 'includes' dir,
        //which would effectually work for multisite installation.
        do {
            $cmsRoot = $firstVar . '/'.implode( '/', $pathVars );
            $cmsIncludePath = "$cmsRoot/includes";
            //stop as we found bootstrap.
            if ( @opendir( $cmsIncludePath ) && 
                 file_exists( "$cmsIncludePath/bootstrap.inc" ) ) { 
                $valid = true;
                break;
            }
            //remove one directory level.
            array_pop( $pathVars );
        } while ( count( $pathVars ) ); 
        
        return ( $valid ) ? $cmsRoot : null;
    }
    
    /**
     * check is user logged in.
     *
     * @return boolean true/false.
     */
    public function isUserLoggedIn( ) {
        $isloggedIn = false;
        if ( function_exists( 'user_is_logged_in' ) ) {
            $isloggedIn = user_is_logged_in( );
        }
        
        return $isloggedIn;
    }
    
    /**
     * Get currently logged in user uf id.
     *
     * @return int $userID logged in user uf id.
     */
    public function getLoggedInUfID( ) {
        $ufID = null;
        if ( function_exists( 'user_is_logged_in' ) && 
             user_is_logged_in( ) && 
             function_exists( 'user_uid_optional_to_arg' ) ) {
            $ufID = user_uid_optional_to_arg( array( ) );
        }
        
        return $ufID;
    }
    
    /**
     * Format the url as per language Negotiation.
     * 
     * @param string $url
     *
     * @return string $url, formatted url.
     * @static
     */
    function languageNegotiationURL( $url, 
                                            $addLanguagePart    = true, 
                                            $removeLanguagePart = false ) 
    {
        if ( empty( $url ) ) return $url;
        
        //upto d6 only, already we have code in place for d7 
        $config = CRM_Core_Config::singleton( );
        if ( function_exists('variable_get') && 
             module_exists('locale') ) {
            global $language;
            
            //get the mode.
            $mode = variable_get('language_negotiation', LANGUAGE_NEGOTIATION_NONE );
            
            //url prefix / path.
            if ( isset( $language->prefix ) &&
                 $language->prefix &&
                 in_array( $mode, array( LANGUAGE_NEGOTIATION_PATH,
                                         LANGUAGE_NEGOTIATION_PATH_DEFAULT ) ) ) {
                
                if ( $addLanguagePart ) {
                    $url .=  $language->prefix . '/';
                }
                if ( $removeLanguagePart ) {
                    $url = str_replace( "/{$language->prefix}/", '/', $url );
                }
            }
            if ( isset( $language->domain ) &&
                 $language->domain &&
                 $mode == LANGUAGE_NEGOTIATION_DOMAIN ) {
                
                if ( $addLanguagePart ) {
                    $url = CRM_Utils_File::addTrailingSlash( $language->domain, '/' );
                }
                if ( $removeLanguagePart && defined( 'CIVICRM_UF_BASEURL' ) ) {
                    $url = str_replace( '\\', '/', $url );
                    $parseUrl = parse_url( $url );
                    
                    //kinda hackish but not sure how to do it right		
                    //hope http_build_url() will help at some point.
                    if ( is_array( $parseUrl ) && !empty( $parseUrl ) ) {
                        $urlParts   = explode( '/', $url );
                        $hostKey    = array_search( $parseUrl['host'], $urlParts );
                        $ufUrlParts = parse_url( CIVICRM_UF_BASEURL );
                        $urlParts[$hostKey] = $ufUrlParts['host'];
                        $url = implode( '/', $urlParts );
                    }
                }
            }
        }
        
        return $url;
    }
}