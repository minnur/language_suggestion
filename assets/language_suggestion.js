(function ($, Drupal, drupalSettings) {

  'use strict';

  $(document).ready(function() {
    var current_lang = drupalSettings.language_suggestion.current_language.toLowerCase();
    var pathLangCode = drupalSettings.path.currentLanguage;
    var settings = drupalSettings.language_suggestion.settings;
    var browser_lang = 'de';//window.navigator.userLanguage || window.navigator.language; // Set to 'de' for debugging.
    var layoutContainer = $((settings.container_class !== undefined || settings.container_class !== '') ? settings.container_class : 'body');
    var show_suggestion = false;
    var lang_code = null;
    var continue_link = null;
    var message = null;
    
    var dismissed = $.cookie('language_suggestion.dismiss');
    var redrectLangCode = $.cookie('language_suggestion.always_redirect');

    var date = new Date();
    var timestamp = date.getTime();

    if (settings.enabled) {

      // This code disables auto redirect when a visitor decides to switch languages in the UI.
      if (settings.disable_redirect_class) {
        $(settings.disable_redirect_class).on('click', function(e) {
          $.removeCookie('language_suggestion.always_redirect');
        });
      }

      // Auto redirect to previously selected langauge.
      // Also making sure we are not creating a redirect loop when already switch to a language.
      if (settings.always_redirect && redrectLangCode !== undefined && redrectLangCode !== current_lang) {
        window.location.href = window.location.origin + '/' + redrectLangCode;
      }

      // Looping through available language mapping to find if any mapped languages matching to a visitors browser language.
      for (var langcode in settings.mapping) {
        if (settings.mapping.hasOwnProperty(langcode)) {
          var langObject = settings.mapping[langcode];
          var codeArr = langObject.browser_lang.split(',');
          var index = 0;
          while (index < codeArr.length) { 
            if (codeArr[index] != current_lang && codeArr[index] == browser_lang.toLowerCase()) {
              show_suggestion = true;
              lang_code = langcode;
              continue_link = langObject.continue_link;
              message = langObject.message;
            }
            index++; 
          }
        }
      }

      // Show the language suggestion box. Make sure user hasn't dismissed in the past or dismiss hasn't expired yet.
      if (show_suggestion && message && (dismissed <= timestamp || dismissed === undefined)) {
        layoutContainer.append('<div id="language-suggestion"<div class="ls-wrapper"><div class="ls-message">'
          + message + '</div><div class="ls-goto"><a href="#" id="ls-continue">' + ((continue_link !== undefined) ? continue_link : Drupal.t('Continue'))
          + '</a></div><div class="ls-dismiss"><a href="#" id="ls-dismiss">'
          + Drupal.t('Dismiss') + '</a></div></div></div>');
        layoutContainer.find('#language-suggestion').delay(settings.show_delay * 1000).show('slow');
      }
    }

    // Continue to the language suggested and make sure we add to autoredirect cookie if such option is enabled in the module settings.
    layoutContainer.find('#ls-continue').on('click', function(e) {
      if (settings.always_redirect) {
        $.cookie('language_suggestion.always_redirect', lang_code);
      }
      window.location.href = window.location.origin + '/' + lang_code;
      e.disableDefault();
    });

    // Dismiss language suggestion box and make sure we keep it dismissed for some time. Time can be configured in the module settings.
    layoutContainer.find('#ls-dismiss').on('click', function(e) {
      layoutContainer.find('#language-suggestion').hide('slow');
      var milliseconds = settings.cookie_dismiss_time * 60 * 60 *1000;
      $.cookie('language_suggestion.dismiss', timestamp + milliseconds);
      e.disableDefault();
    });

  });

})(jQuery, Drupal, drupalSettings);
