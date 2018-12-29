<?php
//CHECK LINE NUMBER 23,24 USES

namespace backend\controllers;
use Yii;
use backend\models\Media;
use backend\models\MediaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * MediaController implements the CRUD actions for Media model.
 */
class MediaController extends Controller
{

    /**
     * {@inheritdoc}
     */
	public function actionTest(){
        $r=Yii::$app->mydb->query('SELECT * FROM `media`'); //Direct use 
        $r=Yii::$app->mydb->result($r);			//DIRECT USE IN ANY CONTROLLER
        echo "<pre>";print_r($r);exit;
        $model = new Media();
        //$model->id='';
        $model->worker_id=1;
        $model->image=87;
        if($model->save()){
            echo 'success save';
        }
        else{
            echo "<pre>"; print_r($model);exit;
        }
     }
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Media models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MediaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Media model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Media model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */



     

    public function actionCreate()
    {
        $model = new Media();
       
        if ($model->load(Yii::$app->request->post()) ) {
           // echo "<pre>"; print_r(Yii::$app->request->post());exit;
            $model->image = UploadedFile::getInstance($model, 'image');
           // echo "<pre>"; print_r($model->image);exit;
            $model->image->saveAs('uploads/' . $model->image->baseName . '.' . $model->image->extension);
            $file_name='uploads/' . $model->image->baseName . '.' . $model->image->extension;
            $model->image =$file_name;
          
           // echo "<pre>"; print_r(Yii::$app->getBasePath().'/web/'.$model->image);exit;
           $filePath=Yii::$app->getBasePath().'/web/'.$model->image;
              $model->image=  Yii::$app->s3->upload($filePath);
          
            if($model->save()){
               // echo "<pre>"; print_r($model);exit;
                return $this->redirect(['index']);
            }else{
                echo "<pre>"; print_r($model);exit;
            }
           // return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Media model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Media model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Media model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Media the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */

    protected function findModel($id)
    {
        if (($model = Media::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    function imageUplodeOns3($FILES,$model)
    {
        if((isset($FILES["Media"]["name"]['image']) && $FILES["Media"]["name"]['image']!=""))
        {
            foreach ($FILES['Media']['name'] as $key => $value) 
            {
                $filePath = realpath($_FILES["Media"]["tmp_name"][$key]);
                $typeArr = explode("/",$_FILES["Media"]["type"][$key]);
                if(array_key_exists($typeArr[1], $this->file_type_ar))
                {
                   // $model->rep_type = $this->file_type_ar[$typeArr[1]];
                }else{
                    //$model->rep_type = "other";
                }
                $doc_name = uniqid().mt_rand(111111,999999).$value;
                $fileUrl=  Yii::$app->s3->upload($filePath);
               // $fileUrl=Yii::$app->s3->url('file.jpg');
               /* $fileUrl = Yii::$app->s3->upload($filePath, [
                    'override' => true,
                    'Key' => $doc_name,
                    'CacheControl' => 'max-age=' . strtotime('+1 year')  
                ]);*/

                if($fileUrl){
                    $_POST["Media"]['image'] = $model->image = $fileUrl;
                    
                }else{
                    $_POST["Media"]['image'] = $model->image = '';
                }
            }
        }
    }

    private function uploadfile($FILES,$model){
        if((isset($FILES["Repository"]["name"]['image']) && $FILES["Repository"]["name"]['image']!=""))
        {
            $model->image=$FILES["Repository"]["name"]['image'];
        }
        var_dump($_FILES);exit;
        $file_name = $_FILES['image']['name'];
          $file_size = $_FILES['image']['size'];
          $file_tmp = $_FILES['image']['tmp_name'];
          $file_type = $_FILES['image']['type'];
          $file_ext=strtolower(end(explode('.',$_FILES['image']['name'])));
          
          $expensions= array("jpeg","jpg","png");
          
          if(in_array($file_ext,$expensions)=== false){
             $errors[]="extension not allowed, please choose a JPEG or PNG file.";
          }
          
          if($file_size > 2097152) {
             $errors[]='File size must be excately 2 MB';
          }
          
          if(empty($errors)==true) {
             move_uploaded_file($file_tmp,"images/".$file_name);
             echo "Success";
          }else{
             print_r($errors);
          }
        }
    
}
