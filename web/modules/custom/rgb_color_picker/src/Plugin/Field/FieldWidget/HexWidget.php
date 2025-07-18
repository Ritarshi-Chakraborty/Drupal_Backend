<?php

namespace Drupal\rgb_color_picker\Plugin\Field\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "rgb_color_hex",
 *   label = @Translation("Hexcode"),
 *   field_types = {"rgb_color"}
 * )
 */
class HexWidget extends WidgetBase {

    /**
     * {@inheritDoc}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
        // Grabs the current value for this field instance.
        $value = $items[$delta];
        $hex = '';
        // If there's a saved value, it formats the RGB integers into a hex string, using uppercase hex digits.
        if (!$items[$delta]->isEmpty()) {
            $hex = sprintf('#%02X%02X%02X', $value->r ?? 0, $value->g ?? 0, $value->b ?? 0);
        }
        // Adds a text field element
        $element += [
            '#type' => 'textfield',
            '#default_value' => $hex,
            '#size' => 7,
            '#maxlength' => 7,
            '#element_validate' => [
                [get_class($this), 'validateHex'],
            ],
        ];
        // This means the form will display just one field (named `value`) under the widget.
        return ['value' => $element];
    }

    /**
     * {@inheritDoc}
     */
    public static function validateHex(&$element, FormStateInterface $form_state, &$complete_form) {
        // Get the submitted value
        $value = $form_state->getValue($element['#parents']);
        // If the value is empty, skip validation.
        if (empty($value)) {
            return;
        }
        // Validates that the input is exactly 6 hex digits with or without the `#`
        if (preg_match('/^#?([0-9a-fA-F]{6})$/', $value, $matches)) {
            // Converts the hex string into an array of 3 integers: [r, g, b]
            $hex = $matches[1];
            $rgb = sscanf($hex, '%02x%02x%02x');
            // #parents holds the hierarchy of form input names (like `field_rgb_color[0][value]`)
            $parents = $element['#parents'];
            // Removes value to prepare for setting r, g, b individually
            array_pop($parents);
            // Sets the individual RGB values (r, g, b) into the form state
            $form_state->setValue(array_merge($parents, ['r']), $rgb[0]);
            $form_state->setValue(array_merge($parents, ['g']), $rgb[1]);
            $form_state->setValue(array_merge($parents, ['b']), $rgb[2]);
        }
        else {
            $form_state->setError($element, t('Enter a valid 6-digit hex color.'));
        }
    }
}
