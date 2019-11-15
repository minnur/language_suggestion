<?php

namespace Drupal\language_suggestion\Plugin\LanguageNegotiation;

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
    $config = $this->config('language_suggestion.language_negotiation');
    $header_param = $config->get('header_param');

    if ($this->languageManager && $request && !empty($header_param) && $request->server->get($header_param)) {
      $http_header_lang = strtolower($request->server->get($header_param));
      $mapping = $this->config->get('mapping');
      foreach ($mapping as $item) {
        if ($codes = explode(',', strtolower($item['http_language']))) {
          foreach ($codes as $code) {
            if ($http_header_lang == $code) {
              $langcode = $item['language'];
            }
          }
        }
      }
    }

    \Drupal::service('page_cache_kill_switch')->trigger();

    return $langcode;
  }

}
