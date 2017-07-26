<?php
class fasterImageSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        $sPage = add_options_page(
            'Paramètres de fasterImage',
            'fasterImage',
            'manage_options',
            'fasterimage-settings',
            array( $this, 'create_admin_page' )
        );

        add_action('load-'.$sPage,'fasterimage_settings_save');

        function fasterimage_settings_save()
        {
            if(isset($_GET['settings-updated']) && $_GET['settings-updated'])
            {
                fasterimage_init_check_account(true);
            }
        }

    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options['fasterimage_enabled'] = get_option( 'fasterimage_enabled' );
        $this->options['fasterimage_domain'] = get_option( 'fasterimage_domain' );

        ?>
        <style>
            .bloc {
                background-color:#fff;
                padding:20px 16px;
                border:1px solid #ddd;
            }
            h1 {
                color:#2c3e50;
                font-size:1.4em;
            }
            h2 {
                color:#2c3e50;
                font-size:1.2em;
                font-weight:bold
            }
            .link-subscribe {
                color:#df691a;
            }
            .link-subscribe:hover {
                font-weight: bold;
                color:#df691a;
            }
        </style>

        <div class="wrap">

            <div class="bloc" style="text-align:center;">
                <a href="http://fasterimage.io/?utm_source=wordpress&utm_medium=plugin&utm_campaign=header" target="_blank"><img src="http://fasterimage.io.fasterimage.io/img/logo.png" /></a>
                <h1>Optimisez vos images & Accélérez votre site Web !</h1>
                <em>Plugin WordPress - version <?php echo FASTERIMAGE_VERSION; ?></em>
            </div>

            <div class="bloc" style="margin:20px 0;">
                <h2>Paramètres :</h2>
                <form method="post" action="options.php">
                    <?php
                    // This prints out all hidden setting fields
                    settings_fields( 'fasterimage_settings_group' );
                    do_settings_sections( 'fasterimage-settings' );
                    submit_button();
                    ?>
                </form>
            </div>

            <div style="text-align:center;margin:10px 0;"><a class="link-subscribe" href="http://fasterimage.io/contact?utm_source=wordpress&utm_medium=plugin&utm_campaign=footer" target="_blank">Aide / Support</a> - <a class="link-subscribe" href="mailto:contact@fasterimage.io" target="_blank">contact@fasterimage.io</a></div>

        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'fasterimage_settings_group', // Option group
            'fasterimage_enabled', // Option name
            array( $this, 'sanitize_enabled' ) // Sanitize
        );

        register_setting(
            'fasterimage_settings_group', // Option group
            'fasterimage_domain', // Option name
            array( $this, 'sanitize_domain' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            null, // Callback
            'fasterimage-settings' // Page
        );

        add_settings_field(
            'fasterimage_enabled', // ID
            'Activé', // Title
            array( $this, 'enabled_callback' ), // Callback
            'fasterimage-settings', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'fasterimage_domain',
            'Nom de domaine',
            array( $this, 'domain_callback' ),
            'fasterimage-settings',
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_enabled( $input )
    {
        if( in_array($input,array('0','1')) )
            return $input;
        else
            return '0';
    }

    public function sanitize_domain( $input )
    {
        return $input;
    }

    /**
     * Print the Section text
     */
    /*public function print_section_info()
    {
        print 'Paramètres :';
    }*/

    /**
     * Get the settings option array and print one of its values
     */
    public function enabled_callback()
    {
        printf(
            '<input type="radio" id="enabled_1" name="fasterimage_enabled" value="1" %s /> <label for="enabled_1">Oui</label>',
            isset( $this->options['fasterimage_enabled'] ) && $this->options['fasterimage_enabled'] == "1" ? 'checked="checked"' : ''
        );

        printf(
            ' &nbsp; &nbsp; <input type="radio" id="enabled_0" name="fasterimage_enabled" value="0" %s /> <label for="enabled_0">Non </label>',
            isset( $this->options['fasterimage_enabled'] ) && $this->options['fasterimage_enabled'] == "0" ? 'checked="checked"' : ''
        );

        echo "<p><em>Une fois activé, les images des articles et pages seront optimisées par fasterImage</em></p>";
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function domain_callback()
    {
        printf(
            '<input type="text" id="domain" name="fasterimage_domain" value="%s" style="width:400px" />',
            isset( $this->options['fasterimage_domain'] ) ? esc_attr( $this->options['fasterimage_domain']) : ''
        );

        echo "<p><em>Utilisez le nom de domaine fourni lors de votre inscription. Ce sera par exemple : <strong>".str_replace(array('http://','https://'),'',get_site_url()).".fasterimage.io</strong></em></p>";

        echo "<p><a class=\"link-subscribe\" href=\"http://fasterimage.io/?utm_source=wordpress&utm_medium=plugin&utm_campaign=settings\" target=\"_blank\">Créez un compte gratuitement</a> pour optimiser vos images !</p>";
    }
}
?>