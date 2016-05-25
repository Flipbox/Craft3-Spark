<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Models;

use Flipbox\Craft3\Spark\Helpers\ModelHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use yii\base\Model as BaseModel;

abstract class Model extends BaseModel implements Interfaces\ModelInterface
{

    /**
     * @inheritdoc
     */
    public function rules()
    {

        // Base rules
        $rules = parent::rules();

        // Add UID rule if property exists
        if ($this->hasProperty('uid')) {
            $rules[] = [
                [
                    'uid'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_POPULATE
                ]
            ];
        }

        // Add dateCreated rule if property exists
        if ($this->hasProperty('dateCreated')) {
            $rules[] = [
                [
                    'dateCreated'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_POPULATE
                ]
            ];
        }

        // Add dateUpdated rule if property exists
        if ($this->hasProperty('dateUpdated')) {
            $rules[] = [
                [
                    'dateUpdated'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_POPULATE,
                    ModelHelper::SCENARIO_SAVE,
                    ModelHelper::SCENARIO_UPDATE
                ]
            ];
        }

        return $rules;

    }

    /**
     * @inheritdoc
     */
    public static function create($config = [])
    {

        // Set our class
        $config['class'] = static::className();

        return ModelHelper::create($config);

    }

    /**
     * @param ModelInterface $model
     * @return Model
     */
    public static function copy(ModelInterface $model)
    {
        return ModelHelper::copy($model);
    }

    /**
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

}