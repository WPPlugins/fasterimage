<?php
/*
Plugin Name: fasterImage
Plugin URI: http://fasterimage.io/optimiser-image/wordpress
Description: Optimisez vos images & accélérez votre site web !
Version: 1.3
Author: fasterImage
Author URI: http://fasterimage.io/?utm_source=wordpress&utm_medium=plugin&utm_campaign=extensions
License: GPLv2 or later
*/
define('FASTERIMAGE_VERSION','1.3');

add_filter('wp_get_attachment_url'	, 'fasterimage_file_handle', 99999 );
add_filter('smilies_src', 'fasterimage_file_handle', 99999 );
function fasterimage_file_handle($sImageUrl) {

    $aFasterImageSupportedExtensions = array(
        'jpg','jpeg','gif','png'
    );

    if(!is_admin()) {
        $sEnabled = get_option('fasterimage_enabled');
        $sDomain = get_option('fasterimage_domain');
        $sChecked = get_option('fasterimage_account_valid');

        if ($sEnabled == '1' && strlen($sDomain) > 0 && $sChecked == '1') {
            //fasterImage is enabled and a domain is set

            //Allowed images extension check
            $bOkFormat = false;
            foreach($aFasterImageSupportedExtensions as $sFormat) {
                if(endsWith(strtolower($sImageUrl),'.'.$sFormat)) {
                    $bOkFormat = true;
                    break;
                }
            }

            if(!$bOkFormat) {
                //Not an allowed image
                return $sImageUrl;
            }

            $sSiteUrl = get_site_url();
            $iSiteUrlLength = strlen($sSiteUrl);

            $aUrlInfo = parse_url($sSiteUrl);

            $sNewSiteUrl = str_replace($aUrlInfo['host'], $sDomain, $sSiteUrl);

            if (strlen($sImageUrl) > $iSiteUrlLength && substr($sImageUrl, 0, $iSiteUrlLength) == $sSiteUrl) {
                return str_replace($sSiteUrl, $sNewSiteUrl, $sImageUrl);
            }

        }
    }

    return $sImageUrl;
}

add_filter( 'the_content', 'fasterimage_content_images', 99999 );
add_filter( 'widget_text', 'fasterimage_content_images', 99999 );
function fasterimage_content_images($sContent) {

    if ( !is_admin()) {

        $sEnabled = get_option('fasterimage_enabled');
        $sDomain = get_option('fasterimage_domain');
        $sChecked = get_option('fasterimage_account_valid');

        if ($sEnabled == '1' && strlen($sDomain) > 0 && $sChecked == '1') {
            //fasterImage is enabled and a domain is set

            $sSiteUrl = get_site_url();
            $aUrlInfo = parse_url($sSiteUrl);

            $sNewSiteUrl = str_replace($aUrlInfo['host'], $sDomain, $sSiteUrl);

            $sPattern = '/src=[\'"]'.str_replace('/','\/',str_replace('.','\.',$sSiteUrl)).'\/([abcdefghijklmnopqrstuvwxyz0123456789% ._~:\/?#@!$&\'()*+,;=\[\]-]+)\.(jpg|jpeg|gif|png)[\'"]/i';

            $sContent = preg_replace($sPattern,'src="'.$sNewSiteUrl.'/$1.$2"',$sContent);

        }

    }

    return $sContent;
}

// Add compatibility with Yoast WordPress SEO for Open Graph
add_filter('wpseo_opengraph_image', 'fasterimage_wpseo_opengraph_fix', 9999);
function fasterimage_wpseo_opengraph_fix($img) {
    $img = removeFasterImageDomain($img);

    return $img;
}

/* ***** Utils ***** */
function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function removeFasterImageDomain($sImageUrl) {

    if(!is_admin()) {
        $sEnabled = get_option('fasterimage_enabled');
        $sDomain = get_option('fasterimage_domain');
        $sChecked = get_option('fasterimage_account_valid');

        if ($sEnabled == '1' && strlen($sDomain) > 0 && $sChecked == '1') {
            //fasterImage is enabled and a domain is set

            $sSiteUrl = get_site_url();

            $aUrlInfo = parse_url($sSiteUrl);

            $sNewSiteUrl = str_replace($aUrlInfo['host'], $sDomain, $sSiteUrl);
            $iNewSiteUrlLength = strlen($sNewSiteUrl);

            if (strlen($sImageUrl) > $iNewSiteUrlLength && substr($sImageUrl, 0, $iNewSiteUrlLength) == $sNewSiteUrl) {
                return str_replace($sNewSiteUrl, $sSiteUrl, $sImageUrl);
            }

        }
    }

    return $sImageUrl;
}

/* ***** Plugin ***** Core */
if( is_admin() ) {
    include_once('fasterimage-settings.php');
    $my_settings_page = new fasterImageSettingsPage();
}

//Admin notices
add_action('admin_notices', 'fasterimage_admin_notices');
function fasterimage_admin_notices() {
    if ($aNotices = get_option('fasterimage_admin_notices')) {
        foreach ($aNotices as $sNotice) {
            switch($sNotice) {
                case 'install':
                    ?>
                    <div class='updated'>
                        <p>
                            <strong>Merci d'avoir installé fasterImage!</strong>
                            <br/>
                            Pour activer les optimisations, veuillez configurer les options dans la page <a href="<?php echo admin_url( 'options-general.php?page=fasterimage-settings'); ?>">Réglages > fasterImage</a>.</p>
                    </div>
                    <?php
                    break;
            }
        }
    }

    $sEnabled = get_option('fasterimage_enabled');
    $sDomain = get_option('fasterimage_domain');
    if($sEnabled == '1' && $sDomain == '') {
        ?>
        <div class='error'>
            <p>
                <strong>fasterImage : Erreur de configuration </strong>
                <br/>
                L'optimisation des images est activée mais aucun nom de domaine n'a été renseigné.
                <br/>
                Veuillez configurer les options dans la page <a href="<?php echo admin_url( 'options-general.php?page=fasterimage-settings'); ?>">Réglages > fasterImage</a>.</p>
        </div>
    <?php
    }
    else {
        $sChecked = get_option('fasterimage_account_valid');
        if($sEnabled == '1' && $sChecked == '0') {
            ?>
            <div class='error'>
                <p>
                    <strong>fasterImage : Erreur de configuration </strong>
                    <br/>
                    Le nom de domaine que vous avez renseigné <strong><?php echo $sDomain; ?></strong> n'est pas associé à un compte fasterImage, les images ne seront donc pas optimisées!
                    <br/>
                    Veuillez <a href="http://fasterimage.io/?utm_source=wordpress&utm_medium=plugin&utm_campaign=settings" target=\"_blank\">créer un compte gratuitement</a> ou ajouter un nouveau nom de domaine à votre compte, puis modifier les options dans la page <a href="<?php echo admin_url( 'options-general.php?page=fasterimage-settings'); ?>">Réglages > fasterImage</a>.</p>
            </div>
            <?php
        }
    }

}

add_action('init','fasterimage_init_check_account');
function fasterimage_init_check_account($bForceCheck = false) {
    $sChecked = get_option('fasterimage_account_valid');
    $iExpiration = get_option('fasterimage_account_valid_expiration');

    if($bForceCheck || $sChecked === false || $iExpiration === false || time() - $iExpiration > 60 * 60 * 24) { // One day expiration
        //Checked the account
        $sEnabled = get_option('fasterimage_enabled');
        $sDomain = get_option('fasterimage_domain');

        if ($sEnabled == '1' && $sDomain != '') {

            $aArgs = array(
                'timeout' => 10,
                'user-agent'  => 'WordPress' . $wp_version . '/fasterImage'. FASTERIMAGE_VERSION .'; ' . get_bloginfo( 'url' )
            );
            $aResponse = wp_remote_get( 'http://fasterimage.io/api/checkdomain/'.$sDomain, $aArgs );
            if(is_array($aResponse)) {
                if($aResponse['body'] == 'OK') {
                    update_option('fasterimage_account_valid', '1');
                    update_option('fasterimage_account_valid_expiration', time());

                    return true;
                }
            }
        }
        update_option('fasterimage_account_valid', '0');
        update_option('fasterimage_account_valid_expiration', time());

        return false;
    }
    else {
        //Check still valid
        if($sChecked == '1') {
            return true;
        }
        else {
            return false;
        }
    }
}

add_action('admin_init', 'fasterimage_admin_notice_install_remove');
function fasterimage_admin_notice_install_remove() {
    global $current_user;
    $user_id = $current_user->ID;
    /* If user clicks to ignore the notice, add that to their user meta */
    if ( isset($_GET['page']) && $_GET['page'] == 'fasterimage-settings' ) {
        $aNotices =  get_option('fasterimage_admin_notices', array());
        $aNewNotices = array();
        foreach($aNotices as $sNotice) {
            if($sNotice != 'install') {
                $aNewNotices[] = $sNotice;
            }
        }
        update_option('fasterimage_admin_notices', $aNewNotices);
    }
}

//Activation / Deactivation
register_activation_hook(__FILE__, 'fasterimage_activation');
function fasterimage_activation() {
    update_option('fasterimage_enabled', '0');
    update_option('fasterimage_account_valid', false);
    update_option('fasterimage_account_valid_expiration', time() - (60 * 24 * 2));

    $aNotices =  get_option('fasterimage_admin_notices', array());
    $aNotices[] = "install";
    update_option('fasterimage_admin_notices', $aNotices);

    update_option('fasterimage_domain',str_replace(array('http://','https://'),'',get_site_url()).".fasterimage.io");
}

register_deactivation_hook(__FILE__, 'fasterimage_deactivation');
function fasterimage_deactivation() {
    delete_option('fasterimage_enabled');
    delete_option('fasterimage_account_valid');
    delete_option('fasterimage_account_valid_expiration');
    delete_option('fasterimage_domain');
    delete_option('fasterimage_admin_notices');
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'fasterimage_settings_link' );
function fasterimage_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=fasterimage-settings">Réglages</a>';
    array_unshift($links, $settings_link);
    return $links;
}

?>