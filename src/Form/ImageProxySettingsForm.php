<?php

namespace Drupal\toolkit\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ImageProxySettingsForm.
 */
class ImageProxySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'toolkit.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'image_proxy_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('toolkit.settings');

    $form['image_proxy'] = [
      '#type' => 'url',
      '#title' => $this->t('Image proxy domain'),
      '#default_value' => $config->get('image_proxy'),
      '#description' => $this->t('All image URLs will be rewritten to use this domain rather than the current site domain. Provide a domain with no trailing slashes, for example, "https://images.proxydomain.com".'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('image_proxy');

    if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
      $form_state->setErrorByName('domain', $this->t('The domain needs to be a valid URL.'));
    }

    if (!empty($url) && substr($url, -1) === '/') {
      $form_state->setErrorByName('domain', $this->t('The domain cannot end in slash.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('toolkit.settings')
      ->set('image_proxy', $form_state->getValue('image_proxy'))
      ->save();
  }

}
