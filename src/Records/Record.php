<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.1.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Records;

use craft\app\db\ActiveRecord as BaseRecord;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;

abstract class Record extends BaseRecord implements Interfaces\RecordInterface
{

    /**
     * @var string
     */
    protected static $tableName = '';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return self::$tableName;
    }

    /*******************************************
     * RULES
     *******************************************/

    /**
     * @inheritdoc
     */
    public function rules()
    {

        // Base rules
        $rules = parent::rules();

        // Add dateUpdated rule if property exists
        if ($this->hasProperty('dateUpdated')) {
            $rules[] = [
                [
                    'dateUpdated'
                ],
                'safe',
                'on' => [
                    RecordHelper::SCENARIO_INSERT,
                    RecordHelper::SCENARIO_UPDATE
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
                    RecordHelper::SCENARIO_INSERT
                ]
            ];
        }

        // Add uid rule if property exists
        if ($this->hasProperty('uid')) {
            $rules[] = [
                [
                    'uid'
                ],
                'safe',
                'on' => [
                    RecordHelper::SCENARIO_INSERT
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

        return RecordHelper::create($config);

    }

    /**
     * @inheritdoc
     */
    public static function className()
    {
        return get_called_class();
    }

}
