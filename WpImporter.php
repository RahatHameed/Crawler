<?php
set_time_limit(0);
/**
 * Website: https://www.gladhoster.com
 * Acknowledge: Rahat Hameed (https://www.linkedin.com/in/rahathameed/)
 *
 * Authors:
 *   Rahat Hameed
 *
 * Version 1.0
 */
/**
 * Include the wordpress core
 */
require_once( '../wp-load.php' );
require_once( '../wp-admin/includes/taxonomy.php' );


global $wpdb;
$WpImporter = new WpImporter();
class WpImporter
{

    public function __construct() {

        $this->limit = 5;
        $this->placeholder_img = 150;// Images Post ID of Placeholder
        $this->debug_mode = false;

        $this->createAPost();
    }


    
    protected function createAPost(){

        global $wpdb;
        $listing_query = "SELECT * from  ".$wpdb->prefix."crawl_listing where status=0 order by post_date limit ".$this->limit."";
        $listing_data = $wpdb->get_results($listing_query);

        if($listing_data) {


            foreach ($listing_data as $listing) {


                $list_id = $listing->list_id;
                $post_title = $listing->post_title;
                $post_content = $listing->post_content;
                $category = $listing->category;

                $post_author = rand(3,16);

                $my_post = array(
                    'post_title' => wp_strip_all_tags($post_title),
                    'post_content' => $post_content,
                    'post_status' => 'publish',
                    'post_author' => $post_author,
                    'post_category' => array($category)
                );

                // Create a new post
                $post_id = wp_insert_post($my_post);


                if($this->debug_mode){
                    echo '<br>post_title: ' . $post_title;
                    echo '<br>post_id: ' . $post_id;

                    $wpdb->show_errors();
                    $wpdb->print_error();
                }



                if($post_id){

                    // Add Post Meta
                    $this->addPostMetaData($post_id, $listing);

                    //Add Featured Image
                    $this->addFeatureImage($post_id, $listing);

                    $wpdb->update(
                        $wpdb->prefix . "crawl_listing",
                        array(
                            'status' => 1,
                            'post_id' => $post_id,
                        ),
                        array('list_id' => $list_id)
                    );

                }


            }//foreach

        }//if


    }

    protected function addPostMetaData($post_id, $listing){
    if ( $post_id && ! is_wp_error( $post_id ) ) {

        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
        $category = $listing->category;
        $site_name= $listing->site_name;
        $post_type= $listing->post_type;
        $post_date= $listing->post_date;
        $featured_img_url= $listing->featured_img_url;
        $featured_video_url= $listing->featured_video_url;
        $featured_audio_url= $listing->featured_audio_url;
        $link= $listing->link;
        $post_views_count = rand(1000,3000);


        $theme_settings = array();
        $theme_settings['td_source']= $listing->site_name;
        $theme_settings['td_source_url']= $listing->link;



        //if Post is video
        if($post_type=='video'){
            update_post_meta($post_id, 'td_last_set_video', $featured_video_url);
            $theme_settings['td_post_template']= 'single_template_9';

            $td_post_video = array('td_video'=>$featured_video_url);
            update_post_meta($post_id,'td_post_video',$td_post_video);
        }elseif($post_type=='audio'){
            $td_post_audio = array('td_audio'=>$featured_audio_url);
            update_post_meta($post_id,'td_post_audio',$td_post_audio);
        }


        // Add post meta
        update_post_meta($post_id, 'td_demo_content', 1);
        update_post_meta($post_id, 'post_views_count', $post_views_count);
        // Add theme Settings
        update_post_meta($post_id,'td_post_theme_settings',$theme_settings);

        // Add Category Data
        $cat_id  = get_cat_ID( $category );

        if($cat_id == 0){
            $new_cat_id  = wp_create_category( $category);// wp_create_category( $brand_name );

            if($new_cat_id==0){
                $catid = 1;
            }else{
                $catid = $new_cat_id;
            }
        }else{
            $catid = $cat_id;
        }

        $post_categories = array($catid);
        wp_set_post_terms( $post_id, $post_categories, 'category',true );


        if($post_date=='' || $post_date=='0000-00-00 00:00:00'){
            $post_date = current_time( 'mysql' );
        }

        wp_update_post(
            array (
                'ID'            => $post_id, // ID of the post to update
                'post_date'     => $post_date,
                'post_date_gmt' => get_gmt_from_date( $post_date )
            )
        );

        } // if post is inserted 

    } // Add post meta data

// Add Feature Image

    protected function addFeatureImage($post_id, $listing)
    {


        $post_type = $listing->post_type;
        $img_url = $listing->featured_img_url;
        if ($img_url != '') {

            $filetype = wp_check_filetype(basename($img_url), null);
            // Add Featured Image to Post
            $image_url = $img_url;//'http://www.geotauaisay.com/wp-content/uploads/2016/09/14315996_1339649252730823_396816470_o-300x160.jpg'; // Define the image URL here
            $current_time = current_time('timestamp');
            $image_name = $current_time . '.' . $filetype['ext'];
            $upload_dir = wp_upload_dir(); // Set upload folder

            if (empty($image_url)) return;

            $image_data = file_get_contents($image_url); // Get image data
            $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
            $filename = basename($unique_file_name); // Create image file name

            // Check folder permission and define file location
            if (wp_mkdir_p($upload_dir['path'])) {
                $file = $upload_dir['path'] . '/' . $filename;
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;
            }

            // Create the image  file on the server
            file_put_contents($file, $image_data);

            // Check image file type
            $wp_filetype = wp_check_filetype($filename, null);

            // Set attachment data
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            // Create the attachment
            $attach_id = wp_insert_attachment($attachment, $file, $post_id);

            // Include image.php
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            // Define attachment metadata
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);

            // Assign metadata to attachment
            wp_update_attachment_metadata($attach_id, $attach_data);
            // And finally assign featured image to post


            $attachment_array = wp_get_attachment_image_src($attach_id);
            //echo '<pre>';print_r($attachment_array);echo '</pre>';
            $attach_thumb_width = $attachment_array[1]; // thumbnail's width

            if ($attach_thumb_width > 10) {
                set_post_thumbnail($post_id, $attach_id);
            } else {
                // Placeholder Incase of No Image
                set_post_thumbnail($post_id, $this->placeholder_img);
            }

            // exit;
        }else{
            // Placeholder Incase of No Image
            set_post_thumbnail($post_id, $this->placeholder_img);
        }

    }// Image check

}
