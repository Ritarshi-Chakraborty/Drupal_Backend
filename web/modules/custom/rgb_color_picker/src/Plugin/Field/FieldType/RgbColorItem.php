<?php

namespace Drupal\rgb_color_picker\Plugin\Field\FieldType;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "rgb_color",
 *   label = @Translation("RGB Color"),
 *   description = @Translation("Stores an RGB color."),
 *   default_widget = "rgb_color_hex",
 *   default_formatter = "rgb_text_formatter"
 * )
 */
class RgbColorItem extends FieldItemBase {
    
    /**
     * {@inheritDoc}
     */
    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
        $properties['r'] = DataDefinition::create('integer')->setLabel(t('Red'))->setRequired(TRUE);
        $properties['g'] = DataDefinition::create('integer')->setLabel(t('Green'))->setRequired(TRUE);
        $properties['b'] = DataDefinition::create('integer')->setLabel(t('Blue'))->setRequired(TRUE);
        return $properties;
    }

    /**
     * {@inheritDoc}
     */
    public static function schema(FieldStorageDefinitionInterface $field_definition) {
        return [
            'columns' => [
                'r' => ['type'=>'int', 'unsigned'=>TRUE, 'size'=>'tiny'],
                'g' => ['type'=>'int', 'unsigned'=>TRUE, 'size'=>'tiny'],
                'b' => ['type'=>'int', 'unsigned'=>TRUE, 'size'=>'tiny'],
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty() {
        $r = $this->get('r')->getValue();
        $g = $this->get('g')->getValue();
        $b = $this->get('b')->getValue();
        return $r === NULL && $g === NULL && $b === NULL;
    }
}
