<?php


namespace app\controllers;

use app\models\Brand;
use app\models\City;
use app\models\Model;
use app\models\Transport;
use app\models\TransportAvito;
use app\models\TransportForm;
use app\models\TransportSearch;
use app\models\TransportType;
use app\components\FailException;
use yii\di\ServiceLocator;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class TransportController extends \yii\web\Controller
{

    public function actionAddTransport()
    {
        $modelForm = new TransportForm;

        if (request()->isPost) {
            try {
                $modelForm->load(request()->post());
                $modelForm->imageFiles = UploadedFile::getInstances($modelForm, 'imageFiles');
                if ($modelForm->save()) {
                    session()->setFlash('success', 'Успешно добавлен');

                    return \response()->redirect(Url::to(['/']));
                } else {
                    throw new FailException('Системная ошибка');
                }
            } catch (FailException $e) {
                session()->setFlash('error', $e->getErrors());
            }
        }

        return $this->render('add_transport', compact('modelForm'));
    }

    public function actionIndex()
    {
        $transportType = TransportType::findOne(1);
        if (!$transportType) {
            throw new NotFoundHttpException();
        }

        $modelSearch = new TransportSearch();
        $modelSearch->setTransportType($transportType);
        $dataProvider = $modelSearch->search(request()->get());

        $linksBrand = Brand::find()->innerJoinWith('transport')
            ->where(['transport.city_id' => $modelSearch->getCity()->id])
            ->groupBy(['brand.id'])->all();

        return $this->render('index', compact('modelSearch', 'dataProvider', 'linksBrand'));
    }

    public function actionType($type)
    {
        $transportType = TransportType::findOne(['name_cpu' => $type]);
        if (!$transportType) {
            throw new NotFoundHttpException();
        }

        $modelSearch = new TransportSearch();
        $modelSearch->setTransportType($transportType);
        $dataProvider = $modelSearch->search(request()->get());

        $linksBrand = Brand::find()->innerJoinWith('transport')
            ->where(['transport.city_id' => $modelSearch->getCity()->id])
            ->groupBy(['brand.id'])->all();

        return $this->render('type', compact('modelSearch', 'dataProvider', 'linksBrand'));
    }

    public function actionBrand($type, $brand)
    {
        $transportType = TransportType::findOne(['name_cpu' => $type]);
        if (!$transportType) {
            throw new NotFoundHttpException();
        }
        $brand = Brand::findOne(['name_cpu' => $brand]);
        if (!$brand) {
            throw new NotFoundHttpException();
        }

        $modelSearch = new TransportSearch();
        $modelSearch->brand_id = $brand->id;
        $modelSearch->setTransportType($transportType);
        $dataProvider = $modelSearch->search();

        $linksModel = Model::find()->innerJoinWith('transport')
            ->with('brand')
            ->where(['model.brand_id' => $brand->id])
            ->andWhere(['transport.city_id' => $modelSearch->getCity()->id])
            ->groupBy(['model.id'])->all();

        return $this->render('brand', compact('modelSearch', 'dataProvider', 'brand', 'linksModel'));
    }

    public function actionModel($type, $brand, $model)
    {
        $transportType = TransportType::findOne(['name_cpu' => $type]);
        if (!$transportType) {
            throw new NotFoundHttpException();
        }
        $brand = Brand::findOne(['name_cpu' => $brand]);
        if (!$brand) {
            throw new NotFoundHttpException();
        }
        $model = Model::findOne(['name_cpu' => $model]);
        if (!$model) {
            throw new NotFoundHttpException();
        }

        $modelSearch = new TransportSearch();
        $modelSearch->brand_id = $brand->id;
        $modelSearch->model_id = $model->id;
        $modelSearch->setTransportType($transportType);
        $dataProvider = $modelSearch->search();

        $linksModel = Model::find()->innerJoinWith('transport')
            ->with('brand')
            ->where(['model.brand_id' => $brand->id])
            ->andWhere(['transport.city_id' => $modelSearch->getCity()->id])
            ->groupBy(['model.id'])->all();

        return $this->render('model', compact('modelSearch', 'dataProvider', 'brand', 'model', 'linksModel'));
    }

    public function actionListModel($brandId, $typeId)
    {
        response()->format = Response::FORMAT_JSON;

        $type = TransportType::findOne($typeId);
        if (!$type) {
            return [];
        }

        $brand = Brand::findOne($brandId);
        if (!$brand) {
            return [];
        }

        return Model::find()->innerJoinWith('brand')->where(['model.brand_id' => $brand->id, 'brand.transport_type_id' => $typeId])->all();
    }

    public function actionListBrand($typeId)
    {
        response()->format = Response::FORMAT_JSON;

        $type = TransportType::findOne($typeId);
        if (!$type) {
            return [];
        }

        return Brand::find()->where(['transport_type_id' => $typeId])->all();
    }

    public function actionShowPhone($id)
    {

        response()->format = Response::FORMAT_JSON;

        $transport = Transport::findOne($id);
        if (!$transport) {
            throw new NotFoundHttpException();
        }

        return ['phone' => $transport->phoneFormat()];
    }
}