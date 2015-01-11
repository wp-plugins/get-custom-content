=== GET Custom Content ===
Contributors: bgentry 
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MRKES4
Tags: custom content, query variables, urls
Requires at least: 3.0
Tested up to: 3.7.1
Stable tag: 1.0

Create dynamic pages using parameters in the url, such as example.com/my-post?username=Bob

== Description ==

This plugin allows you to create custom content that appears in your WordPress posts depending on query variables in the URL. For example, appending \"?customer=yes\" to your URL can make the sidebar display the phrase \"We love our customers!\" while \"?customer=notyet\" can make the sidebar display \"Become our customer today!\"

The plugin includes a shortcode for insterting custom content into a page or post, as well as a widget for adding custom content to sidebars.

How to use:
After installing the plugin, you will find "GET Custom Content" in your dashboard menu.
There are just a few steps:

1. Create a Variable.
To create a variable, click "Custom Content Variables" under the "GET Custom Content" section of the Dashboard menu. Type a name for your variable, then click 'add new variable.'

2. Create values for the variable
Click "New Value" under the "GET Custom Content" section of the Dashboard menu. Type the name of your value in the post title field, and type some content. Then select the variable to which this value belongs in the "GET Custom Content Variables" box on the editor screen.

If you want this particular value to be the default value, and therefore be shown when the variable is not defined in the URL, click the check box in the "Make this the Default?" box on the editor screen. It will appear if the variable is not defined in the URL (such as www.example.com) or when the variable is defined but the value does not exist (such as www.example.com?variable=nonexistent)

It's up to you to make sure you don't set up multiple values to be the default. If more than one value is set as the default, then whichever one is first when WordPress asks for the default will be shown as the default.

3. Use the widget or shortcode to ask the site to load the custom content
There are two ways to display custom content. One is to use the GET Custom Content widget to drop it in the sidebar. The second method allows you to load custom content in a page's content using the shortcode, format [bg_gcc variable="variableName"]

4. Create a URL. Just take a normal URL for your post or page, and add ?variablename=valuename and give this URL to people whom you would like to see your custom content.

Detailed instructions are here: http://bryangentry.us/get-custom-content-wordpress-plugin/

== Frequently Asked Questions ==

= How can I override the content for a specific variable / value combination? =
If you have created a variable and some values for it, you can override the default content using the shortcode.

To do this, after you enter the shortcode [bg_gcc variable="variablename"], type [/bg_gcc]. Between these codes, type values like this:

-value-valuename
Here is content to override the pre-defined content!
-value-valuename

New in version 1.1, you can override the content for a variable/value combination in the widget using the same text that you would use in the shortcode method. Just enter this text in the appropriate textarea in the widget form.

== How can I override the default content defined for a particular variable combination? ==

The default content for the variable is displayed when the variable is not defined in the URL, or it is defined with a value that does not exist.

If you want to override this content in some place, use the same pattern for overriding a variable/value combination (see answer to previous question), but use "default" instead of the value name. Example:

-value-default
I want this shown instead of the default!
-value-default

== How can I insert the URL's query variable value into my site? ==
Sometimes you may want to use value in the user's URL to display something on your page. For example, you might want the link example.com/my-page/?username=Gretel to display "Greetings, Gretel!" on the page. To do this, enter a description for the particular variable (in this case, username) back on the screen where you can add or edit GET Custom Content Variables. Include _VALUE_ in the spot where you want the value to be filled in. For example, you could type:

Greetings, _VALUE_.

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Changelog ==

1.1 - Added ability to set a default value for each variable. Default can be set with a meta box, overridden in shortcode. Also added ability to override pre-defined content in the widget. Cleaned up a lot of code.

1.0 - Plugin invented!