<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.0.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace flipbox\craft\spark\helpers;

use craft\app\models\FieldLayout;

class FieldLayoutHelper
{
    public static function getFieldByHandle(FieldLayout $fieldLayout, $handle)
    {
        foreach($fieldLayout->getFields() as $field){

            if($handle === $field->handle){

                return $field;

            }

        }

        return null;

    }

}
