<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace Flipbox\Craft3\Spark\Elements;

use craft\app\base\Element as BaseElement;
use Flipbox\Craft3\Spark\Helpers\ElementHelper;

abstract class Element extends BaseElement implements Interfaces\ElementWithIdInterface
{

    use Traits\ElementWithIdTrait {
        rules as _traitRules;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return array_merge(
            parent::rules(),
            $this->_traitRules()
        );

    }

    /**
     * @inheritdoc
     */
    public static function create($config = [])
    {

        // Set our class
        $config['class'] = static::className();

        return ElementHelper::create($config);

    }

    /**
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

}
