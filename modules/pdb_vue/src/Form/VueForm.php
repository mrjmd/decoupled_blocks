<?php

namespace Drupal\pdb_vue\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class VueForm.
 *
 * @package Drupal\pdb_vue\Form
 */
class VueForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pdb_vue.settings'];
  }

  /**
   * {@inheridoc}
   */
  public function getFormId() {
    return 'pdb_vue_form';
  }

  /**
   * {@inheridoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pdb_vue.settings');
    $form['development_mode'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Development Mode'),
      '#description' => $this->t('Checking the box enables development mode'),
      '#default_value' => $config->get('development_mode'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // Set variables based on form values.
    $development_mode = $form_state->getValue('development_mode');
    // Get the config object.
    $config = \Drupal::service('config.factory')
      ->getEditable('pdb_vue.settings');
    // Set the values the user submitted in the form.
    $config->set('development_mode', $development_mode);
    $config->save();

    // Clear caches so that it will pick up the changes to vue library
    drupal_flush_all_caches();
  }

}
