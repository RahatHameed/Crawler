# Wordpress Posts Crawler
![Screenshot](images/wp-crawler.jpg)<br>
This crawler class will fetch urls, their details into custom tables and create wordpress post using collected information from any website. Place this directory inside root directory of your wordpress project so what wp-load.php is available outside our directory.
# Manual Setup
First of all create folder 'Crawler' inside your theme root directory.<br>
Take Pull from this repository.



# Simple html dom
Main crawler folder contains following directories and files.<br>
<b>Directory:</b> simplehtmldom<br>
This folder contains simple htlm dom parser library, which is included inside our crawl file. Refer to library online documentation for further help.<br>
<b>Manual:</b>
https://simplehtmldom.sourceforge.io/manual.htm


# Crawl Class
File: <b>crawl.php</b><br>
This file contains our main file which will crawl target website links and their details inside database tables wp_crawl_links and wp_crawl_listing


Functions
1. <b>getUrls()</b><br>
This function will fetch urls of any category listing page, and insert into database table wp_crawl_links along its category name.<br>


2. <b>getDetail()</b><br>
This function will crawl a post detail page, and fetch its post title, date, description, image and insert into database table wp_crawl_listing.

# Wp Importer Class
File: <b>WpImporter.php</b><br>
![Screenshot](images/wordpress-importer.png)
The functions in this class will fetch data stores in our table wp_crawl_listing and create a WordPress post along its meta database, featured image and create and assign category to it.<br>


# Database Tables
Instructions: crawl.sql file contains the structure for both the custom tables, import this file into your WordPress database, if your database prefix is wp_ then no need to change it, otherwise change the prefix of both these tables.<br>

# Writing Cron Job
![Screenshot](images/wp-importer.png)
Once you fetch data from your target website website, write cron job on server for both the crawl.php and WpImorter.php file to execute it and import posts int your wordpress websbite automatically.
