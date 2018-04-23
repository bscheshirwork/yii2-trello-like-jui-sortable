# yii2-trello-like-jui-sortable

This is widget for display you data into portlet and move it between column.

Each successful move produced request to your backend. 


# Installation
Add to you `require` section `composer.json`
```
"bscheshirwork/yii2-trello-like-jui-sortable": "*",
```

# Usage

Example: your have bindStatus junction table. You wish sort lead by status.

Add to `index-drag-and-drop` view file (`views/board/index-drag-and-drop`) your columns and your contend.
```php
    <?php
    TrelloLikeSortable::begin([
        'targetAction' => '/board/drop-finish',
    ]);
    ?>

    <?php foreach ($columns as $columnModel):?>

    <div class="column" id="column<?= $columnModel->id ?>">
        <div class="column-header"><?= $columnModel->name ?></div>
        <?php foreach (array_key_exists($columnModel->id, $dataProviders) ? $dataProviders[$columnModel->id]->models ?? [] : [] as $model):?>
        <div class="portlet" id="item<?= $model->id ?>>
            <div class="portlet-header"><?=$model->name?></div>
            <div class="portlet-content"><?=$model->description?></div>
        </div>

        <?php endforeach;?>
    </div>
    <?php endforeach;?>


    <?php
    TrelloLikeSortable::end();
    ?>
```


Add action `index-drag-and-drop` for show grid and `drop-finish` to `BoardController`.

```php
    /**
     * Lists all Lead models.
     * @return mixed
     */
    public function actionIndexDragAndDrop()
    {
        $searchModel = new BoardSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $columns = Status::find()->active()->indexBy('id')->all();
        $dataProviders = $searchModel->dataProviderByStatusList($dataProvider, $columns);

        return $this->render('index-drag-and-drop', [
            'searchModel' => $searchModel,
            'dataProviders' => $dataProviders,
            'columns' => $columns,
        ]);
    }

    /**
     * Accept result of drag-and-drop event TrelloLikeSortable
     * @return bool
     */
    public function actionDropFinish() {
        $model = (new \yii\base\DynamicModel(['item' => null, 'column' => null, 'next' => null]))
            ->addRule('item', 'filter', ['filter' => function ($value) { return strtr($value, ['item' => '']); }])
            ->addRule('column', 'filter', ['filter' => function ($value) { return strtr($value, ['column' => '']); }])
            ->addRule('next', 'filter', ['filter' => function ($value) { return strtr($value, ['item' => '']); }])
            ->addRule('item', 'integer')
            ->addRule('column', 'integer')
            ->addRule('next', 'integer');
        $result = $model->load(Yii::$app->request->post(), '');
        $result &= $model->validate();
        /** todo: add buiseness logic here */

        return (bool) $result;
    }

```

Add to search model `BoardSearch`

```php
    /**
     * Add new $dataProvider list by all active statuses
     * @param ActiveDataProvider $dataProvider
     * @param Status[] $statusList
     * @return ActiveDataProvider[]
     */
    public function dataProviderByStatusList(ActiveDataProvider $dataProvider, array $statusList = []): array
    {
        foreach ($statusList ?? [] as $id => $column) {
            $result[$id] = $this->dataProviderByStatus($dataProvider, $column);
        }

        return $result ?? [];
    }

    /**
     * Add new clone of $dataProvider with additional condition: last of binding status for passed status
     * @param ActiveDataProvider $dataProvider
     * @param Status $status
     * @return ActiveDataProvider
     */
    public function dataProviderByStatus(ActiveDataProvider $dataProvider, Status $status): ActiveDataProvider
    {
        $dataProviderItem = clone $dataProvider;
        /** @var ActiveQuery $query */
        $query = $dataProviderItem->query = clone $dataProviderItem->query;
        $query->joinWith([
            'bindStatuses' => function (\common\models\BindStatusQuery $query) {
                $query->lastBy('leadId');
            },
        ])->andWhere([\common\models\BindStatus::tableName() . '.statusId' => $status->id]);

        return $dataProviderItem;
    }

```

Add to junction table ActiveQuery class / to trait
```php

    /**
     * Add to query latest by time
     * SELECT id, createdAt, updatedAt From bind_status where GREATEST(createdAt, updatedAt) =
     * ( select max(GREATEST(createdAt, updatedAt)) from bind_status as i where i.leadId=bind_status.leadId )
     * @param $group string the group field of junction table. Group by relation to main table.
     * @throws \yii\base\InvalidConfigException
     */
    public function lastBy($group) {
        /** @var ActiveQuery $subquery */
        $subquery = \Yii::createObject(static::class, [$this->modelClass]);
        $mainAlias = $this->getPrimaryTableName();
        $alias = strtr($subquery->getPrimaryTableName(), [ '{{%' => '{{%inner_']);
        $subquery->alias($alias)
        ->select('max(GREATEST(' . $alias . '.`createdAt`, ' . $alias . '.`updatedAt`))')
        ->andWhere($alias . '.`' . $group . '` = '. $mainAlias . '.`' . $group . '` ');

        $this->andWhere(['=', 'GREATEST('. $mainAlias . '.`createdAt`, '. $mainAlias . '.`updatedAt`)', $subquery]);
    }

```