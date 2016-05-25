<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Models\Traits;

use Flipbox\Craft3\Spark\Helpers\ModelHelper;

trait ModelWithStateTrait
{

    /**
     * @var boolean Enabled
     */
    public $enabled;

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return (bool)$this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return !$this->isEnabled();
    }

    /**
     * @inheritdoc
     */
    public function toEnabled()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toDisabled()
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return [
            [
                [
                    'enabled'
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
