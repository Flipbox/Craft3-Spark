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
use Flipbox\Craft3\Spark\Services\Interfaces\DeletableModelAccessorInterface;
use Flipbox\Craft3\Spark\Services\ModelAccessorByIdOrHandle;

trait DeleteActionTrait
{

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @return DeletableModelAccessorInterface|ModelAccessorByIdOrHandle
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
    protected function canDelete()
    {

        if (!$this->getService() instanceof DeletableModelAccessorInterface) {

            throw new InvalidControllerServiceException(sprintf(
                "Controller service must implement '%s'.",
                'Flipbox\Craft3\Spark\Services\Interfaces\DeletableModelAccessorInterface'
            ));

        }

        // Require admin role
        $this->requireAdmin();

        // we require post data
        $this->requirePostRequest();

    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function deleteSuccessMessage(ModelInterface $model)
    {
        return sprintf(
            "'%s' was deleted successfully",
            (string)$model
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function deleteSuccessJsonResponse(ModelInterface $model)
    {
        return $this->returnJson(array(
                'success' => true,
                'message' => $this->deleteSuccessMessage($model),
                'data' => $model
            )
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function deleteFailMessage(ModelInterface $model)
    {
        return sprintf(
            "'%s' was NOT deleted successfully",
            (string)$model
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function deleteFailJsonResponse(ModelInterface $model)
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
    protected function deleteRouteVariables(ModelInterface $model)
    {
        return array(
            'model' => $model
        );
    }

    /**
     * Delete a Connected Application record.
     *
     * @throws HttpException
     */
    public function actionDelete()
    {

        try {

            $model = $this->getService()->get(
                Craft::$app->getRequest()->getRequiredBodyParam('identifier')
            );

        } catch (InvalidModelException $e) {

            throw new HttpException(500, $e->getMessage());

        }

        // Allow controllers to perform additional actions
        $this->onBeforeDelete($model);

        if ($this->getService()->delete($model)) {

            // Allow controllers to perform additional actions
            $this->onAfterDelete($model);

            // Handle AJAX calls
            if (Craft::$app->getRequest()->getIsAjax()) {

                return $this->deleteSuccessJsonResponse($model);

            }

            // Set flash success notice
            Craft::$app->getSession()->setNotice(
                $this->deleteSuccessMessage($model)
            );

            return $this->redirectToPostedUrl($model);

        }

        // Handle AJAX calls
        if (Craft::$app->getRequest()->getIsAjax()) {

            return $this->deleteFailJsonResponse($model);

        }

        // Set flash success notice
        Craft::$app->getSession()->setError(
            $this->deleteFailMessage($model)
        );

        // Set route variables
        Craft::$app->getUrlManager()->setRouteParams(
            $this->deleteRouteVariables($model)
        );

    }

    /**
     * Allow manipulations to model prior to saving.
     *
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function onBeforeDelete(ModelInterface $model)
    {
        return $model;
    }

    /**
     * Allow manipulations to model after to saving.
     *
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function onAfterDelete(ModelInterface $model)
    {
        return $model;
    }

}
