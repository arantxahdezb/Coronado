<?php

namespace Drupal\coronado\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigPromoForm.
 */
class how_we_workForm extends ConfigFormBase
{

  protected function getEditableConfigNames() {
    return [
      'coronado.how_we_work',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'how_we_work_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('coronado.how_we_work');

    $form['how_we_work'] = [
      '#type' => 'text_format',
      '#format' => ($config->get('how_we_work.format') !== NULL) ? $config->get('how_we_work.format') : 'full_html',
      '#title' => $this->t('Cómo trabajamos'),
      '#default_value' => ($config->get('how_we_work.value') !== NULL) ? $config->get('how_we_work.value') : '',
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

    $this->config('coronado.how_we_work')
      ->set('how_we_work', $form_state->getValue('how_we_work'))
      ->save();
  }
}
