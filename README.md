# content-importer-for-notion

## Description
Wordpress Plugin for displaying content from Notion using the Notion API.  This plugin assumes you have some knowledge of [Notion](https://notion.so/) and know how to install Wordpress plugins.

This plugin take a simple Notion Database and allows you to display the contents of that database in a Wordpress site.  Sample Notion Page Coming Soon

___

## How it Works
Using the Notion API, the list of pages and the contents of those pages are stored locally as a custom post type.  The custom post type is called notion_content.  The local database acts as a cache and does not require a Notion API call everytime a user visits the page the content is being used on.  The content is stored locally so an API to Notion is not required everytime a user visits the page the content is being used on.  

___

## Requirements

### Wordpress Website
[Wordpress](https://wordpress.org/)

### Notion Integration Token
You will need to setup a Notion 
[Notion API Integration](https://www.notion.so/my-integrations)

---

## Installation

1. Install plugin and activate plugin in Wordpress
2. Go to Notion Content -> Setup in the Wordpress admin.
3. Enter in the Notion API Key (aka Internal Integration Token)
4. Enter in the link to the Notion Database (Not ID.  The plugin will parse the URL). 


## Usage
1. Go to Notion Content and click on the "Refresh All Content" button.
3. Copy and Paste the shortcode to be used in your Wordpress Post or Page.


---

## Supported Notion Blocks
- Heading 1
- Heading 2
- Heading 3
- Bullet Lists
- Numbered List
- To Do
- Quote
- Callout
- Toggle
- Dividers
- Notion Simple Tables
- Images (Uploaded images directly to Wordpress Media Library)

---

## Custom Styles
You can add custom classes and custom CSS.  

---

## Notion Columns
Support for simple Notion columns.  Columns occur when you dragging and dropping blocks of content side by side in Notion. 

---

## Automatic Content Refresh
You can now set content to refresh automatically using WP-Cron.  Intervals can be set for 15 Minutes, 30 Minutes, 1 Hour, 6 Hours, 12 Hours, or once a day.  

---

## Coming Soon
- Add more instructions on how to setup API and page integration