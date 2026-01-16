<?php

namespace Drupal\rgb_color_picker\Plugin\Field\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "rgb_color_separate",
 *   label = @Translation("Separate R/G/B"),
 *   field_types = {"rgb_color"}
 * )
 */
class RgbSeparateWidget extends WidgetBase {

    /**
     * {@inheritDoc}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
        $value = $items[$delta];
        $element = [];
        $element['r'] = [
            '#type' => 'number',
            '#min' => 0,
            '#max' => 255,
            '#default_value' => $value->r ?? 0,
            '#title' => $this->t('R')
        ];
        $element['g'] = [
            '#type' => 'number',
            '#min' => 0,
            '#max' => 255,
            '#default_value' => $value->g ?? 0,
            '#title' => $this->t('G')
        ];
        $element['b'] = [
            '#type' => 'number',
            '#min' => 0,
            '#max' => 255,
            '#default_value' => $value->b ?? 0,
            '#title' => $this->t('B')
        ];
        return $element;
    }
}
