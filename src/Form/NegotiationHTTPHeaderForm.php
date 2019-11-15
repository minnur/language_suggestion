<?php

namespace Drupal\language_suggestion\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure the browser language negotiation method for this site.
 *
 * @internal
 */
class NegotiationHTTPHeaderForm extends ConfigFormBase {

  /**
   * The configurable language manager.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConfigurableLanguageManagerInterface $language_manager) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'language_suggestion_configure_http_header_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['language_suggestion.language_negotiation'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    // Initialize a language list to the ones available, including English.
    $languages = $this->languageManager->getLanguages();

    $existing_languages = [];
    foreach ($languages as $langcode => $language) {
      $existing_languages[$langcode] = $language->getName();
    }

    // If we have no languages available, present the list of predefined languages
    // only. If we do have already added languages, set up two option groups with
    // the list of existing and then predefined languages.
    if (empty($existing_languages)) {
      $language_options = $this->languageManager->getStandardLanguageListWithoutConfigured();
    }
    else {
      $language_options = [
        (string) $this->t('Existing languages') => $existing_languages,
        (string) $this->t('Languages not yet added') => $this->languageManager->getStandardLanguageListWithoutConfigured(),
      ];
    }

    $form['mapping'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Language code'),
        $this->t('Site Language'),
      ],
      '#empty' => $this->t('Looks like the site does not have any languages enabled.'),
      '#prefix' => '<br />',
    ];
    foreach ($this->languageManager->getLanguages() as $lang) {
      $config = $this->config('language_suggestion.language_negotiation');
      $id = $lang->getId();
      $form['mapping'][$id]['http_language'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#default_value' => $config->get('mapping.' . $id . '.browser_lang'),
        '#description' => $this->t('Comma separated language/country codes'),
      ];
      $form['mapping'][$id]['language'] = [
        '#title' => $this->t('Site language'),
        '#title_display' => 'invisible',
        '#type' => 'select',
        '#options' => $language_options,
        '#default_value' => $id,
        '#required' => TRUE,
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('language_suggestion.language_negotiation');
    $mappings = $form_state->get('mappings');
    if (!empty($mappings)) {
      $config->setData(['map' => $mappings]);
      $config->save();
    }

    parent::submitForm($form, $form_state);
  }

}
