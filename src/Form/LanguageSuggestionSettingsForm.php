<?php

namespace Drupal\language_suggestion\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
    return 'language_suggestion_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $mgr = \Drupal::languageManager();
    $config = $this->config('language_suggestion.settings');
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#description' => $this->t('Show langauge suggestion.'),
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
    $form['show_delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Language Suggestion Delay'),
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
      '#title' => $this->t('Dismiss Delay'),
      '#description' => $this->t('How many hours to wait before showing language suggestion again after clicking Dismiss button.'),
      '#default_value' => $config->get('cookie_dismiss_time'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['container'] = [
      '#type' => 'fieldset',
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['container']['mapping'] = [
      '#type' => 'table',
      '#header' => [$this->t('Browser Language'), $this->t('Message'), $this->t('Continue Link'), $this->t('Language'), ''],
      '#empty' => $this->t('Looks like the site does not have any languages enabled.'),
    ];
    foreach ($mgr->getLanguages() as $lang) {
      $id = $lang->getId();
      $form['container']['mapping'][$id]['browser_lang'] = [
        '#type' => 'textfield',
        '#title' => $this->t(''),
        '#default_value' => $config->get('mapping.' . $id . '.browser_lang'),
        '#description' => $this->t('Comma separated list of browser language codes'),
        '#size' => 15,
      ];
      $form['container']['mapping'][$id]['message'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Message'),
        '#default_value' => $config->get('mapping.' . $id . '.message'),
        '#description' => $this->t('A message to show. Write this message in the language that will be suggested'),
      ];
      $form['container']['mapping'][$id]['continue_link'] = [
        '#type' => 'textfield',
        '#title' => $this->t(''),
        '#default_value' => $config->get('mapping.' . $id . '.continue_link'),
        '#description' => $this->t('Continue link title'),
        '#size' => 25,
      ];
      $form['container']['mapping'][$id]['suggeseted_lang'] = [
        '#plain_text' => $lang->getName(),
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('language_suggestion.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('container_class', $form_state->getValue('container_class'))
      ->set('always_redirect', $form_state->getValue('always_redirect'))
      ->set('disable_redirect_class', $form_state->getValue('disable_redirect_class'))
      ->set('cookie_dismiss_time', $form_state->getValue('cookie_dismiss_time'))
      ->set('show_delay', $form_state->getValue('show_delay'))
      ->set('mapping', $form_state->getValue('mapping'))
      ->save();
  }

}
