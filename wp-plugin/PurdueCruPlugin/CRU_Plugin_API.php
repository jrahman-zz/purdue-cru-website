<?php

require_once("CRU_Utils.php");
require_once("CRU_Small_Groups.php");
require_once("CRU_Messaging.php");
require_once("CRU_Target_Areas.php");

/**
 *
 * Class to provide methods to allow the theme to interface with the plugin
 *
 * @author Jason P. Rahman (jprahman93@gmail.com, rahmanj@purdue.edu)
 */
class CRU_Plugin_API {


    /**
     * Print a list of target area option tags
     */
    public static function target_area_options() {
        $target_areas = CRU_Target_Areas::get_target_areas();

        if ($target_areas !== NULL && is_array($target_areas)) {
            foreach ($target_areas as $target_area) { ?>
            <option value="<?php echo($target_area['area_id']); ?>"><?php echo($target_area['area_name']); ?></option>
       <?php}
    }

    /**
     * Get a list of the small groups for a given target area
     *
     * @return an associative array containing all the details for a given small group
     */
    public static function get_small_groups($area_id) {
        $small_groups = CRU_Small_Groups::get_small_groups_full($area_id);
        return $small_groups;
    }


    /**
     * Get a list of photos for the photo banner from Facebook
     *
     * @return array an array of associative arrays containing details for each photo
     */
    public static function get_facebook_photos() {
        
    }

    /**
     * Get the Facebook newsfeed for the Purdue CRU page
     */
    public static function get_facebook_newsfeed() {
        try {
            $config = CRU_Config::get_config("config.ini");
            $page_id = $config->facebook->cru_page;

            $app_secret = $config->facebook->appSecret;
            $app_id = $config->facebook->appId;

            $facebook_client = new CRU_Facebook_Client($app_id, $app_secret);
            $page = new CRU_Facebook_Object($page_id, $facebook_client);
            $posts = $page->connections->posts;

            return $posts;
        } catch (CRU_IllegalConfigFileException $e) {
            return NULL;
        } catch (CRU_Facebook_InitializationException $e) {
            return NULL;
        } catch (Exception $e) {
            return NULL;
        }
    }
}
?>
