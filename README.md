# Language Suggestion module for Drupal 8

Drupal 8 module built for multilingual websites with a friendly suggestion box about other versions of the site with browser based language detection.

Language suggestion mapping page:

![Language Suggestion Drupal 8 module](https://www.drupal.org/files/screenshot_201.png)

Settings page:

![Language Suggestion Drupal 8 module](https://www.drupal.org/files/screenshot-2_12.png)

Suggestion box that appears for visitors

![Language Suggestion box Drupal 8 module](https://www.drupal.org/files/suggestion-box_0.png)

### Use case

Sometimes when implementing multilingual sites there might be a case when rather than redirecting to a version of the site that you assume visitor is from you might want suggest and ask them if they would like to continue to a language that we think he understands.

This module does exactly that. It shows a little box with some text and a link to take to a sugested version of the site. The module also provides a way to **dismiss** the box which just snoozes it for a configurable time. It can also remember selection and **automatically redirect** to previously selected language. This option can be overriden by a user when manually selecting a language from the language switcher/dropdown.

Configuration page is located at (`Administration`  > `Configuration` > `Regional and language`). Path `/admin/config/regional/language-suggestion`


Module developed by [Minnur Yunusov](https://www.minnur.com) at [Chapter Three](https://www.chapterthree.com)

### Install

`composer require drupal/language_suggestion` or download from [Language Suggestion Drupal project page](https://www.drupal.org/project/language_suggestion)
