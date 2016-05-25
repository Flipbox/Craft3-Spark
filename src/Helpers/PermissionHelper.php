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

class PermissionHelper
{

    /**
     * Returns whether a given user has a given permission.
     *
     * @param $checkPermission
     * @param null $userElement
     * @return bool
     */
    public static function doesUserHavePermission($checkPermission, $userElement = null)
    {

        // Get current logged in user if not provided
        if (is_null($userElement)) {

            /** @var \craft\app\elements\User $userElement */
            $userElement = \Craft::$app->getUser()->getIdentity();
        }

        if ($userElement) {

            return $userElement->can($checkPermission);

        }

        return false;

    }

}
