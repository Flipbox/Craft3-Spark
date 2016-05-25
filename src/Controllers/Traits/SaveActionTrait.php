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

namespace Flipbox\Craft3\Spark\Controllers\Traits;

use Craft;
use craft\app\errors\HttpException;
use Flipbox\Craft3\Spark\Exceptions\InvalidControllerServiceException;
use Flipbox\Craft3\Spark\Exceptions\InvalidModelException;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Services\Interfaces\SavableModelAccessorInterface;
use Flipbox\Craft3\Spark\Services\ModelAccessorByIdOrHandle;

trait SaveActionTrait
{

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @return SavableModelAccessorInterface|ModelAccessorByIdOrHandle
     */
    abstract protected function getService();

    /**
     * @param $error
     * @return mixed
     */
    abstract public function returnErrorJson($error);

    /**
     * @param array $var
     * @return mixed
     */
    abstract public function returnJson($var = array());

    /**
     * @param null $object
     * @param null $default
     * @return mixed
     */
    abstract public function redirectToPostedUrl($object = null, $default = null);

    /**
     * @return mixed
     */
    abstract public function requirePostRequest();

    /**
     * @return mixed
     */
    abstract public function requireAdmin();

    /*******************************************
     * PERMISSIONS
     *******************************************/

    /**
     * @throws InvalidControllerServiceException
     */
    protected function canSave()
    {

        if (!$this->getService() instanceof SavableModelAccessorInterface) {

            throw new InvalidControllerServiceException(sprintf(
                "Controller service must implement '%s'.",
                'Flipbox\Craft3\Spark\Services\Interfaces\SavableModelAccessorInterface'
            ));

        }

        // Require admin role
        $this->requireAdmin();

        // we require post data
        $this->requirePostRequest();

        return true;

    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function saveSuccessMessage(ModelInterface $model)
    {
        return sprintf(
            "'%s' was saved successfully",
            (string)$model
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function saveSuccessJsonResponse(ModelInterface $model)
    {
        return $this->returnJson(array(
                'success' => true,
                'message' => $this->saveSuccessMessage($model),
                'data' => $model
            )
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function saveFailMessage(ModelInterface $model)
    {
        return sprintf(
            "'%s' was NOT saved successfully",
            (string)$model
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function saveFailJsonResponse(ModelInterface $model)
    {
        return $this->returnErrorJson(array(
                'success' => false,
                'errors' => $model->getErrors()
            )
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function saveRouteVariables(ModelInterface $model)
    {
        return array(
            'model' => $model
        );
    }

    /**
     * @return array
     */
    public function getSaveData()
    {
        return Craft::$app->getRequest()->getBodyParams();
    }

    /**
     * Standard save action.  Attempt to get posted 'id' and an array of 'fields'.
     *
     * @throws HttpException
     */
    public function actionSave()
    {

        // Check if we can save
        $this->canSave();

        // hidden field
        $id = Craft::$app->getRequest()->getBodyParam('identifier');

        if (!empty($id)) {

            try {

                $model = $this->getService()->getById($id);

            } catch (InvalidModelException $e) {

                throw new HttpException(500, $e->getMessage());

            }

        } else {

            $model = $this->getService()->create();

        }

        // set post data as attributes
        $model->setAttributes(
            $this->getSaveData()
        );

        // Allow controllers to perform additional actions
        $this->onBeforeSave($model);

        // perform save action against service
        if ($this->getService()->save($model)) {

            // Allow controllers to perform additional actions
            $this->onAfterSave($model);

            // Handle AJAX calls
            if (Craft::$app->getRequest()->getIsAjax()) {

                return $this->saveSuccessJsonResponse($model);

            }

            // Set flash success notice
            Craft::$app->getSession()->setNotice(
                $this->saveSuccessMessage($model)
            );

            return $this->redirectToPostedUrl($model);

        }

        // Handle AJAX calls
        if (Craft::$app->getRequest()->getIsAjax()) {

            return $this->saveFailJsonResponse($model);

        }

        // set flash success notice
        Craft::$app->getSession()->setError(
            $this->saveFailMessage($model)
        );

        // set model
        Craft::$app->getUrlManager()->setRouteParams(
            $this->saveRouteVariables($model)
        );

    }

    /**
     * Allow manipulations to model prior to saving.
     *
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function onBeforeSave(ModelInterface $model)
    {
        return $model;
    }

    /**
     * Allow manipulations to model after to saving.
     *
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function onAfterSave(ModelInterface $model)
    {
        return $model;
    }

}
