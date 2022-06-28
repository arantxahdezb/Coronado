<?php

namespace Drupal\coronado\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigPromoForm.
 */
class about_usForm extends ConfigFormBase
{

  protected function getEditableConfigNames()
  {
    return [
      'coronado.about_us',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'about_us_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('coronado.about_us');

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Titulo'),
      '#default_value' => ($config->get('title') !== NULL) ? $config->get('title') : '',
      '#description' => $this->t('Titulo'),
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#format' => ($config->get('description.format') !== NULL) ? $config->get('description.format') : 'basic_html',
      '#title' => $this->t('Descripción'),
      '#default_value' => ($config->get('description.value') !== NULL) ? $config->get('description.value') : '',
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

    $this->config('coronado.about_us')
      ->set('title', $form_state->getValue('title'))
      ->set('description', $form_state->getValue('description'))
      ->save();
  }
}
