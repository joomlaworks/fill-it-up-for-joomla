# Fill It Up (for Joomla) [v1.0.0]
***
Fill It Up for Joomla is a handy free extension to mass generate content &amp; users in Joomla.

You have the choice of easily creating your own set of re-usable dummy content (essentially made up of sets of images, links and embed snippets) or you can use third-party sets, curated by other users.

By using Fill It Up you speed up the initial development process until actual, proper content is in place.

It's that simple. Really :)

That's why it took only a few minutes to set up this demo site: [PENDING]

Fill It Up for Joomla can generate: Joomla articles, K2 items (with comments & rich media) and Joomla users. In the future we plan on adding content adapters so more Joomla extensions are supported out of the box.


## Screenshots
[PENDING]

## Demos
Check out the official demo site: [PENDING]

*Are you a theme developer using Fill It Up (for Joomla)? [Get in touch with us](http://www.joomlaworks.net/support/contact) and ask to include your demo sites here too!*


## Why?
Custom dummy content should not be hard to make. Unlike other solutions, you get to create your own set of dummy content and re-use them as you want.

It's what we like to call "curated sample data sets"!

In the future, we plan to release pre-built sets of definition files from other users, covering different types of dummy content (e.g. politics, cars, design, fashion, news etc.) for anyone to re-use and get from prototyping to a production ready Joomla site in hours, not days!


## Use it
1. Get the latest build, ready to upload to Joomla: http://www.joomlaworks.net/downloads/?f=jw_fillitup_for_joomla-v1.0.0.zip (please DO NOT use the zip file download provided by GitHub automatically)
2. Edit the component's Settings and add this demo definition file:
   - If you already use HTTPS: https://cdn.joomlaworks.org/fillitup/demo/900x600.json?upd
   - If you get any issues related to SSL (especially while testing Fill It Up locally), use this URL instead: http://cdn.joomlaworks.org/fillitup/demo/900x600_plain_http.json
3. Go back to the main component page and choose one of the (currently) three options for content & user generation.
4. Adjust your settings for that option and go!

Depending on the number of items you choose to generate, it will take from a few seconds to a few minutes to complete, so be patient. You'll see a success message when the process is complete.


## Create your own dummy content sets
Examine the structure of the demo definition file: https://cdn.joomlaworks.org/fillitup/demo/900x600.json?upd

You'll notice that this file references some .zip files. These .zip files contain images which are fetched by Fill It Up and inserted in Joomla items in the category name specified in the same definition block. Additionally, per category block, you can pass one JavaScript array for videos (use links for video providers supported by K2, or use entire embed snippets for others) and one JavaScript array for Flickr sets (albums) (use entire embed snippets). Now since the file has to be valid JSON, make sure you escape any double quotes (\") when inserting embed snippets into each array.


## Upgrading
Just install the latest build on top of any previous installation.


***Enjoy and share it :)***

===
Fill It Up for Joomla is released under the GNU/GPL license.

Copyright (c) 2006 - 2016 [JoomlaWorks Ltd.](http://www.joomlaworks.net)
