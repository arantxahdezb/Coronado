<?php

namespace Drupal\coronado_recipes\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */
class addRecipe extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'coronado_recipes_addRecipe';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $products = array();
    foreach ($this->product() as $result) {
      $products[$result->name] = $result->name;
    }

    $levels = array();
    $levels["Principiante"] = "Principiante";
    $levels["Casual"] = "Casual";
    $levels["Chef"] = "Chef";

    $temporalitys = array();

    $temporalitys["desayuno"] = "Desayuno";
    $temporalitys["compartir-con-pareja"] = "Compartir con la pareja";
    $temporalitys["compartir-con-amigos"] = "Compartir con amigos";
    $temporalitys["compartir-con-la-familia"] = "Compartir con la familia";
    $temporalitys["para-los-mas-pequenos"] = "Para los más pequeños";
    $temporalitys["fiestas-infantiles"] = "Fiestas Infantiles";
    $temporalitys["ocasion-especiales"] = "Ocasiones especiales";
    $temporalitys["para-el-calor"] = "Para el calor";
    $temporalitys["para-el-frio"] = "Para el frío";
    $temporalitys["san-valentin"] = "San Valentín";
    $temporalitys["mes-patrio"] = "Mes Patrio";
    $temporalitys["halloween"] = "Halloween";
    $temporalitys["navidad"] = "Navidad";

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#size' => 100,
      '#maxlength' => 100,
      '#required' => TRUE,
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#size' => 255,
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $form['image_preview'] = [
      '#type' => 'managed_file',
      '#title'  => t('Imagen preview'),
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
      '#upload_location'  => 'public://recipe/',
      '#required' => TRUE,
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title'  => t('Imagen Detalle'),
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
      '#upload_location'  => 'public://recipe/',
      '#required' => TRUE,
    ];

    $form['steps'] = [
      '#type' => 'item'
    ];

    // Gather the number of names in the form already.
    $num_steps = $form_state->get('num_steps');
    // We have to ensure that there is at least one name field.
    if ($num_steps === NULL) {
      $form_state->set('num_steps', 1);
      $num_steps = 1;
    }

    $form['#tree'] = TRUE;
    $form['steps_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Preparación'),
      '#prefix' => '<div id="steps-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $num_steps; $i++) {
      $form['steps_fieldset']['name'][$i] = [
        '#type' => 'textfield',
        '#size' => 255,
        '#maxlength' => 255,
      ];
    }

    $form['steps_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['steps_fieldset']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar paso'),
      '#submit' => ['::addOnesteps'],
      '#ajax' => [
        'callback' => '::addmorestepsCallback',
        'wrapper' => 'steps-fieldset-wrapper',
      ],
    ];
    // If there is more than one name, add the remove button.
    if ($num_steps > 1) {
      $form['steps_fieldset']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Eliminar paso'),
        '#submit' => ['::removestepsCallback'],
        '#ajax' => [
          'callback' => '::addmorestepsCallback',
          'wrapper' => 'steps-fieldset-wrapper',
        ],
      ];
    }

    $form['ingredients'] = [
      '#type' => 'item'
    ];

    // Gather the number of names in the form already.
    $num_ingredients = $form_state->get('num_ingredients');
    // We have to ensure that there is at least one name field.
    if ($num_ingredients === NULL) {
      $form_state->set('num_ingredients', 1);
      $num_ingredients = 1;
    }

    $form['#tree'] = TRUE;

    $form['ingredients_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Ingredientes'),
      '#prefix' => '<div id="ingredients-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $num_ingredients; $i++) {
      $form['ingredients_fieldset']['name'][$i] = [
        '#type' => 'textfield',
        '#size' => 255,
        '#maxlength' => 255,
      ];
    }

    $form['ingredients_fieldset']['actions'] = [
      '#type' => 'actions',
    ];

    $form['ingredients_fieldset']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar ingrediente'),
      '#submit' => ['::addOneingredients'],
      '#ajax' => [
        'callback' => '::addmoreingredientsCallback',
        'wrapper' => 'ingredients-fieldset-wrapper',
      ],
    ];
    // If there is more than one name, add the remove button.
    if ($num_ingredients > 1) {
      $form['ingredients_fieldset']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Eliminar ingrediente'),
        '#submit' => ['::removeingredientsCallback'],
        '#ajax' => [
          'callback' => '::addmoreingredientsCallback',
          'wrapper' => 'ingredients-fieldset-wrapper',
        ],
      ];
    }

    $form['product'] = array(
      '#type' => 'select',
      '#title' => $this->t('Producto'),
      '#options' => $products,
      '#required' => TRUE,
    );

    $form['PDF'] = [
      '#type' => 'managed_file',
      '#title'  => t('PDF'),
      '#description' => t('Allowed extensions: pdf'),
      '#progress_indicator' => 'bar',
      '#progress_message' => 'Wait ...',
      '#upload_validators' => [
        'file_validate_extensions' => ['pdf'],
        'file_validate_size' => array(5 * 1024 * 1024),
      ],
      '#upload_location'  => 'public://recipe_pdf/',

    ];

    $form['level'] = array(
      '#type' => 'select',
      '#title' => $this->t('Nivel'),
      '#options' => $levels,
      '#required' => TRUE,
    );

    $form['temporality'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Ocasión'),
      '#options' => $temporalitys,
      '#required' => TRUE,
    );

    $form['time'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Tiempo'),
      '#size' => 100,
      '#maxlength' => 100,
      '#required' => TRUE,
    );

    $form['video_link'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('ID Youtube'),
      '#description' => 'For example. URL:https://www.youtube.com/watch?v=gMF7Wp5iVto ID: <strong>gMF7Wp5iVto</strong>',
      '#size' => 25,
      '#maxlength' => 25,
    );

    $form['meta_title'] = array(
      '#type' => 'textfield',
      '#title' => 'Title',
      '#size' => 100,
      '#required' => true,
      '#maxlength' => 100,
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
      '#submit' => array('coronado_recipes_recipe_cancel'),
    );

    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreingredientsCallback(array &$form, FormStateInterface $form_state)
  {
    return $form['ingredients_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneingredients(array &$form, FormStateInterface $form_state)
  {
    $name_field = $form_state->get('num_ingredients');
    $add_button = $name_field + 1;
    $form_state->set('num_ingredients', $add_button);
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
  public function removeingredientsCallback(array &$form, FormStateInterface $form_state)
  {
    $name_field = $form_state->get('num_ingredients');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_ingredients', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'num_ingredients' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }


  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmorestepsCallback(array &$form, FormStateInterface $form_state)
  {
    return $form['steps_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOnesteps(array &$form, FormStateInterface $form_state)
  {
    $name_field = $form_state->get('num_steps');
    $add_button = $name_field + 1;
    $form_state->set('num_steps', $add_button);
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
  public function removestepsCallback(array &$form, FormStateInterface $form_state)
  {
    $name_field = $form_state->get('num_steps');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_steps', $remove_button);
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
    $steps = $form_state->getValue(['steps_fieldset', 'name']);
    $ingredients = $form_state->getValue(['ingredients_fieldset', 'name']);

    $connection = \Drupal::database();
    $image = $form_state->getValue('image')[0];
    $preview = $form_state->getValue('image_preview')[0];
    $pdf = $form_state->getValue('PDF')[0];
    // $file = File::load($fid);
    // file_create_url($file->getFileUri());

    $campos = array(
      'name' => $form_state->getValue('name'),
      'url' => $this->fixForUri($form_state->getValue('url')),
      'description' => $form_state->getValue('description'),
      'image' => $image,
      'image_preview' => $preview,
      'video_link' => $form_state->getValue('video_link'),
      'product' => $form_state->getValue('product'),
      'PDF' => $pdf,
      'level' => $form_state->getValue('level'),
      'temporality' => serialize($form_state->getValue('temporality')),
      'time' => $form_state->getValue('time'),
      'steps' => serialize($steps),
      'ingredients' => serialize($ingredients),
      'meta_title' => $form_state->getValue('meta_title'),
      'meta_description' => $form_state->getValue('meta_description'),
      'meta_keywords' => $form_state->getValue('meta_keywords'),
      'updated_at' => \Drupal::time()->getCurrentTime(),
      'created_at' => \Drupal::time()->getCurrentTime(),
    );

    $connection->insert('coronado_recipes')
      ->fields($campos)
      ->execute();

    drupal_flush_all_caches();

    \Drupal::messenger()->addStatus($this->t('La receta @name fue creada exitosamente', array('@name' => $form_state->getValue('name'))));

    $form_state->setRedirect('coronado_recipes.listRecipe');
  }

  function product()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product', 'p');
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
