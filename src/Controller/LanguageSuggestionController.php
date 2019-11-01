<?php

namespace Drupal\language_suggestion\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;

class LanguageSuggestionController extends ControllerBase {

  protected $config_factory;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config_factory = $config_factory->get('language_suggestion.settings');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Get HTTP parameter value.
   */
  public function getHTTPHeaderValue(Request $request) {
    $langcode = '';
    $data = [];
    $parameter = $this->config_factory->get('http_header_parameter');
    if ($this->config_factory->get('language_detection') == 'http_header') {
      $langcode = $request->headers->get($parameter);
    }
    $data['#cache'] = [
      'max-age' => 600,
      'tags' => ['language_suggestion_http_header'],
      'contexts' => [
        'url',
        'ip'
      ],
    ];
    $response = new CacheableJsonResponse($langcode);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($data));
    return $response;
  }

}
