<?php

namespace Drupal\notify\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\node\Entity\NodeType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class DefaultForm extends ConfigFormBase {

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Class constructor.
   *
   * @param ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param MessengerInterface $messenger
   *   The core messenger service.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   */
  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger, ModuleHandler $module_handler) {
    parent::__construct($config_factory);
    $this->messenger = $messenger;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('messenger'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'notify_default_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'notify.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('notify.settings');
    $set = 'defaults';
    $form['notify_defaults'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Notification default for new users'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => $this->t('The default master switch for new users (check for enabled, uncheck for disabled).'),
    ];

    $form['notify_defaults']['notify_reg_default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Receive e-mail notifications'),
      '#return_value' => 1,
      '#default_value' => $config->get('notify_reg_default'),
    ];

    $form['notify_defs'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Initial settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => $this->t('These are the initial settings that will apply to new users registering, and to users that are enrolled in notifications with batch subscription.'),
    ];
    $form['notify_defs']['node'] = [
      '#type' => 'radios',
      '#title' => $this->t('Notify new content'),
      '#default_value' => $config->get('notify_def_node'),
      '#options' => [$this->t('Disabled'), $this->t('Enabled')],
      '#description' => $this->t('Include new posts in the notification mail.'),
    ];
    $form['notify_defs']['comment'] = [
      '#type' => 'radios',
      '#access' => $this->moduleHandler->moduleExists('comment'),
      '#title' => $this->t('Notify new comments'),
      '#default_value' => $config->get('notify_def_comment'),
      '#options' => [$this->t('Disabled'), $this->t('Enabled')],
      '#description' => $this->t('Include new comments in the notification mail.'),
    ];
    $form['notify_defs']['teasers'] = [
      '#type' => 'radios',
      '#title' => $this->t('How much to include?'),
      '#default_value' => $config->get('notify_def_teasers'),
      '#options' => [
        $this->t('Title only'),
        $this->t('Title + Teaser/Excerpt'),
        $this->t('Title + Body'),
        $this->t('Title + Body + Fields'),
      ],
      '#description' => $this->t('Select the amount of each item to include in the notification e-mail.'),
    ];

    $set = 'ntype';
    $form[$set] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Notification by node type'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => $this->t('Having nothing checked defaults to sending notifications about all node types.'),
    ];
    $nodetypes = [];
    foreach (NodeType::loadMultiple() as $type => $object) {
      $nodetypes[$type] = $object->label();
    }

    if (NULL !== ($config->get('notify_nodetypes'))) {
      $def_nodetypes = $config->get('notify_nodetypes');
    } else {
      $def_nodetypes = [];
    }

    $form[$set]['notify_nodetypes'] = [
      '#type' => 'checkboxes',
      '#title' => 'Node types',
      '#options' => $nodetypes,
      '#default_value' => $def_nodetypes,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('notify.settings')
      ->set('notify_reg_default', $values['notify_reg_default'])
      ->set('notify_def_node', $values['node'])
      ->set('notify_def_comment', $values['comment'])
      ->set('notify_def_teasers', $values['teasers'])
      ->set('notify_nodetypes', $values['notify_nodetypes'])
      ->save();
    $this->messenger->addMessage($this->t('Notify default settings saved.'));
  }

}
