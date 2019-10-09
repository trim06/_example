<?php


namespace app\models;


use app\components\FailException;
use app\components\ImageHandler\CImageHandler;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class TransportForm extends \yii\base\Model
{
    public $user_name;
    public $brand_id;
    public $transport_type_id;
    public $model_id;
    public $price_hour;
    public $price_day;
    public $price_description;
    public $year;
    public $phone;
    public $color;
    public $buyout;
    public $buyout_price;
    public $description;
    public $parser_id;

    /**
     * @var UploadedFile[]
     */
    public $imageFiles;


    private $transport;

    public function rules()
    {
        return [
            [['user_name', 'brand_id', 'transport_type_id', 'model_id', 'year', 'phone', 'description'], 'required'],
            [['brand_id', 'transport_type_id', 'model_id', 'price_hour', 'price_day', 'year', 'phone', 'color', 'buyout_price', 'parser_id'], 'integer'],
            [['price_description', 'buyout', 'description'], 'string'],
            ['buyout', 'default', 'value' => 'N'],
            ['buyout', 'in', 'range' => ['Y', 'N']],

            [['user_name'], 'string', 'max' => 200],

            [['imageFiles'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxFiles' => 20],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_name'         => 'Имя',
            'brand_id'          => 'Марка',
            'transport_type_id' => 'Вид транспорта',
            'model_id'          => 'Модель',
            'model_type_id'     => 'Тип',
            'price_hour'        => 'Цена за час аренды',
            'price_day'         => 'Цена за день аренды',
            'price_description' => 'Если цена меняется от срока аренды - укажите правила ценообразования',
            'year'              => 'Год выпуска',
            'phone'             => 'Телефон',
            'color'             => 'Color',
            'buyout'            => 'Выкуп',
            'buyout_price'      => 'Цена за аренду с выкупом',
            'description'       => 'Описание',
            'imageFiles'        => 'Изображения',
        ];
    }


    public function save()
    {

        if (!$this->validate()) {
            throw new FailException($this->getFirstErrors());
        }

        $transport = $this->getTransport();
        $transport->attributes = $this->attributes;
        $transport->city_id = City::findCurrent()->id ?? 1;
        $transport->model_type_id = 1;

        if (!$transport->save()) {
            throw new FailException($transport->getFirstErrors());
        } else {
            foreach ($this->imageFiles as $key => $file) {
                $fileName = ($key + 1) . '.' . $file->extension;
                $pathFile = $transport->generatePathImage() . '/' . $fileName;
                $file->saveAs($pathFile);

                $ih = new CImageHandler;
                $ih->load($pathFile)
                    ->thumb(200, false)
                    ->save($transport->generatePathImage(200) . '/' . $fileName);
            }

            if($this->parser_id) {
                $transportAvito = new TransportAvito;
                $transportAvito->transport_id = $transport->id;
                $transportAvito->avito_id = $this->parser_id;
                $transportAvito->save();
            }
        }

        return true;


    }

    /**
     * @return Transport
     */
    public function getTransport()
    {
        if (!$this->transport) {
            $this->transport = new Transport;
        }

        return $this->transport;
    }

    /**
     * @param mixed $transport
     */
    public function setTransport(Transport $transport)
    {
        $this->transport = $transport;
    }


    public function getListTransportTypes()
    {
        return ArrayHelper::map(TransportType::find()->all(), 'id', 'name');
    }

    public function getListBrands()
    {
        if (!$this->transport_type_id) {
            return ['' => 'сначала выберите вид'];
        } else {
            return ArrayHelper::map(Brand::findAll(['transport_type_id' => $this->transport_type_id]), 'id', 'name');
        }
    }

    public function getListModels()
    {
        if (!$this->brand_id) {
            return ['' => 'сначала выберите марку'];
        } else {
            return ArrayHelper::map(Model::findAll(['brand_id' => $this->brand_id]), 'id', 'name');
        }
    }

    public function getListYears()
    {
        $years = array_reverse(range(1850, date('Y')));

        return array_combine($years, $years);
    }

}