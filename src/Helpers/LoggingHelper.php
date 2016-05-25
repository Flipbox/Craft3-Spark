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

namespace Flipbox\Craft3\Spark\Helpers;

use Craft;
use craft\app\web\Request;
use Flipbox\Craft3\Spark\Modules\Interfaces\LoggableInterface;
use yii\base\Module;
use yii\log\Logger;

/**
 * LoggingHelper.
 *
 * @author Flipbox Factory <hello@flipboxdigital.com>
 *
 * @version $Id$
 */
class LoggingHelper
{
    /**
     * prefixSessionData.
     *
     * @return callable
     */
    public static function prefixSessionData()
    {
        return function ($message) {
            $request = Craft::$app->getRequest();
            $ip = $request instanceof Request ? $request->getUserIP() : '-';

            /* @var $user \craft\app\web\User */
            $user = Craft::$app->has('user', true) ? Craft::$app->get('user') : null;
            if ($user && ($identity = $user->getIdentity())) {
                $userID = $identity->getId() . ':' . $identity->username;
            } else {
                $userID = '-';
            }

            /* @var $session \yii\web\Session */
            $session = Craft::$app->has('session', true) ? Craft::$app->get('session') : null;
            $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

            return "[$ip][$userID][$sessionID]";
        };
    }

    /**
     * isDebugModeEnabled.
     *
     * @param Module $module
     *
     * @return bool
     */
    public static function isDebugModeEnabled(Module $module)
    {
        return Craft::$app->getConfig()->get('devMode') || ($module instanceof LoggableInterface && $module->isDebugModeEnabled());
    }

    /**
     * getDispatchDefinition.
     *
     * @param Module $module
     * @param array $config
     *
     * @return array
     */
    public static function getDispatchDefinition(Module $module, array $config = [])
    {

        $configService = Craft::$app->getConfig();

        $defaultConfig = [
            'logger' =>
            /* '\yii\log\Logger', */
                new Logger(),
            'class' => '\yii\log\Dispatcher',
            'targets' => [
                /* 'file' => $fileTarget, */
                'file' => [
                    'class' => '\craft\app\log\FileTarget',
                    'levels' => array_merge(
                        ['error', 'warning'],
                        static::isDebugModeEnabled($module) ? ['trace', 'info'] : []
                    ),
                    'logFile' => Craft::getAlias('@storage/logs/' . strtolower(str_replace('/', '-',
                            $module->getUniqueId())) . '.log'),
                    'logVars' => [],
                    'fileMode' => $configService->get('defaultFilePermissions'),
                    'dirMode' => $configService->get('defaultFolderPermissions'),
                    'prefix' => static::prefixSessionData(),
                ],
            ],
        ];

        return ArrayHelper::merge($config, $defaultConfig);
    }
}
