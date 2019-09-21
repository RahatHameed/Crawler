-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2019 at 11:14 AM
-- Server version: 5.7.14
-- PHP Version: 7.0.10


CREATE TABLE `wp_crawl_links` (
  `id` int(11) NOT NULL,
  `link` varchar(250) NOT NULL,
  `site_name` varchar(100) DEFAULT NULL,
  `post_type` varchar(20) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_desciption` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_crawl_listing`
--

CREATE TABLE `wp_crawl_listing` (
  `list_id` int(11) NOT NULL,
  `link_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `post_title` varchar(250) DEFAULT NULL,
  `meta_description` text,
  `post_content` text,
  `post_date` datetime DEFAULT NULL,
  `featured_img_url` text,
  `featured_video_url` text,
  `featured_audio_url` text,
  `post_type` varchar(10) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `site_name` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp_crawl_links`
--
ALTER TABLE `wp_crawl_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wp_crawl_listing`
--
ALTER TABLE `wp_crawl_listing`
  ADD PRIMARY KEY (`list_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp_crawl_links`
--
ALTER TABLE `wp_crawl_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wp_crawl_listing`
--
ALTER TABLE `wp_crawl_listing`
  MODIFY `list_id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
