<?php

namespace Drupal\language_suggestion\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

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
      '#description' => $this->t('Show language suggestion.'),
      '#default_value' => $config->get('enabled'),
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
        'http_header' => $this->t('HTTP header')
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
      '#description' => $this->t('Specify HTTP header parameter that contains language code. <strong>Case sensitive parameter</strong>.'),
      '#default_value' => $config->get('http_header_parameter'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
          ':input[name="always_redirect"]' => ['checked' => TRUE],
          ':input[name="language_detection"]' => ['value' => 'http_header'],
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
    Cache::invalidateTags(['language_suggestion_http_header']);
    $this->config('language_suggestion.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('container_class', $form_state->getValue('container_class'))
      ->set('cookie_dismiss_time', $form_state->getValue('cookie_dismiss_time'))
      ->set('always_redirect', $form_state->getValue('always_redirect'))
      ->set('disable_redirect_class', $form_state->getValue('disable_redirect_class'))
      ->set('language_detection', $form_state->getValue('language_detection'))
      ->set('http_header_parameter', $form_state->getValue('http_header_parameter'))
      ->set('show_delay', $form_state->getValue('show_delay'))
      ->save();
  }

}
