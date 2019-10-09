<?php


namespace app\models;

use yii\data\ActiveDataProvider;

class TransportSearch extends \yii\base\Model
{

    public $sortby;
    public $order = 'ASC';

    public $brand_id;
    public $model_id;

    public $year_from;
    public $year_to;
    public $buyout;

    private $transport_type;
    private $city = null;

    public function rules()
    {
        return [
            [['sortby'], 'in', 'range' => ['name', 'price']],
            [['order'], 'in', 'range' => ['DESC', 'ASC']],
            [['brand_id', 'model_id', 'year_from', 'year_to', 'buyout'], 'safe'],
        ];
    }


    /**
     * @param array  $params
     * @param string $formName
     *
     * @return ActiveDataProvider
     */
    public function search($params = [], $formName = '')
    {
        $query = Transport::find()->where(['city_id' => $this->getCity()->id]);
        $query->with('brand', 'model');

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'defaultPageSize' => 20,
                'pageSizeLimit'   => [1, 100],
                //				'pageSize' => 1
            ],
            'sort'       => [
                'defaultOrder' => [
                    'date' => SORT_DESC,
                ],
                'attributes'   => [
                    'date'       => [
                        'desc' => ['transport.date' => SORT_DESC],
                        'asc'  => ['transport.date' => SORT_DESC],
                    ],
                    'pricelow'   => [
                        'desc' => ['transport.price_hour' => SORT_ASC],
                        'asc'  => ['transport.price_hour' => SORT_ASC],
                    ],
                    'pricehight' => [
                        'desc' => ['transport.price_hour' => SORT_DESC],
                        'asc'  => ['transport.price_hour' => SORT_DESC],
                    ],
                ]
            ],
        ]);

        $this->load($params, $formName);

        if (!($this->validate())) {
            return $dataProvider;
        }

        if ($this->sortby) {
            $query->orderBy([$this->sortby => ($this->order == 'ASC' ? SORT_ASC : SORT_DESC)]);
        }

        if (isset($this->transport_type->id)) {
            $query->andFilterWhere([Transport::tableName() . '.transport_type_id' => $this->transport_type->id]);
        }
        $query->andFilterWhere([Transport::tableName() . '.brand_id' => $this->brand_id]);
        $query->andFilterWhere([Transport::tableName() . '.model_id' => $this->model_id]);
        if ($this->buyout) {
            $query->andWhere([Transport::tableName() . '.buyout' => 'Y']);
        }
        //        $query->andFilterWhere(['between', 'year', $this->year_from, $this->year_to]);
        $query->andFilterWhere(['>=', Transport::tableName() . '.year', $this->year_from]);
        $query->andFilterWhere(['<=', Transport::tableName() . '.year', $this->year_to]);

        return $dataProvider;
    }

    public function getCity()
    {
        if (!$this->city) {
            $this->city = City::findCurrent();
            if (!$this->city) {
                $this->city = City::findOne(1); //Краснодар
            }
        }

        return $this->city;
    }

    public function setTransportType($transportType)
    {
        $this->transport_type = $transportType;
    }

    /**
     * @return TransportType
     */
    public function getTransportType()
    {
        if ($this->transport_type) {
            return $this->transport_type;
        }

        return TransportType::findOne(1);
    }

}