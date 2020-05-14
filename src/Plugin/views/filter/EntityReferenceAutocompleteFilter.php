<?php

namespace Drupal\toolkit\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Entity reference autocomplete filter.
 *
 * @ViewsFilter("entity_reference_autocomplete")
 */
class EntityReferenceAutocompleteFilter extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    // Add the value field.
    $form['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#size' => 30,
      '#default_value' => $this->value,
      '#autocomplete_route_name' => 'toolkit.views_entity_autocomplete',
      '#autocomplete_route_parameters' => [
        'target_type' => $this->configuration['target_entity_type_id'],
        'selection_handler' => $this->configuration['field_handler'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Check for a value.
    if (!empty($this->value[0])) {
      // Extract the entity ID.
      $matches = [];
      preg_match_all("/\(([A-Za-z0-9_]+)\)$/", $this->value[0], $matches);

      // Check for a match.
      if (!empty($matches[1])) {
        // Add the filter.
        $this->ensureMyTable();
        $this->query->addWhere($this->options['group'], "$this->tableAlias.$this->realField", array_pop($matches[1]), $this->operator);
      }
    }
  }

}
