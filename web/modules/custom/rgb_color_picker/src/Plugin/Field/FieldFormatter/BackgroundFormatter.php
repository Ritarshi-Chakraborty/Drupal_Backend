<?php

namespace Drupal\rgb_color_picker\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\Markup;

/**
 * @FieldFormatter(
 *   id = "rgb_background_formatter",
 *   label = @Translation("RGB Backgorund Formatter"),
 *   field_types = {"rgb_color"}
 * )
 */
class BackgroundFormatter extends FormatterBase {

    /**
     * {@inheritDoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode) {
        $elements = [];
        foreach ($items as $delta => $item) {
            $hex = sprintf('#%02X%02X%02X', $item->r, $item->g, $item->b);
            $elements[$delta] = [
                '#markup' => Markup::create(
                    '<div style="background-color: '.$hex.'; padding: 3rem;">'.$hex.'</div>'
                )
            ];
        }
        return $elements;
    }
}
