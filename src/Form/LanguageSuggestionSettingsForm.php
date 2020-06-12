<?php

namespace Drupal\language_suggestion\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Drupal\file\Entity\File;

/**
 * Class Language Suggestion settings form.
 */
class LanguageSuggestionSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['language_suggestion.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'language_suggestion_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('language_suggestion.settings');
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#description' => $this->t('Show langauge suggestion.'),
      '#default_value' => $config->get('enabled'),
    ];
    $form['cookie_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie name prefix'),
      '#description' => $this->t('Cookie name that is prefxied to all variable that needs to be stored. Could be used to reset all the cookies for your visitors by renaming it.'),
      '#default_value' => $config->get('cookie_prefix'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['container_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Container CSS element'),
      '#description' => $this->t('Main container CSS element class or ID. This is the element that will be used to add language suggestion box.'),
      '#default_value' => $config->get('container_class'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['show_delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Language suggestion delay'),
      '#description' => $this->t('How many seconds to wait before showing language suggestion.'),
      '#default_value' => $config->get('show_delay'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['cookie_dismiss_time'] = [
      '#type' => 'number',
      '#title' => $this->t('Dismiss delay'),
      '#description' => $this->t('How many hours to wait before showing language suggestion again after clicking Dismiss button.'),
      '#default_value' => $config->get('cookie_dismiss_time'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['always_redirect'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Always redirect'),
      '#description' => $this->t('Visitors who selected suggested language in the past will be redirected to prefered language automatically.'),
      '#default_value' => $config->get('always_redirect'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['disable_redirect_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Language switch CSS element'),
      '#description' => $this->t('Class/ID of the element that disables automatic redirect. For example when visitro manually decides to switch a language in the UI.'),
      '#default_value' => $config->get('disable_redirect_class'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
          ':input[name="always_redirect"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['language_detection'] = [
      '#type' => 'radios',
      '#title' => $this->t('Language detection'),
      '#description' => $this->t('Language detection method.'),
      '#default_value' => ($language_detection = $config->get('language_detection')) ? $language_detection : 'browser',
      '#options' => [
        'browser' => $this->t('Browser'),
        'http_header' => $this->t('HTTP header'),
        'geoip_db' => $this->t('GEO IP2 Country Database (https://www.maxmind.com/)'),
      ],
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['http_header_parameter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('HTTP header parameter'),
      '#description' => $this->t('Specify HTTP header parameter that contains langauge code. <strong>Case sensitive parameter</strong>.'),
      '#default_value' => $config->get('http_header_parameter'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
          ':input[name="always_redirect"]' => ['checked' => TRUE],
          ':input[name="language_detection"]' => ['value' => 'http_header'],
        ],
      ],
    ];
    $form['geoip_db_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('GeoIP Country Database File'),
      '#upload_location' => 'public://maxmind',
      '#upload_validators' => [
        'file_validate_extensions' => ['mmdb'],
      ],
      '#default_value' => ($file_id = $config->get('geoip_db_file')) ? [$file_id] : '',
      '#description' => $this->t('Upload .mmdb file.'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
          ':input[name="language_detection"]' => ['value' => 'geoip_db'],
        ],
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $db_file = $form_state->getValue('geoip_db_file');
    if (!empty($db_file[0])) {
      $file = File::load($db_file[0]);
      $file->setPermanent();
      $file->save();
    }

    Cache::invalidateTags(['language_suggestion_http_header']);
    $this->config('language_suggestion.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('cookie_prefix', $form_state->getValue('cookie_prefix'))
      ->set('container_class', $form_state->getValue('container_class'))
      ->set('cookie_dismiss_time', $form_state->getValue('cookie_dismiss_time'))
      ->set('always_redirect', $form_state->getValue('always_redirect'))
      ->set('disable_redirect_class', $form_state->getValue('disable_redirect_class'))
      ->set('language_detection', $form_state->getValue('language_detection'))
      ->set('http_header_parameter', $form_state->getValue('http_header_parameter'))
      ->set('show_delay', $form_state->getValue('show_delay'))
      ->set('geoip_db_file', isset($file) ? $file->id() : '')
      ->save();
  }

}
