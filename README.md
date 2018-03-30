Description
===============

This MediaWiki extension add fonctionnality for a user to link pages to his account. For instance 'favorite pages' or 'pages I like'

Installation
===============

1. clone UsersPagesLinks into the 'extensions' directory of your mediawiki installation
2. add the folling Line to your LocalSettings.php file :
> require_once("$IP/extensions/UsersPagesLinks/UsersPagesLinks.php");
3. run php maintenance/update.php 

Example
===============
Coming soon on http://en.wikifab.org


MediaWiki Versions
===============
This extension has been tested on MediaWiki version 1.27.1

Semantic Extra Special Properties
===============
To be able to use 'favorites' and 'I dit it' as filters, two properties have been added to SESP.

To use them, use these labels :

Favorites

I did it
