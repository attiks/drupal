<?php

/**
 * @file
 * Contains Drupal\responsive_image\ResponsiveImageForm.
 */

namespace Drupal\responsive_image;

use Drupal\Core\Entity\EntityForm;
use \Drupal\Component\Utility\String;

/**
 * Form controller for the responsive image edit/add forms.
 */
class ResponsiveImageMappingForm extends EntityForm {

  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   * @param \Drupal\responsive_image\ResponsiveImageMappingInterface $responsive_image_mapping
   *   The entity being edited.
   *
   * @return array
   *   The array containing the complete form.
   */
  public function form(array $form, array &$form_state) {
    if ($this->operation == 'duplicate') {
      $form['#title'] = $this->t('<em>Duplicate responsive image mapping</em> @label', array('@label' => $this->entity->label()));
      $this->entity = $this->entity->createDuplicate();
    }
    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit responsive image mapping</em> @label', array('@label' => $this->entity->label()));
    }

    /** @var \Drupal\responsive_image\ResponsiveImageMappingInterface $responsive_image_mapping */
    $responsive_image_mapping = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $responsive_image_mapping->label(),
      '#description' => $this->t("Example: 'Hero image' or 'Author image'."),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $responsive_image_mapping->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\responsive_image\Entity\ResponsiveImageMapping::load',
        'source' => array('label'),
      ),
      '#disabled' => (bool) $responsive_image_mapping->id() && $this->operation != 'duplicate',
    );

    if ((bool) $responsive_image_mapping->id() && $this->operation != 'duplicate') {
      $description = $this->t('Select a breakpoint group from the enabled themes.') . ' ' . $this->t("Warning: if you change the breakpoint group you lose all your selected mappings.");
    }
    else {
      $description = $this->t('Select a breakpoint group from the enabled themes.');
    }
    $form['breakpointGroup'] = array(
      '#type' => 'select',
      '#title' => $this->t('Breakpoint group'),
      '#default_value' => ($responsive_image_mapping->getBreakpointGroup() != '') ? $responsive_image_mapping->getBreakpointGroup()['id'] : '',
      '#options' => breakpoint_group_select_options(),
      '#required' => TRUE,
      '#description' => $description,
    );

    $image_styles = image_style_options(TRUE);
    foreach ($responsive_image_mapping->getMappings() as $breakpoint_id => $mapping) {
      foreach ($mapping as $multiplier => $mapping_definition) {
        $breakpoint = $responsive_image_mapping->getBreakpointGroup()['breakpoints'][$breakpoint_id];
        $label = $multiplier . ' ' . $breakpoint['name'] . ' [' . $breakpoint['mediaQuery'] . ']';
        $form['mappings'][$breakpoint_id][$multiplier] = array(
          '#type' => 'details',
          '#title' => String::checkPlain($label),
        );
        $form['mappings'][$breakpoint_id][$multiplier]['mapping_type'] = array(
          '#title' => t('Type'),
          '#type' => 'radios',
          '#options' => array(
            '_none' => t('Do not use this breakpoint'),
            'image_style' => t('Use image styles'),
            'sizes' => t('Use the sizes attribute'),
          ),
          '#default_value' => isset($mapping_definition['mapping_type']) ? $mapping_definition['mapping_type'] : '_none',
        );
        $form['mappings'][$breakpoint_id][$multiplier]['image_style'] = array(
          '#type' => 'select',
          '#title' => t('Image style'),
          '#options' => $image_styles,
          '#default_value' => isset($mapping_definition['image_style']) ? $mapping_definition['image_style'] : array(),
          '#description' => $this->t('Select an image style for this breakpoint.'),
          '#states' => array(
            'visible' => array(
              ':input[name="mappings[' . $breakpoint_id . '][' . $multiplier . '][mapping_type]"]' => array('value' => 'image_style'),
            ),
          ),
        );
        $form['mappings'][$breakpoint_id][$multiplier]['sizes'] = array(
          '#type' => 'textfield',
          '#title' => t('Sizes'),
          '#default_value' => isset($mapping_definition['sizes']) ? $mapping_definition['sizes'] : '',
          '#description' => $this->t('Enter the value for the sizes attribute (e.g. "(min-width:700px) 700px, 100vw").'),
          '#states' => array(
            'visible' => array(
              ':input[name="mappings[' . $breakpoint_id . '][' . $multiplier . '][mapping_type]"]' => array('value' => 'sizes'),
            ),
          ),
        );
        $form['mappings'][$breakpoint_id][$multiplier]['sizes_image_styles'] = array(
          '#title' => t('Image styles'),
          '#type' => 'checkboxes',
          '#options' => array_diff_key($image_styles, array('' => '')),
          '#default_value' => isset($mapping_definition['sizes_image_styles']) ? $mapping_definition['sizes_image_styles'] : array(),
          '#states' => array(
            'visible' => array(
              ':input[name="mappings[' . $breakpoint_id . '][' . $multiplier . '][mapping_type]"]' => array('value' => 'sizes'),
            ),
          ),
        );

      }
    }

    $form['#tree'] = TRUE;

    return parent::form($form, $form_state, $responsive_image_mapping);
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    /** @var \Drupal\responsive_image\ResponsiveImageMappingInterface $responsive_image_mapping */
    $responsive_image_mapping = $this->entity;

    // Only validate on edit.
    if (isset($form_state['values']['mappings'])) {
      $responsive_image_mapping->setMappings($form_state['values']['mappings']);

      // Check if another breakpoint group is selected.
      if ($form_state['values']['breakpointGroup'] != $form_state['complete_form']['breakpointGroup']['#default_value']) {
        // Remove the mappings.
        unset($form_state['values']['mappings']);
      }
      // Make sure at least one mapping is defined.
      elseif (!$responsive_image_mapping->isNew() && !$responsive_image_mapping->hasMappings()) {
        $this->setFormError('mappings', $form_state, $this->t('Please select at least one mapping.'));
      }
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, array &$form_state) {
    /** @var \Drupal\responsive_image\ResponsiveImageMappingInterface $responsive_image_mapping */
    $responsive_image_mapping = $this->entity;
    $responsive_image_mapping->save();

    watchdog('responsive_image', 'Responsive image mapping @label saved.', array('@label' => $responsive_image_mapping->label()), WATCHDOG_NOTICE);
    drupal_set_message($this->t('Responsive image mapping %label saved.', array('%label' => $responsive_image_mapping->label())));

    // Redirect to edit form after creating a new mapping or after selecting
    // another breakpoint group.
    if (!$responsive_image_mapping->hasMappings()) {
      $form_state['redirect_route'] = array(
        'route_name' => 'responsive_image.mapping_page_edit',
        'route_parameters' => array(
          'responsive_image_mapping' => $responsive_image_mapping->id(),
        ),
      );
    }
    else {
      $form_state['redirect_route']['route_name'] = 'responsive_image.mapping_page';
    }
  }

}
