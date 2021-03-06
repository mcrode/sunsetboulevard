<?php

namespace Drupal\blazy\Dejavu;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\blazy\BlazyDefault;

/**
 * Base class for blazy video embed field formatters.
 *
 * This means Slick Video which depends on VEF is deprecated for Slick Media
 * at Blazy 8.2.x with core Media only.
 *
 * @deprecated BlazyVideoBase is deprecated in blazy:8.x-2.0 and will be removed
 *   from blazy:9.x-1.x. Use
 *   \Drupal\blazy\Plugin\Field\FieldFormatter\BlazyMediaFormatterBase instead.
 * @see https://www.drupal.org/node/3103018
 */
abstract class BlazyVideoBase extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return BlazyDefault::extendedSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element    = [];
    $definition = $this->getScopedFormElements();

    $definition['_views'] = isset($form['field_api_classes']);

    $this->admin()->buildSettingsForm($element, $definition);
    $element['media_switch']['#options']['media'] = $this->t('Image to iFrame');

    return $element;
  }

  /**
   * Defines the scope for the form elements.
   */
  public function getScopedFormElements() {
    $field       = $this->fieldDefinition;
    $entity_type = $field->getTargetEntityTypeId();
    $target_type = $this->getFieldSetting('target_type');

    return [
      'background'        => TRUE,
      'breakpoints'       => BlazyDefault::getConstantBreakpoints(),
      'current_view_mode' => $this->viewMode,
      'entity_type'       => $entity_type,
      'field_name'        => $this->fieldDefinition->getName(),
      'image_style_form'  => TRUE,
      'media_switch_form' => TRUE,
      'multimedia'        => TRUE,
      'plugin_id'         => $this->getPluginId(),
      'settings'          => $this->getSettings(),
      'target_type'       => $target_type,
      'thumb_positions'   => TRUE,
      'nav'               => TRUE,
    ];
  }

}
