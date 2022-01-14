<?php

/**
* Plugin Name: Infoportal-api-search
* Description: This is a plugin which will take other APIs and search them for content. It will only redirect to an extern side, shuld it find content.
* Author: Laurenz Schindler, Daniel Waage
* Version: 1.0.0
*/

// $objectToSave = ["a link", "another link"]

/**
 * Get the links from the DB
 */
function infoportal_api_suche_prefix() {
    add_option("objectToSave", ["a link", "another link yet again"]);
}

/**
 * Remove links from the DB
 */
function infoportal_api_suche_postfix() {
    delete_option("objectToSave");
}

/**
 * CleanUp on Uninstall
 */
function infoportal_api_suche_ultima() {
    delete_option("objectToSave");
}

register_activation_hook(__FILE__, 'infoportal_api_suche_prefix');
register_deactivation_hook(__FILE__, 'infoportal_api_suche_postfix');
register_uninstall_hook(__FILE__, 'infoportal_api_suche_ultima');
