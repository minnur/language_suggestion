<?php

namespace Drupal\language_suggestion\Plugin\LanguageNegotiation;

use Drupal\Component\Utility\UserAgent;
use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language HTTP header.
 *
 * @LanguageNegotiation(
 *   id = \Drupal\language_suggestion\Plugin\LanguageNegotiation\LanguageNegotiationHTTPHeader::METHOD_ID,
 *   weight = -3,
 *   name = @Translation("HTTP Header"),
 *   description = @Translation("Language from the HTTP header."),
 *   config_route_name = "language_suggestion.negotiation_http_header"
 * )
 */
class LanguageNegotiationHTTPHeader extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-http-header';

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    $langcode = NULL;

    if ($this->languageManager && $request && $request->server->get('HTTP_ACCEPT_LANGUAGE')) {
      $http_accept_language = $request->server->get('HTTP_ACCEPT_LANGUAGE');
      $langcodes = array_keys($this->languageManager->getLanguages());
      $mappings = $this->config->get('language.mappings')->get('map');
      $langcode = UserAgent::getBestMatchingLangcode($http_accept_language, $langcodes, $mappings);
    }

    // Internal page cache with multiple languages and browser negotiation
    // could lead to wrong cached sites. Therefore disabling the internal page
    // cache.
    // @todo Solve more elegantly in https://www.drupal.org/node/2430335.
    \Drupal::service('page_cache_kill_switch')->trigger();

    return $langcode;
  }

}
