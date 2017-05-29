<?php

namespace app\controllers;

use app\models\UploadHistory;
use app\services\LogParser;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class ParsingController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new LogParser();
        $filename = new UploadHistory();

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file && $model->validate()) {
                $model->file->saveAs($model->file);
                $filename->filename = $model->file->name;
                $filename->save();
                $path = $filename->filename_id;
                $rows = $model->indexFile($model->file);
                $model->logUpload($rows, $path);
            }
        }

        return $this->render('index', ['model' => $model]);
    }

}