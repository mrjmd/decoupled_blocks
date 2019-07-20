<?php

namespace Drupal\pdb_twig;

use Drupal\Core\Utility\ProjectInfo;
use Drupal\pdb\ComponentDiscovery;

/**
 * Discovery service for twig component locale translations.
 */
class ComponentTranslationsDiscovery extends ComponentDiscovery implements ComponentTranslationsDiscoveryInterface {

  /**
   * {@inheritdoc}
   */
  public function getComponentTranslations() {
    $components = $this->getComponents();

    // Get the components that provide locale translations.
    // Follows locale module approach to discover translation projects.
    // See locale_translation_project_list().
    $projects = [];
    $additional_whitelist = [
      'interface translation project',
      'interface translation server pattern',
    ];

    $this->moduleHandler->loadInclude('locale', 'compare.inc');
    $component_data = _locale_translation_prepare_project_list($components, 'pdb');

    $project_info = new ProjectInfo();
    $project_info->processInfoList($projects, $component_data, 'pdb', TRUE, $additional_whitelist);

    return $projects;
  }

}
