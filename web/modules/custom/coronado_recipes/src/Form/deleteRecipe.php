<?php

namespace Drupal\coronado_recipes\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements an example form.
 */
class deleteRecipe extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'coronado_recipes_deleteRecipe';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = null)
  {
    $recipe = $this->get_recipe($arg);
    $form['disclaimer'] = array(
      '#markup' => 'La receta a eliminar es ' . $recipe['name'] . '. <b><i>Esta acción no se podrá deshacer.</i></b> <br><br>',
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete')
    );

    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('coronado_recipes_recipe_cancel'),
    );

    $form['idregistro'] = array(
      '#type' => 'hidden',
      '#value' => $arg
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
    $id = $form_state->getValue('idregistro');
    $connection = \Drupal::database();
    $connection->delete('coronado_recipes')
      ->condition('id', $id)
      ->execute();

    drupal_flush_all_caches();

    \Drupal::messenger()->addStatus("Se ha eliminado la receta");

    $form_state->setRedirect('coronado_recipes.listRecipe');
  }

  function get_recipe($arg)
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_recipes', 'p');
    $query->fields('p', ['name']);
    $query->condition('id', $arg);
    $result = $query->execute();
    return $result->fetchAssoc();
  }
}
