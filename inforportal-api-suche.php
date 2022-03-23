<?php

/**
 * Plugin Name: Infoportal-api-search
 * Description: This is a plugin which will take other APIs and search them for content. It will only redirect to an extern side, shuld it find content.
 * Author: Laurenz Schindler, Daniel Waage
 * Version: 1.0.0
 */


 /*
TODO:
- merging queries
- make links in search query clickable
- add Wordpress/GraphQL Queries
- Multiple APIs (maybe save them as an Array of API-Accesstoken Objects or something)
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
        // add_action('pre_get_posts', array($this, 'spoofMainQuery')); // for getting a post into the DB
        // add_action('found_posts', array($this, 'spoofMainQuery')); // adding the posts to the query
        add_action('template_redirect', array($this, 'spoofMainQuery'));
    }

    function spoofMainQuery()
    {
        if (is_search()) {
            global $wp, $wp_query;

            // Getting a response from another API
            $url = get_option('al_link_single');

            $testerToken = get_option('al_link_token');

            $wsFunction = 'core_course_search_courses';
            $moodlewsRestFormat = 'json';
            $criteriaName = 'search';
            $criteriaValue = get_search_query();

            // For a normal Course-Search "query" (Slap that boi together)
            $restCall = $url
                . 'server.php?wstoken=' . $testerToken
                . '&wsfunction=' . $wsFunction
                . '&moodlewsrestformat=' . $moodlewsRestFormat
                . '&criterianame=' . $criteriaName
                . '&criteriavalue=' . $criteriaValue;

            $response = file_get_contents($restCall); // The actual call
            $htmlStripped = strip_tags($response); // formatting
            // modifying the string, as the url is a problem for jsondecode()
            $firstChunk = stristr($htmlStripped, 'fileurl', true);
            $firstChunk = substr($firstChunk, 0, -2);
            $secondChunk = substr($htmlStripped, strlen($firstChunk) + 12);
            $secondChunk = stristr($secondChunk, "\"");
            $secondChunk = substr($secondChunk, 1);
            // putting the string back together
            $htmlFiltered = $firstChunk . $secondChunk;
            $callResponseJSON = array_values(json_decode($htmlFiltered, true)); // formatting



            /* 
            [0] => Array (
                [id] => 3
                [fullname] => All about your Dwarven Moods
                [displayname] => All about your Dwarven Moods
                [shortname] => Moods
                [categoryid] => 2
                [categoryname] => Overview
                    [sortorder] => 10001
                [summary] => Youd be surprised, how moody these little shits get over time… Or just due to sunlight.
                [summaryformat] => 1
                [summaryfiles] => Array()
                [overviewfiles] => Array(
                    [0] => Array(
                    [filename] => 1200px-Jean-Léon_Gérôme_-_Diogenes_-_Walters_37131.jpg
                    [filepath] => /
                    [filesize] => 277187
                    [timemodified] => 1643626167
                    [mimetype] => image/jpeg
                    )
                )
                [showactivitydates] => 1
                [showcompletionconditions] => 1
                [contacts] => Array(
                    [0] => Array(
                        [id] => 2
                        [fullname] => Daniel Waage
                    )
                )
                [enrollmentmethods] => Array(
                    [0] => manual
                )
            )

            [1] => Array(
                [id] => 2
                [fullname] => The Visual Style
                [displayname] => The Visual Style
                [shortname] => Graphics
                [categoryid] => 2
                [categoryname] => Overview
                [sortorder] => 10002
                [summary] => A quick explanation on the graphic style of Dwarf Fortress
                [summaryformat] => 1
                [summaryfiles] => Array()
                [overviewfiles] => Array(
                    [0] => Array(
                        [filename] => DF-Logo-NT.png
                        [filepath] => /
                        [filesize] => 4901
                        [fileurl] => https://dev09.oncampus-server.de/webservice/pluginfile.php/17/course/overviewfiles/DF-Logo-NT.png
                        [timemodified] => 1641559575
                        [mimetype] => image/png
                    )
                )
                [showactivitydates] => 1
                [showcompletionconditions] => 1
                [contacts] => Array(
                [0] => Array(
                        [id] => 2
                        [fullname] => Daniel Waage
                    )
                )
                [enrollmentmethods] => Array(
                    [0] => manual
                )
            )
            */

            $post_array = [];
            
            foreach ($callResponseJSON[1] as &$valueArray) {
                $debugID = -99 - rand(1, 999);
                $post = new stdClass();
                $post->ID = $debugID;
                $post->post_author = 1;
                $post->post_date = current_time('mysql');
                $post->post_date_gmt = current_time('mysql', 1);
                $post->post_title = $valueArray['fullname'] . "";
                $post->post_content = $valueArray['summary'] . " ID: " . $debugID; // $callResponseJSON['summary'];
                $post->post_status = 'publish';
                $post->comment_status = 'closed';
                $post->ping_status = 'closed';
                $post->post_name = 'fake-page-' . rand(1, 99999); // append random number to avoid clash
                $post->post_type = 'page';
                $post->filter = 'raw'; // important!
                $post->guid = 'google.com';

                $wp_post = new WP_Post($post);
                wp_cache_add($debugID, $wp_post, 'posts');
                array_push($post_array, $wp_post);
            }

            /*
            print("</br>REST-Abfrage:</br>");
            print_r($post_array);
            print("</br></br>Wordpress Query Posts:</br>");
*/
            
            /*
            $objectedVars = get_object_vars($wp_query);
            foreach($objectedVars["posts"] as &$help) {
                print_r($help);
                print("</br>   ");
            }
            */






            
            // Creating a fake-query
            // for now this holds only one post as i did not know how to implement multiple
            $wp_queryT = new WP_Query();
            $wp_queryT->post = $wp_post;
            $wp_queryT->posts = array($wp_post);
            $wp_queryT->queried_object = $wp_post;
            $wp_queryT->queried_object_id = $debugID;
            //$wp_queryT->found_posts = 1;
            //$wp_queryT->post_count = 1;
            //$wp_queryT->max_num_pages = 1;
            $wp_queryT->is_page = true;
            $wp_queryT->is_singular = true;
            $wp_queryT->is_single = false;
            $wp_queryT->is_attachment = false;
            $wp_queryT->is_category = false;
            $wp_queryT->is_tag = false;
            $wp_queryT->is_tax = false;
            $wp_queryT->is_author = false;
            $wp_queryT->is_date = false;
            $wp_queryT->is_year = false;
            $wp_queryT->is_month = false;
            $wp_queryT->is_day = false;
            $wp_queryT->is_time = false;
            $wp_queryT->is_search = false;
            $wp_queryT->is_feed = false;
            $wp_queryT->is_comment_feed = false;
            $wp_queryT->is_trackback = false;
            $wp_queryT->is_home = false;
            $wp_queryT->is_embed = false;
            $wp_queryT->is_404 = false;
            $wp_queryT->is_paged = false;
            $wp_queryT->is_admin = false;
            $wp_queryT->is_preview = false;
            $wp_queryT->is_robots = false;
            $wp_queryT->is_posts_page = false;
            $wp_queryT->is_post_type_archive = false;
            
            
            
            $wp_query->posts = array_merge($wp_query->posts, $post_array);
            $wp_query->post_count = $wp_query->post_count + count($post_array);
            $wp_query->found_posts = $wp_query->post_count;
            

            //print_r($wp_query);

            $GLOBALS['wp_query'] = $wp_queryT;
            $wp->register_globals();
        }
    }


    function settings()
    {
        add_settings_section('al_first_section', null, null, 'hit-that-lick');

        add_settings_field('api_links', 'Choose your API', array($this, 'chooserHTML'), 'hit-that-lick', 'al_first_section');
        register_setting('apiPluginGroup', 'al_links', array('sanatize_callback' => 'sanatize_text_field', 'default' => '0'));

        add_settings_field('api_link_single', 'Link to REST-API', array($this, 'linkHTML'), 'hit-that-lick', 'al_first_section');
        register_setting('apiPluginGroup', 'al_link_single', array('sanatize_callback' => array($this, 'sanatizeLink'), 'default' => 'linkle'));

        add_settings_field('api_link_token', 'Generated Token', array($this, 'tokenHTML'), 'hit-that-lick', 'al_first_section');
        register_setting('apiPluginGroup', 'al_link_token', array('sanatize_callback' => 'sanatize_text_field', 'default' => 'tonkle'));

        add_settings_field('api_link_search', 'Search', array($this, 'searchHTML'), 'hit-that-lick', 'al_first_section');
        register_setting('apiPluginGroup', 'al_link_search', array('sanatize_callback' => 'sanatize_text_field', 'default' => 'dunkle'));
    }

    function sanatizeLink($input)
    {
        if ($input == '') {
            add_settings_error('al_link_single', 'al_link_single_emptyError', 'Link must be not empty to reach an API.');
            return '';
        } else if (strpos($input, 'rest') !== false) {
            add_settings_error('al_link_single', 'al_link_single_urlError', 'Your link must be a valid link to a rest API.');
            return '';
        }
        return $input;
    }

    function chooserHTML()
    { ?>
        <select name="al_links">
            <option value="0" <?php selected(get_option('al_links'), '0') ?>>Moodle API</option>
            <option value="1" <?php selected(get_option('al_links'), '1') ?>>Wordpress API</option>
            <option value="2" <?php selected(get_option('al_links'), '2') ?>>GraphQL API</option>
        </select>
    <?php }

    function linkHTML()
    { ?>
        <input type="text" name="al_link_single" value="<?php echo esc_attr(get_option('al_link_single')) ?>">
    <?php }

    function tokenHTML()
    { ?>
        <input type="text" name="al_link_token" value="<?php echo esc_attr(get_option('al_link_token')) ?>">
    <?php }

    function searchHTML()
    { ?>
        <input type="text" name="al_link_search" value="<?php echo esc_attr(get_option('al_link_search')) ?>">
    <?php }

    // Registering the Settings-Link
    function settingsLink()
    {
        add_options_page('Devious Lick', 'Licks', 'manage_options', 'hit-that-lick', array($this, 'sickFunctionHTML'));
    }

    function simpleHTML()
    { ?>
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
        // $url = 'https://dev09.oncampus-server.de/webservice/rest/'; // hardcoded
        $url = get_option('al_link_single');

        // $testerToken = '6295a85a88bffcfe147c85c3a28beb96'; // hardcoded
        $testerToken = get_option('al_link_token');

        $wsFunction = 'core_course_search_courses'; // Rest-Call-Header (?)
        $moodlewsRestFormat = 'json';
        $criteriaName = 'search'; // Function
        $criteriaValue = get_option('al_link_search'); // What-To-Search-For

        // For a normal Course-Search "query" (Slap that boi together)
        $restCall = $url
            . 'server.php?wstoken=' . $testerToken
            . '&wsfunction=' . $wsFunction
            . '&moodlewsrestformat=' . $moodlewsRestFormat
            . '&criterianame=' . $criteriaName
            . '&criteriavalue=' . $criteriaValue;

        $response = file_get_contents($restCall); // The actual call
        $htmlStripped = strip_tags($response); // formatting
        $jsonResponse = json_decode($htmlStripped, true); // formatting

        if ($htmlStripped) {
            print_r($htmlStripped);
        }





        if ($jsonResponse) {
            foreach ($jsonResponse['courses'] as $course) {
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
