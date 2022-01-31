<?php

/**
 * Plugin Name: Infoportal-api-search
 * Description: This is a plugin which will take other APIs and search them for content. It will only redirect to an extern side, shuld it find content.
 * Author: Laurenz Schindler, Daniel Waage
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Encapsulating all functions is a good way to go
class InfoportalApiSearch
{
    // Registering the function
    function __construct()
    {
        add_action('admin_menu', array($this, 'settingsLink'));
        add_action('admin_init', array($this, 'settings'));
    }


    function settings() {
        add_settings_section('al_first_section', null, null, 'hit-that-lick');

        add_settings_field('api_links', 'Choose your API', array($this, 'chooserHTML'), 'hit-that-lick', 'al_first_section');
        register_setting('apiPluginGroup', 'al_links', array('sanatize_callback' => 'sanatize_text_field', 'default' => '0'));

        add_settings_field('api_link_single', 'Link to REST-API', array($this, 'linkHTML'), 'hit-that-lick', 'al_first_section');
        register_setting('apiPluginGroup', 'al_link_single', array('sanatize_callback' => 'sanatize_text_field', 'default' => 'linkle'));

        add_settings_field('api_link_token', 'Generated Token', array($this, 'tokenHTML'), 'hit-that-lick', 'al_first_section');
        register_setting('apiPluginGroup', 'al_link_token', array('sanatize_callback' => 'sanatize_text_field', 'default' => 'tonkle'));

        add_settings_field('api_link_search', 'Search', array($this, 'searchHTML'), 'hit-that-lick', 'al_first_section');
        register_setting('apiPluginGroup', 'al_link_search', array('sanatize_callback' => 'sanatize_text_field', 'default' => 'dunkle'));
    }

    function chooserHTML() { ?>
        <select name="al_links">
            <option value="0" <?php selected(get_option('al_links'), '0') ?>>Moodle API</option>
            <option value="1" <?php selected(get_option('al_links'), '1') ?>>Wordpress API</option>
        </select>
    <?php }

    function linkHTML() {?> 
        <input type="text" name="al_link_single" value="<?php echo esc_attr(get_option('al_link_single')) ?>">
    <?php }

    function tokenHTML() {?> 
        <input type="text" name="al_link_token" value="<?php echo esc_attr(get_option('al_link_token')) ?>">
    <?php }

    function searchHTML() {?> 
        <input type="text" name="al_link_search" value="<?php echo esc_attr(get_option('al_link_search')) ?>">
    <?php }

    // Registering the Settings-Link
    function settingsLink()
    {
        add_options_page('Devious Lick', 'Licks', 'manage_options', 'hit-that-lick', array($this, 'sickFunctionHTML'));
    }

    function simpleHTML() { ?>
        <div< class="wrap">
            <h1>API Search Settings</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_fields('apiPluginGroup');
                    do_settings_sections('hit-that-lick');
                    submit_button();
                ?>
            </form>
        </class>
    <?php }

    // Whatever happens here happens :)
    function sickFunctionHTML()
    {
        // $url = 'https://dev09.oncampus-server.de/webservice/rest/';
        $url = get_option('al_link_single');

        // $testerToken = '6295a85a88bffcfe147c85c3a28beb96';
        $testerToken = get_option('al_link_token');

        $wsFunction = 'core_course_search_courses';
        $moodlewsRestFormat = 'json';
        $criteriaName = 'search';
        $criteriaValue = get_option('al_link_search');

        // For a normal Course-Search query
        $restCall = $url
            . 'server.php?wstoken=' . $testerToken
            . '&wsfunction=' . $wsFunction
            . '&moodlewsrestformat=' . $moodlewsRestFormat
            . '&criterianame=' . $criteriaName
            . '&criteriavalue=' . $criteriaValue;

        $response = file_get_contents($restCall);
        $htmlStripped = strip_tags($response);
        $jsonResponse = json_decode($htmlStripped, true);
        /*
        if ($htmlStripped) {
            print_r($htmlStripped);
        }
        */

        
        
        if ($jsonResponse) {
            foreach($jsonResponse['courses'] as $course) {
                echo "<br>";
                print_r($course['fullname']);
                echo "<br>";
                print_r($course['summary']);
                echo "<br>";
            }
        }
        
?>
        <div< class="wrap">
            <h1>API Search Settings</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_fields('apiPluginGroup');
                    do_settings_sections('hit-that-lick');
                    submit_button();
                ?>
            </form>
        </class>
<?php }
}

$infoportalApiSearch = new InfoportalApiSearch();
