<?php

namespace Drupal\notify\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Messenger\MessengerInterface;
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
   * Class constructor.
   *
   * @param ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param MessengerInterface $messenger
   *   The core messenger service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger) {
    parent::__construct($config_factory);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('messenger')
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
    $form['notify_defaults'] = array(
      '#type' => 'fieldset',
      '#title' => t('Notification default for new users'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('The default master switch for new users (check for enabled, uncheck for disabled).'),
    );

    $form['notify_defaults']['notify_reg_default'] = array(
      '#type' => 'checkbox',
      '#title' => t('Receive e-mail notifications'),
      '#return_value' => 1,
      '#default_value' => $config->get('notify_reg_default'),
    );

    $form['notify_defs'] = array(
      '#type' => 'fieldset',
      '#title' => t('Initial settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('These are the initial settings that will apply to new users registering, and to users that are enrolled in notifications with batch subscription.'),
    );
    $form['notify_defs']['node'] = array(
      '#type' => 'radios',
      '#title' => t('Notify new content'),
      '#default_value' => $config->get('notify_def_node'),
      '#options' => array(t('Disabled'), t('Enabled')),
      '#description' => t('Include new posts in the notification mail.'),
    );
    $form['notify_defs']['comment'] = array(
      '#type' => 'radios',
      '#access' => \Drupal::service('module_handler')->moduleExists('comment'),
      '#title' => t('Notify new comments'),
      '#default_value' => $config->get('notify_def_comment'),
      '#options' => array(t('Disabled'), t('Enabled')),
      '#description' => t('Include new comments in the notification mail.'),
    );
    $form['notify_defs']['teasers'] = array(
      '#type' => 'radios',
      '#title' => t('How much to include?'),
      '#default_value' => $config->get('notify_def_teasers'),
      '#options' => array(
        t('Title only'),
        t('Title + Teaser/Excerpt'),
        t('Title + Body'),
        t('Title + Body + Fields'),
      ),
      '#description' => t('Select the amount of each item to include in the notification e-mail.'),
    );

    $set = 'ntype';
    $form[$set] = array(
      '#type' => 'fieldset',
      '#title' => t('Notification by node type'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('Having nothing checked defaults to sending notifications about all node types.'),
    );
    $nodetypes = array();
    foreach (\Drupal\node\Entity\NodeType::loadMultiple() as $type => $object) {
      $nodetypes[$type] = $object->label();
    }

    if (NULL !== ($config->get('notify_nodetypes'))) {
      $def_nodetypes = $config->get('notify_nodetypes');
    } else {
      $def_nodetypes = array();
    }

    $form[$set]['notify_nodetypes'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Node types',
      '#options' => $nodetypes,
      '#default_value' => $def_nodetypes,
    );
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
    $this->messenger->addMessage(t('Notify default settings saved.'));
  }

}
