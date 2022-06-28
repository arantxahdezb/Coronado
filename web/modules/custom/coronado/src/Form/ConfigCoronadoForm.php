<?php

namespace Drupal\coronado\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigPromoForm.
 */
class ConfigCoronadoForm extends ConfigFormBase
{

  protected function getEditableConfigNames() {
    return [
      'coronado.configcoronado',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'config_coronado_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('coronado.configcoronado');

    $form['notice_of_privacy'] = [
      '#type' => 'text_format',
      '#format' => ($config->get('notice_of_privacy.format') !== NULL) ? $config->get('notice_of_privacy.format') : 'basic_html',
      '#title' => $this->t('Aviso de privacidad'),
      '#default_value' => ($config->get('notice_of_privacy.value') !== NULL) ? $config->get('notice_of_privacy.value') : '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitForm($form, $form_state);

    $this->config('coronado.configcoronado')
      ->set('notice_of_privacy', $form_state->getValue('notice_of_privacy'))
      ->save();
  }
}
