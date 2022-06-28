<?php

namespace Drupal\coronado_products\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */
class addProduct extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'coronado_products_addProduct';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $options = array();
    foreach ($this->product_type() as $result) {
      $options[$result->id] = $result->name;
    }

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#maxlength' => 100,
      '#required' => true,
    );

    $form['description_preview'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Descripción Preview'),
      '#maxlength' => 100,
      '#required' => true,
    );

    $form['description_detail'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Descripción Detalle'),
      '#required' => true,
    );

    $form['image_preview'] = [
      '#type' => 'managed_file',
      '#title'  => t('Imagen Preview'),
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
      '#upload_location'  => 'public://products/',
      '#required' => true,
    ];

    $form['code'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Código Ecommerce'),
      '#maxlength' => 50,
    );

    $form['product_type_id'] = array(
      '#type' => 'select',
      '#title' => $this->t('Categoría de producto'),
      '#options' => $options,
      '#required' => true,
    );

    $form['ranges'] = [
      '#type' => 'item'
    ];

    // Gather the number of names in the form already.
    $num_ranges = $form_state->get('num_ranges');
    // We have to ensure that there is at least one name field.
    if ($num_ranges === NULL) {
      $form_state->set('num_ranges', 1);
      $num_ranges = 1;
    }

    $form['#tree'] = TRUE;
    $form['ranges_fieldset'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Presentaciones'),
      '#prefix' => '<div id="ranges-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $num_ranges; $i++) {
      $j = $i + 1;
      $form['ranges_fieldset']['range'][$i] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Presentación ' . $j),
      ];

      $form['ranges_fieldset']['range'][$i]['mass'][$i] = [
        '#title' => $this->t('Presentación'),
        '#description' => t('For example. Untable: 310 g'),
        '#type' => 'textfield',
      ];
      $form['ranges_fieldset']['range'][$i]['image'][$i] = [
        '#type' => 'managed_file',
        '#title'  => t('Imagen'),
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
        '#upload_location'  => 'public://products/',
      ];
    }

    $form['ranges_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['ranges_fieldset']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar presentación'),
      '#submit' => ['::addOneranges'],
      '#ajax' => [
        'callback' => '::addmorerangesCallback',
        'wrapper' => 'ranges-fieldset-wrapper',
      ],
    ];
    // If there is more than one name, add the remove button.
    if ($num_ranges > 1) {
      $form['ranges_fieldset']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Eliminar presentación'),
        '#submit' => ['::removerangesCallback'],
        '#ajax' => [
          'callback' => '::addmorerangesCallback',
          'wrapper' => 'ranges-fieldset-wrapper',
        ],
      ];
    }

    $form['meta_title'] = array(
      '#type' => 'textfield',
      '#title' => 'Title',
      '#size' => 100,
      '#maxlength' => 100,
      '#required' => true,
    );

    $form['meta_description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Meta Description'),
      '#size' => 255,
      '#maxlength' => 255,
      '#required' => true,
    );

    $form['meta_keywords'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Meta Keywords'),
      '#size' => 255,
      '#maxlength' => 255,
    );

    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#maxlength' => 100,
      '#required' => TRUE,
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('coronado_products_product_cancel'),
    );

    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmorerangesCallback(array &$form, FormStateInterface $form_state)
  {
    return $form['ranges_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneranges(array &$form, FormStateInterface $form_state)
  {
    $name_field = $form_state->get('num_ranges');
    $add_button = $name_field + 1;
    $form_state->set('num_ranges', $add_button);
    // Since our buildForm() method relies on the value of 'num_ingredients' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removerangesCallback(array &$form, FormStateInterface $form_state)
  {
    $name_field = $form_state->get('num_ranges');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_ranges', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'num_ingredients' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
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
    $ranges = $form_state->getValue(['ranges_fieldset', 'range']);

    $connection = \Drupal::database();
    $fid = $form_state->getValue('image_preview')[0];

    $campos = array(
      'name' => $form_state->getValue('name'),
      'url' => $this->fixForUri($form_state->getValue('url')),
      'description_preview' => $form_state->getValue('description_preview'),
      'description_detail' => $form_state->getValue('description_detail'),
      'image_preview' => $fid,
      'code' => $form_state->getValue('code'),
      'ranges' => serialize($ranges),
      'product_type_id' => $form_state->getValue('product_type_id'),
      'meta_title' => $form_state->getValue('meta_title'),
      'meta_description' => $form_state->getValue('meta_description'),
      'meta_keywords' => $form_state->getValue('meta_keywords'),
      'created_at' => \Drupal::time()->getCurrentTime(),
      'updated_at' => \Drupal::time()->getCurrentTime(),
    );

    $connection->insert('coronado_product')
      ->fields($campos)
      ->execute();

    drupal_flush_all_caches();

    \Drupal::messenger()->addStatus($this->t('El producto @name fue creado exitosamente', array('@name' => $form_state->getValue('name'))));

    $form_state->setRedirect('coronado_products.listProduct');
  }

  function product_type()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product_type', 'p');
    $query->fields('p', ['id', 'name']);
    $result = $query->execute();
    return $result;
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
