<?php

namespace Drupal\rgb_color_picker\Plugin\Field\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "rgb_color_picker",
 *   label = @Translation("Color picker"),
 *   field_types = {"rgb_color"}
 * )
 */
class ColorPickerWidget extends WidgetBase {

    /**
     * {@inheritDoc}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
        $r = $items[$delta]->r ?? 0;
        $g = $items[$delta]->g ?? 0;
        $b = $items[$delta]->b ?? 0;
        $hex = sprintf('#%02X%02X%02X', $r, $g, $b);
        $element = [
            '#type' => 'color',
            '#title' => 'Color Picker',
            '#default_value' => $hex,
        ];
        return $element;
    }

    /**
     * {@inheritDoc}
     */
    public function extractFormValues(FieldItemListInterface $items, $form, FormStateInterface $form_state) {
        // Retrieves the submitted value for this field from the form state
        $value = $form_state->getValue($this->fieldDefinition->getName());
        // Checks if a value was submitted
        if (isset($value[0])) {
            // Removes the # symbol from the hex string
            $hex = ltrim($value[0], '#');
            foreach ($items as $item) {
                // Extracts 2-character hex strings and converts them to decimal.
                $item->r = hexdec(substr($hex, 0, 2));
                $item->g = hexdec(substr($hex, 2, 2));
                $item->b = hexdec(substr($hex, 4, 2));
            }
        }
    }
}
