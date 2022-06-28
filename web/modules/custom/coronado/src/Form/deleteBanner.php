<?php

namespace Drupal\coronado\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements an example form.
 */
class deleteBanner extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'coronado_deleteBanner';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = null)
  {

    $banner = $this->get_banner($arg);
    $form['disclaimer'] = array(
      '#markup' => 'El banner a eliminar es ' . $banner['title'] . '.   <b><i>Esta acción no se podrá deshacer.</i></b> <br><br>',
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete')
    );

    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('coronado_banner_cancel'),
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
    $connection->delete('coronado_home')
      ->condition('id', $id)
      ->execute();

    drupal_flush_all_caches();

    \Drupal::messenger()->addStatus("Se ha eliminado el banner");

    $form_state->setRedirect('coronado.listBanner');
  }

  function get_banner($arg)
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_home', 'p');
    $query->fields('p', ['title']);
    $query->condition('id', $arg);
    $result = $query->execute();
    return $result->fetchAssoc();
  }
}
