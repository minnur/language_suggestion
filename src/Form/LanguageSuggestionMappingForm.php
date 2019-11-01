<?php

namespace Drupal\language_suggestion\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;

/**
 * Class Language Suggestion mapping form.
 */
class LanguageSuggestionMappingForm extends ConfigFormBase {
 
  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a \Drupal\language_suggestion\Form\LanguageSuggestionMappingForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, RequestStack $request_stack) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('request_stack')
    );
  }

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
    return 'language_suggestion_mapping_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $domain = $this->requestStack->getCurrentRequest()->getHttpHost();
    $config = $this->config('language_suggestion.settings');
    $form['mapping'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Browser Language'),
        $this->t('Message'),
        $this->t('Continue Title'),
        $this->t('Continue URL'),
        '',
        $this->t('Language'),
        '',
      ],
      '#empty' => $this->t('Looks like the site does not have any languages enabled.'),
      '#prefix' => '<br />',
    ];
    foreach ($this->languageManager->getLanguages() as $lang) {
      $id = $lang->getId();
      $langauge_path = Url::fromRoute('<front>', [], ['language' => $lang])->toString();
      $form['mapping'][$id]['browser_lang'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#default_value' => $config->get('mapping.' . $id . '.browser_lang'),
        '#description' => $this->t('Comma separated language codes'),
        '#size' => 15,
      ];
      $form['mapping'][$id]['message'] = [
        '#type' => 'textarea',
        '#title' => '',
        '#default_value' => $config->get('mapping.' . $id . '.message'),
        '#description' => $this->t('A message to show. Write this message in the language that will be suggested'),
      ];
      $form['mapping'][$id]['continue_link'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#default_value' => $config->get('mapping.' . $id . '.continue_link'),
        '#description' => $this->t('Continue link title'),
        '#size' => 20,
      ];
      $form['mapping'][$id]['url'] = [
        '#type' => 'radios',
        '#title' => '',
        '#options' => [
          'default' => $langauge_path,
          'custom' => $this->t('Custom'),
        ],
        '#default_value' => ($url = $config->get('mapping.' . $id . '.url')) ? $url : 'default',
        '#size' => 25,
      ];
      $form['mapping'][$id]['custom_url'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#default_value' => $config->get('mapping.' . $id . '.custom_url'),
        '#description' => $this->t('Custom redirect URL'),
        '#size' => 20,
        '#states' => [
          'visible' => [
            ':input[name="mapping[' . $id . '][url]"]' => ['value' => 'custom'],
          ],
        ],
      ];
      $form['mapping'][$id]['suggeseted_lang'] = [
        '#plain_text' => $lang->getName(),
      ];
      $form['mapping'][$id]['default_url'] = [
        '#type' => 'hidden',
        '#value' => $langauge_path,
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    Cache::invalidateTags(['language_suggestion_http_header']);
    $this->config('language_suggestion.settings')
      ->set('mapping', $form_state->getValue('mapping'))
      ->save();
  }

}
