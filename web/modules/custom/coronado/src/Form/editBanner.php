<?php

namespace Drupal\coronado\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements an example form.
 */
class editBanner extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'coronado_editBanner';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = null)
  {

    $banner = $this->get_banner($arg);

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => t('Nombre para identificar el banner'),
      '#maxlength' => 100,
      '#default_value' => $banner['title'],
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
      '#default_value' => [$banner['image_desktop']],
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
      '#default_value' => [$banner['image_mobile']],
    ];

    $form['video_link'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL embed youtube'),
      '#description' => 'For example. URL:https://www.youtube.com/watch?v=gMF7Wp5iVto ID: <strong>gMF7Wp5iVto</strong>',
      '#maxlength' => 100,
      '#default_value' => $banner['video_link'],
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

    $form['id'] = array(
      '#type' => 'hidden',
      '#value' => $arg,
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

    $campos = array(
      'title' => $form_state->getValue('title'),
      'image_desktop' => $image_desktop,
      'image_mobile' => $image_mobile,
      'video_link' => $form_state->getValue('video_link'),
      'updated_at' => \Drupal::time()->getCurrentTime(),
    );

    $id = $form_state->getValue('id');

    $connection->update('coronado_home')
      ->fields($campos)
      ->condition('id', $id)
      ->execute();

    drupal_flush_all_caches();

    \Drupal::messenger()->addStatus($this->t('El banner fue actualizado exitosamente'));

    $form_state->setRedirect('coronado.listBanner');
  }

  function get_banner($arg)
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_home', 'p');
    $query->fields('p');
    $query->condition('id', $arg);
    $result = $query->execute();
    return $result->fetchAssoc();
  }
}
