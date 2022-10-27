# Readme
**--------------------------------------------------**
## Wp Resale
**Contributors:** wp-developers

**Wesite link:** https://www.zestgeek.com/

**Tags:** #resales-online, #resale-marbella

### Introduction
It is a property-selling plugin where users can search for property according to location, budget, etc. Users can see all the features of property uploaded by sellers. This plugin helps to get and store all the properties provided by the "https://resales-online.com" API.

### Description

A property-selling plugin with multilingual feature.It have 5 languages English, Spanish, French, German, and Hebrew. It has two types of filters normal and advanced filters. All the filter data is coming from the "https://resales-online.com" API. APIs are dynamically managed. 2 hooks are developed in this plugin which can be used in the site :- "resales_results" (The main hook to get/show all the data), and "resales_search" (Hook mainly needed to add in the home page to search a property.).

### Installation Steps

1.	Install real-estate plugin.

2.	Create a page with the slug name “list-property” and add shortcode with the following arguments:-

	[resales_results price_min=2000 price_max=1000000 price_step=5000 prop_box=1 loc_box=1] 

3.	Add “[resales_search price_min=2000 price_step=5000 prop_box=1 loc_box=1]” hook at home page.

4.	Create one more page for a single product and select the template “Single Property” with the slug name “single-property”.

5.	After activating the plugin need to add the API key. Add API key under the API settings menu in the admin dashboard.

6.	Use the following arguments in points 2 And 3

	“ price_min=“1000″ preferred price minimum
   	price_max=“1000000″ preferred price maximum
   	price_step=“10000″ preferred price step between price max & min
   	prop_box=“0″ hide property selection dropdown
   	loc_box=“0″ hide location selection dropdown
   	wiipagesize=“12″ set the number of properties to show on a page “

* Note:- (If no arguments passed then it will take the default values. Please make sure arguments passed in points 2 and 3 are correct.)

**Steps to add new language**

Step 1 
First, need to create .mo and .po file
Example: wp-resale-fr_FR.po for french, wp-resale-es_ES.po for spanish same as .mo file wp-resale-fr_FR.mo for french wp-resale-es_ES.mo for french

Step 2
Goto plugin location 
/wp-content/plugins/wp-resale/language/

Step 3 
Move the file to the mentioned locations

And you are good to go! 