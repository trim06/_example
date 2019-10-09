<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%transport}}".
 *
 * @property int           $id
 * @property int           $user_id
 * @property string        $user_name
 * @property string        $date
 * @property int           $city_id
 * @property int           $brand_id
 * @property int           $transport_type_id
 * @property int           $model_id
 * @property int           $model_type_id
 * @property int           $price_hour
 * @property int           $price_day
 * @property string        $price_description
 * @property int           $year
 * @property int           $phone
 * @property int           $color
 * @property string        $buyout
 * @property int           $buyout_price
 * @property string        $description
 *
 * @property Brand         $brand
 * @property Model         $model
 * @property TransportType $transportType
 * @property Transport[]   $transports
 */
class Transport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%transport}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'brand_id', 'transport_type_id', 'model_id', 'model_type_id', 'price_hour', 'price_day', 'year', 'phone', 'color', 'buyout_price', 'city_id'], 'integer'],
            [['date'], 'safe'],
            [['brand_id', 'transport_type_id', 'model_id', 'model_type_id', 'year', 'phone', 'description', 'city_id'], 'required'],
            [['price_description', 'buyout', 'description'], 'string'],
            [['user_name'], 'string', 'max' => 200],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brand::className(), 'targetAttribute' => ['brand_id' => 'id']],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => Model::className(), 'targetAttribute' => ['model_id' => 'id']],
            [['transport_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transport::className(), 'targetAttribute' => ['transport_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'user_id'           => 'User ID',
            'user_name'         => 'User Name',
            'date'              => 'Date',
            'brand_id'          => 'Brand ID',
            'transport_type_id' => 'Transport Type ID',
            'model_id'          => 'Model ID',
            'model_type_id'     => 'Model Type ID',
            'price_hour'        => 'Price Hour',
            'price_day'         => 'Price Day',
            'price_description' => 'Price Description',
            'year'              => 'Year',
            'phone'             => 'Phone',
            'color'             => 'Color',
            'buyout'            => 'Buyout',
            'buyout_price'      => 'Buyout Price',
            'description'       => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brand::className(), ['id' => 'brand_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModel()
    {
        return $this->hasOne(Model::className(), ['id' => 'model_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportType()
    {
        return $this->hasOne(TransportType::className(), ['id' => 'transport_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransports()
    {
        return $this->hasMany(Transport::className(), ['transport_type_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return TransportQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransportQuery(get_called_class());
    }

    public function getUrl()
    {
        return '/' . $this->transportType->name_cpu . '/' . $this->brand->name_cpu . '/' . $this->model->name_cpu;
    }

    public function getTitle()
    {
        return $this->brand->name . ' ' . $this->model->name . ' ' . $this->year;
    }

    public function getHiddenPhone()
    {
        return substr($this->phoneFormat(), 0, -4) . '*-**';

    }

    public function phoneFormat()
    {
        $data = '+7' . $this->phone;

        if (preg_match('/^\+\d(\d{3})(\d{3})(\d{2})(\d{2})$/', $data, $matches)) {
            $result = '+7 (' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3] . '-' . $matches[4];

            return $result;
        }
    }

    public function generatePathImage($thumb = null)
    {
        return generatePath($this->id, 2, \Yii::getAlias('@webroot') . '/transport', true, $thumb);
    }

    public function getPathImage($thumb = null)
    {
        return generatePath($this->id, 2, \Yii::getAlias('@webroot') . '/transport', false, $thumb);
    }

    public function getWebPathImage($thumb = null)
    {
        return generatePath($this->id, 2, \Yii::getAlias('@web') . '/transport', false, $thumb);
    }
}
