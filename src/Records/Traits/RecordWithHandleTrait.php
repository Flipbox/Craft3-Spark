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

namespace Flipbox\Craft3\Spark\Records\Traits;

use Flipbox\Craft3\Spark\Helpers\RecordHelper;

trait RecordWithHandleTrait
{

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
                    RecordHelper::SCENARIO_SAVE,
                    RecordHelper::SCENARIO_INSERT,
                    RecordHelper::SCENARIO_UPDATE
                ]
            ]
        ];
    }

}
