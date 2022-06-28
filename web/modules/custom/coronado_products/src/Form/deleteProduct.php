<?php

namespace Drupal\coronado_products\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements an example form.
 */
class deleteProduct extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'coronado_products_deleteProduct';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = null)
  {
    $product = $this->get_product($arg);
    $form['disclaimer'] = array(
      '#markup' => 'El producto a eliminar es ' . $product['name'] . '.  <b><i>Esta acción no se podrá deshacer.</i></b> <br><br>',
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete')
    );

    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('coronado_products_product_cancel'),
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
    $connection->delete('coronado_product')
      ->condition('id', $id)
      ->execute();

    drupal_flush_all_caches();

    \Drupal::messenger()->addStatus("Se ha eliminado el producto");

    $form_state->setRedirect('coronado_products.listProducts');
  }

  function get_product($arg)
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product', 'p');
    $query->fields('p', ['name']);
    $query->condition('id', $arg);
    $result = $query->execute();
    return $result->fetchAssoc();
  }
}
