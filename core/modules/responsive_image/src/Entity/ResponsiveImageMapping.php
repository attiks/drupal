<?php

/**
 * @file
 * Contains \Drupal\responsive_image\Entity\ResponsiveImageMapping.
 */

namespace Drupal\responsive_image\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\responsive_image\ResponsiveImageMappingInterface;

/**
 * Defines the responsive image mapping entity.
 *
 * @ConfigEntityType(
 *   id = "responsive_image_mapping",
 *   label = @Translation("Responsive image mapping"),
 *   controllers = {
 *     "list_builder" = "Drupal\responsive_image\ResponsiveImageMappingListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\responsive_image\ResponsiveImageMappingForm",
 *       "add" = "Drupal\responsive_image\ResponsiveImageMappingForm",
 *       "delete" = "Drupal\responsive_image\Form\ResponsiveImageMappingDeleteForm",
 *       "duplicate" = "Drupal\responsive_image\ResponsiveImageMappingForm"
 *     }
 *   },
 *   list_path = "admin/config/media/responsive-image-mapping",
 *   admin_permission = "administer responsive images",
 *   config_prefix = "mappings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "responsive_image.mapping_page_edit",
 *     "duplicate-form" = "responsive_image.mapping_page_duplicate"
 *   }
 * )
 */
class ResponsiveImageMapping extends ConfigEntityBase implements ResponsiveImageMappingInterface {

  /**
   * The responsive image ID (machine name).
   *
   * @var string
   */
  public $id;

  /**
   * The responsive image label.
   *
   * @var string
   */
  public $label;

  /**
   * The responsive image mappings.
   *
   * @var array
   */
  protected $mappings = array();

  /**
   * The responsive image breakpoint group.
   *
   * @var array
   */
  protected $breakpointGroup = '';

  /**
   * Overrides Drupal\config\ConfigEntityBase::__construct().
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->loadBreakpointGroup();
    $this->loadAllMappings();
  }

  /**
   * Overrides Drupal\Core\Entity::save().
   */
  public function save() {
    // Only save the keys, but return the full objects.
    $breakpoint_group = $this->getBreakpointGroup();
    if ($breakpoint_group && is_array($breakpoint_group)) {
      $this->setBreakpointGroup($breakpoint_group['id']);
    }

    // Split the breakpoint ids into their different parts, as dots as
    // identifiers are not possible.
    $loaded_mappings = $this->mappings;
    $this->mappings = array();
    foreach ($loaded_mappings as $breakpoint_id => $mapping) {
      list($source_type, $source, $name) = explode('.', $breakpoint_id);
      $this->mappings[$source_type][$source][$name] = $mapping;
    }

    parent::save();
    $this->loadBreakpointGroup();
    $this->loadAllMappings();
  }

  /**
   * Implements \Drupal\Core\Entity\EntityInterface::createDuplicate().
   */
  public function createDuplicate() {
    return entity_create('responsive_image_mapping', array(
      'id' => '',
      'label' => t('Clone of !label', array('!label' => check_plain($this->label()))),
      'mappings' => $this->getMappings(),
    ));
  }

  /**
   * Loads the breakpoint group.
   */
  protected function loadBreakpointGroup() {
    if ($this->getBreakpointGroup()) {
      $breakpoint_group = breakpoint_load_breakpoint_group($this->getBreakpointGroup());
      $this->setBreakpointGroup($breakpoint_group);
    }
  }

  /**
   * Loads all mappings and removes non-existing ones.
   */
  protected function loadAllMappings() {
    $loaded_mappings = $this->getMappings();
    $all_mappings = array();
    if ($breakpoint_group = $this->getBreakpointGroup()) {
      $breakpoints = $breakpoint_group['breakpoints'];
      foreach ($breakpoints as $breakpoint_id => $breakpoint) {
        // Get the components of the breakpoint ID to match the format of the
        // configuration file.
        list($source_type, $source, $name) = explode('.', $breakpoint_id);

        // Get the mapping for the default multiplier.
        $all_mappings[$breakpoint_id]['1x'] = '';
        if (isset($loaded_mappings[$source_type][$source][$name]['1x'])) {
          $all_mappings[$breakpoint_id]['1x'] = $loaded_mappings[$source_type][$source][$name]['1x'];
        }

        // Get the mapping for the other multipliers.
        if (isset($breakpoint['multipliers']) && !empty($breakpoint['multipliers'])) {
          foreach ($breakpoint['multipliers'] as $multiplier => $status) {
            if ($status) {
              $all_mappings[$breakpoint_id][$multiplier] = '';
              if (isset($loaded_mappings[$source_type][$source][$name][$multiplier])) {
                $all_mappings[$breakpoint_id][$multiplier] = $loaded_mappings[$source_type][$source][$name][$multiplier];
              }
            }
          }
        }
      }
    }
    $this->setMappings($all_mappings);
  }

  /**
   * {@inheritdoc}
   */
  public function hasMappings() {
    $mapping_found = FALSE;
    foreach ($this->getMappings() as $multipliers) {
      foreach ($multipliers as $mapping_definition) {
        if (!$this::isEmptyMappingDefinition($mapping_definition)) {
          $mapping_found = TRUE;
          break 2;
        }
      }
    }
    return $mapping_found;
  }

  /**
   * {@inheritdoc}
   */
  public static function isEmptyMappingDefinition($mapping_definition) {
    if (!empty($mapping_definition)) {
      switch ($mapping_definition['mapping_type']) {
        case 'sizes':
          if ($mapping_definition['sizes'] && array_filter($mapping_definition['sizes_image_styles'])) {
            return FALSE;
          }
          break;
        case 'image_style':
          if ($mapping_definition['image_style']) {
            return FALSE;
          }
          break;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $names = array(
      'id',
      'uuid',
      'label',
      'mappings',
      'breakpointGroup',
    );
    $properties = array();
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setMappings(array $mappings) {
    $this->set('mappings', $mappings);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappings() {
    return $this->get('mappings');
  }

  /**
   * {@inheritdoc}
   */
  public function setBreakpointGroup($breakpoint_group) {
    $this->set('breakpointGroup', $breakpoint_group);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBreakpointGroup() {
    return $this->get('breakpointGroup');
  }
}
