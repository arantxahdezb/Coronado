<?php

namespace Drupal\coronado\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */
class addBanner extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'coronado_addBanner';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => t('Nombre para identificar el banner'),
      '#maxlength' => 100,
      '#required' => TRUE,
    );

    $form['image_desktop'] = [
      '#type' => 'managed_file',
      '#title'  => t('Imagen Escritorio'),
      '#description' => t('Allowed extensions: png jpg jpeg'),
      '#progress_indicator' => 'bar',
      '#progress_message' => 'Wait ...',
      '#upload_validators' => [
        'file_validate_is_image' => array(),
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => array(5 * 1024 * 1024),
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location'  => 'public://banner_home/',
      '#required' => TRUE,
    ];

    $form['image_mobile'] = [
      '#type' => 'managed_file',
      '#title'  => t('Imagen Mobile'),
      '#description' => t('Allowed extensions: png jpg jpeg'),
      '#progress_indicator' => 'bar',
      '#progress_message' => 'Wait ...',
      '#upload_validators' => [
        'file_validate_is_image' => array(),
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => array(5 * 1024 * 1024),
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location'  => 'public://banner_home/',
      '#required' => TRUE,
    ];

    $form['video_link'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL embed youtube'),
      '#description' => 'For example. URL:https://www.youtube.com/watch?v=gMF7Wp5iVto ID: <strong>gMF7Wp5iVto</strong>',
      '#maxlength' => 100,
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('coronado_banner_cancel'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $connection = \Drupal::database();
    $image_desktop = $form_state->getValue('image_desktop')[0];
    $image_mobile = $form_state->getValue('image_mobile')[0];
    // $file = File::load($fid);
    // file_create_url($file->getFileUri());

    $campos = array(
      'title' => $form_state->getValue('title'),
      'image_desktop' => $image_desktop,
      'image_mobile' => $image_mobile,
      'video_link' => $form_state->getValue('video_link'),
      'created_at' => \Drupal::time()->getCurrentTime(),
      'updated_at' => \Drupal::time()->getCurrentTime(),
    );

    $connection->insert('coronado_home')
      ->fields($campos)
      ->execute();

    drupal_flush_all_caches();

    \Drupal::messenger()->addStatus($this->t('El banner fue creado exitosamente'));

    $form_state->setRedirect('coronado.listBanner');
  }
}
