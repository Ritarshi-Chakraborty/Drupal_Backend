<?php

namespace Drupal\rgb_color_picker\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\Markup;

/**
 * @FieldFormatter(
 *   id = "rgb_text_formatter",
 *   label = @Translation("RGB Text Formatter"),
 *   field_types = {"rgb_color"}
 * )
 */
class TextFormatter extends FormatterBase {

    /**
     * {@inheritDoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode) {
        $elements = [];
        foreach ($items as $delta => $item) {
            $hex = sprintf('#%02X%02X%02X', $item->r, $item->g, $item->b);
            $elements[$delta] = [
                '#markup' => Markup::create(
                    '<h4 style="color:'.$hex.';">'.$hex.'</h4>'
                )
            ];
        }
        return $elements;
    }
}
