<?php

namespace Drupal\pdb_twig\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form with Twig components config.
 */
class TwigConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pdb_twig.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pdb_twig_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pdb_twig.settings');

    $form['block_full'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Full Block'),
      '#description' => $this->t('Render full blocks with contextual links.'),
      '#default_value' => $config->get('block_full'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = \Drupal::service('config.factory')->getEditable('pdb_twig.settings');

    $block_full = $form_state->getValue('block_full');
    $config->set('block_full', $block_full);
    $config->save();
  }

}
