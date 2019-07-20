<?php

namespace Drupal\pdb_twig\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to import twig component translations.
 */
class ImportTranslationsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pdb_twig_import_translations';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#markup' => $this->t('Import all available translations for Twig components.'),
    ];

    // After a string is translated via the UI, it is considered a custom
    // translation and can not be overriden. This check allows that.
    $form['overwrite_customized'] = [
      '#title' => $this->t('Overwrite existing customized translations'),
      '#description' => $this->t('This will override translated string via UI, use with caution.'),
      '#type' => 'checkbox',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import Translations'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Use custom service to only import components that provide translations.
    $discovery = \Drupal::service('pdb_twig.component_translations_discovery');
    $projects = array_keys($discovery->getComponentTranslations());

    // This needs to be configurable on the form.
    $langcodes = ['es'];

    \Drupal::moduleHandler()->loadInclude('locale', 'fetch.inc');
    $options = _locale_translation_default_update_options();
    $options['overwrite_options']['customized'] = (bool) $form_state->getValue('overwrite_customized');

    // Use the locale translation batch.
    $batch = locale_translation_batch_update_build($projects, $langcodes, $options);

    // Add a check operation as the second operation of the batch.
    // This is required to re-import an already imported translation PO file.
    array_unshift($batch['operations'], [[self::class, 'importTranslationsCheckFiles'], [$projects, $langcodes]]);

    // Add a check operation at the beginning of the batch.
    // This is required to discover new translation projects.
    array_unshift($batch['operations'], [[self::class, 'importTranslationsCheckProjects'], []]);

    // Add a refresh operation.
    // This is required to make changes avialable without clearing the cache.
    $batch['operations'][] = [[self::class, 'importTranslationsRefresh'], [$langcodes]];
    batch_set($batch);
  }

  /**
   * Batch operation that checks for translation projects.
   *
   * This makes possible to discover new projects providing translations.
   *
   * @param array $context
   *   The batch context.
   */
  public static function importTranslationsCheckProjects(&$context) {
    // Check for projects providing translations.
    \Drupal::moduleHandler()->loadInclude('locale', 'compare.inc');
    locale_translation_build_projects();
  }

  /**
   * Batch operation that checks for projects translation PO files.
   *
   * This makes possible to re-import already imported PO files.
   *
   * @param array $projects
   *   The project names (pdb twig components).
   * @param array $langcodes
   *   The language codes.
   * @param array $context
   *   The batch context.
   */
  public static function importTranslationsCheckFiles($projects, $langcodes, &$context) {
    // Check if the given projects have new translations.
    // The check is based on PO file last modified timestamp.
    \Drupal::moduleHandler()->loadInclude('locale', 'compare.inc');
    locale_translation_check_projects_local($projects, $langcodes);
    \Drupal::state()->set('locale.translation_last_checked', REQUEST_TIME);
  }

  /**
   * Batch operation that refreshes translations for given languages.
   *
   * This makes imported PO changes available without clearing the cache.
   *
   * @param array $langcodes
   *   The language codes.
   * @param array $context
   *   The batch context.
   */
  public static function importTranslationsRefresh($langcodes, &$context) {
    // Refresh all the locale translations and clear the translation caches.
    _locale_refresh_translations($langcodes);
  }

}
