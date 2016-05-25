<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.0.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Models\Traits;

use Flipbox\Craft3\Spark\Helpers\ModelHelper;

trait ModelWithHandleTrait
{

    /**
     * @var string Handle
     */
    public $handle;

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @inheritdoc
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return [
            [
                ['handle'],
                'craft\\app\\validators\\Handle',
                'reservedWords' => [
                    'id',
                    'uid',
                ]
            ],
            [
                ['handle'],
                'unique'
            ],
            [
                ['handle'],
                'required'
            ],
            [
                ['handle'],
                'string',
                'max' => 150
            ],
            [
                [
                    'handle'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_POPULATE,
                    ModelHelper::SCENARIO_SAVE,
                    ModelHelper::SCENARIO_INSERT,
                    ModelHelper::SCENARIO_UPDATE
                ]
            ]
        ];
    }

}
