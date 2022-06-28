<?php

namespace Drupal\coronado_products\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements an example form.
 */
class editProductType extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'coronado_products_editProductType';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = null)
  {
    $productType = $this->get_productType($arg);

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#default_value' => $productType['name'],
      '#size' => 100,
      '#maxlength' => 100,
      '#required' => TRUE,
    );

    $form['meta_title'] = array(
      '#type' => 'textfield',
      '#title' => 'Title',
      '#size' => 100,
      '#maxlength' => 100,
      '#required' => true,
      '#default_value' => $productType['meta_title'],
    );

    $form['meta_description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Meta Description'),
      '#size' => 255,
      '#maxlength' => 255,
      '#required' => true,
      '#default_value' => $productType['meta_description'],
    );

    $form['meta_keywords'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Meta Keywords'),
      '#size' => 255,
      '#maxlength' => 255,
      '#default_value' => $productType['meta_keywords'],
    );

    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#maxlength' => 100,
      '#default_value' => $productType['url'],
      '#required' => true,
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Guardar'),
    ];

    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('coronado_products_productType_cancel'),
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
    $campos = array(
      'name' => $form_state->getValue('name'),
      'url' => $this->fixForUri($form_state->getValue('url')),
      'meta_title' => $form_state->getValue('meta_title'),
      'meta_description' => $form_state->getValue('meta_description'),
      'meta_keywords' => $form_state->getValue('meta_keywords'),
      'updated_at' => \Drupal::time()->getCurrentTime(),
    );

    $id = $form_state->getValue('id');

    $connection->update('coronado_product_type')
      ->fields($campos)
      ->condition('id', $id)
      ->execute();

    drupal_flush_all_caches();

    \Drupal::messenger()->addStatus($this->t('La categoría de producto @name fue actualizada exitosamente', array('@name' => $form_state->getValue('name'))));

    $form_state->setRedirect('coronado_products.listProductType');
  }

  function get_productType($arg)
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product_type', 'p');
    $query->fields('p');
    $query->condition('id', $arg);
    $result = $query->execute();
    return $result->fetchAssoc();
  }

  function fixForUri($string)
  {
    $slug = trim($string); // trim the string
    $slug = preg_replace('/[^a-zA-Z0-9 -]/', '', $slug); // only take alphanumerical characters, but keep the spaces and dashes too...
    $slug = str_replace(' ', '-', $slug); // replace spaces by dashes
    $slug = strtolower($slug); // make it lowercase
    return $slug;
  }
}
