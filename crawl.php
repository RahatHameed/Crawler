<?php
/**
 * Website: https://www.gladhoster.com
 * Acknowledge: Rahat Hameed (https://www.linkedin.com/in/rahathameed/)
 *
 * Authors:
 *   Rahat Hameed
 *
 */
set_time_limit(0);
require_once('../wp-load.php');
error_reporting(E_ALL);
include('simplehtmldom/simple_html_dom.php');

global $wpdb;
$WpCrawler = new WpCrawler();

class WpCrawler
{


    function __construct()
    {
        global $wpdb;

        $this->site_name = 'Tech Juice';
        $this->site_url = 'https://www.techjuice.pk';
        $this->limit = 10;// No of links to Fetch
        $this->debug_mode = true;// set it true indevelopment to check result.

        // Preparing to fetch Links and detail
        $crawl = array();
        $crawl['category']='Startups'; // Name of Category to be Imported
        $crawl['listing_url']='https://www.techjuice.pk/category/startups';
        $this->getUrls($crawl);


    }


    function getUrls($params){

        global $wpdb;

        $site_name      = $this->site_name;
        $site_url       = $this->site_url;
        $listing_url    = $params['listing_url'];
        $category       = $params['category'];
        $limit          = $this->limit;



        $content =$this->get_web_page( $listing_url);
        $html = str_get_html($content['content']);

        // find all link
        $int=1;
        $htmlgrid  = $html->find('div[class=content-inner] div[class=loop clearfix grid]',0);
        foreach($htmlgrid->find("div[class=row-fluid]") as $row)
        {

            $item = $row->find('div[class=post-story-wraper]] h2',0);
            $link =  $item->find('a',0)->href;
            // IF Link has No  prefix then add it
            $prefix_search = strpos($link, $site_url);
            if ($prefix_search===FALSE) {
                $link = $site_url.$link;
            }

            $params['link']=$link;

            if($this->debug_mode) {
                echo '<br><br><br><b>Row No:</b>: ' . $int;
                echo '<br><b>Post URL</b>: ' . $link;
                echo '<br> Prefix search : ' . $prefix_search;
            }


            //Check Link Exist
            $linksCount = $wpdb->get_var("select count(*) from " . $wpdb->prefix . "crawl_links where link='" . $link . "'");

            if ($this->debug_mode) {
                echo '<br> Exist Count : ' . $linksCount;
            }

            if ($linksCount == 0) {


                $wpdb->insert(
                    $wpdb->prefix . "crawl_links",
                    array(
                        'link' => $link,
                        'site_name' => $site_name,
                        'category' => $category,
                        'status' => 0
                    )
                );

                $link_id = $wpdb->insert_id;
                $params['link_id'] = $link_id;
                $this->getDetail($params);

            }

            if ($int == $limit) break;
            $int++;


        }// foreach
    }



    function getDetail($params){
        global $wpdb;

        $category       = $params['category'];
        $link           = $params['link'];
        $link_id        = $params['link_id'];
        $site_name      = $this->site_name;
        $content        = $this->get_web_page( $link);
        $html = str_get_html($content['content']);


        // wait for 10 seconds
        //sleep(10);
        if($html){

            $meta_description  = $html->find('head meta[name=description]',0);
            if($meta_description){
                $meta_description = $meta_description->getAttribute('content');
            }

            $post_title  = trim($html->find('div[class=top-title] h1',0)->plaintext);
            $post_date_container  = $html->find('div.authorship span.date]',0);
            $post_date = $post_date_container->find('meta[itemprop=datePublished]',0)->content;

            $formated_post_date='';
            if($post_date!=''){
                $formated_post_date = date('Y-m-d H:i:s.u',strtotime($post_date));
            }

            // remove useless content
            $post_ads_div = $html->find('p#post_cust_tags',0);
            $post_ads_div->innertext='';
            $html->save();


            $paragraph='';
            foreach($html->find('div[class=the-content clearfix] p') as $para){
                $paragraph .= "<p>".$para->innertext."</p>";
            }

            $post_content_selector = $html->find('div[class=the-content clearfix]',0);
            if($post_content_selector){
                $post_content = $post_content_selector->innertext;
                //remove particular content, div, class, study php simple dom parser guide
                //https://simplehtmldom.sourceforge.io/manual.htm
            }



            //remove tags without content
            $remove_tags = array("a");

            foreach($remove_tags as $tag)
            {
                $paragraph = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/", '', $paragraph);
            }



            $featured_image  = $html->find('div.featured-image-wrapper div.featured-image-inner',0);
            if($featured_image){
                $featured_img_url = $featured_image->find('img',0)->src;
                if($featured_img_url==''){
                    $featured_image2 = $html->find('div[class=content-panel content-container] div[class=the-content clearfix]', 0);
                    if($featured_image2){
                        $featured_img_url = $featured_image2->find('img', 0)->src;
                    }
                }
            }


            $post_type='image';


            if($this->debug_mode) {

                echo '<br><br><b>Title:</b><br>';
                echo $post_title;
                echo '<br><b>Post Date:</b><br>';
                echo $post_date;
                echo '<br><b>post content:</b> <br>';
                echo $post_content;
                echo '<br><b>featured_img_src: </b><br>';
                echo $featured_img_url;
                echo '<hr>';

            }


            if($post_title!='' && $post_content!='' && $featured_img_url!=''){


                //check If listing does not exits
                $listingCount = $wpdb->get_var("select count(*) from " . $wpdb->prefix . "crawl_listing where link='" . $link . "'");
                if($listingCount==0) {

                    $wpdb->insert(
                        $wpdb->prefix . "crawl_listing",
                        array(
                            'link_id' => $link_id,
                            'post_title' => $post_title,
                            'post_date' => $formated_post_date,
                            'post_content' => $post_content,
                            'meta_description' => $meta_description,
                            'featured_img_url' => $featured_img_url,
                            'post_type' => $post_type,
                            'link' => $link,
                            'category' => $category,
                            'site_name' => $site_name,
                            'status'=>0
                        )
                    );

                    // Update crawl Links status
                    $wpdb->update(
                        $wpdb->prefix . "crawl_links",
                        array(
                            'status' => 1
                        ),
                        array('id' => $link_id)
                    );

                }//if $listingCount
            }

        }


    }


    function get_web_page( $url )
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }
}//class