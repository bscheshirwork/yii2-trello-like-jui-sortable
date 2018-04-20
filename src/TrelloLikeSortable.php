<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace bscheshirwork\tljs;

use bscheshirwork\tljs\assets\WidgetAsset;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\jui\Widget;
use yii\helpers\Html;

/**
 * Trello-like sortable board renders by a sortable jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * <?php
 * TrelloLikeSortable::begin([
 *     'targetAction' => '/lead/drop-finish',
 * ]);
 * ?>
 *
 *     <div class="column" id="column1">
 *
 *         <div class="portlet">
 *             <div class="portlet-header">Feeds</div>
 *             <div class="portlet-content">Lorem ipsum dolor sit amet, consectetuer adipiscing elit</div>
 *         </div>
 *
 *         <div class="portlet">
 *             <div class="portlet-header">News</div>
 *             <div class="portlet-content">Lorem ipsum dolor sit amet, consectetuer adipiscing elit</div>
 *         </div>
 *
 *     </div>
 *
 *     <div class="column">
 *
 *         <div class="portlet">
 *             <div class="portlet-header">Shopping</div>
 *             <div class="portlet-content">Lorem ipsum dolor sit amet, consectetuer adipiscing elit</div>
 *         </div>
 *
 *     </div>
 *
 *     <div class="column">
 *
 *         <div class="portlet">
 *             <div class="portlet-header">Links</div>
 *             <div class="portlet-content">Lorem ipsum dolor sit amet, consectetuer adipiscing elit</div>
 *         </div>
 *
 *         <div class="portlet">
 *             <div class="portlet-header">Images</div>
 *             <div class="portlet-content">Lorem ipsum dolor sit amet, consectetuer adipiscing elit</div>
 *         </div>
 *
 *     </div>
 *
 * <?php
 * TrelloLikeSortable::end();
 * ?>
 *
 * ```
 *
 * @see http://api.jqueryui.com/sortable/
 * @see https://jsfiddle.net/BSCheshir/xpvt214o/160661/
 * @author BSCheshir <bscheshir.work@gmail.com>
 */
class TrelloLikeSortable extends Widget
{
    /**
     * @var array|string $url the parameter to be used to generate a URL for send data about drop finish.
     * @see \yii\helpers\Url::to() for details on how url are being created.
     * The POST data will contain information about column, dragable item, and item after dragable.
     * Can accept this data in `BoardController` like
     * ```php
     *     public function actionDropFinish() {
     *         $model = (new \yii\base\DynamicModel(['item' => null, 'column' => null, 'next' => null]))
     *             ->addRule('item', 'filter', ['filter' => function ($value) { return strtr($value, ['item' => '']); }])
     *             ->addRule('column', 'filter', ['filter' => function ($value) { return strtr($value, ['column' => '']); }])
     *             ->addRule('next', 'filter', ['filter' => function ($value) { return strtr($value, ['next' => '']); }])
     *             ->addRule('item', 'integer')
     *             ->addRule('column', 'integer')
     *             ->addRule('next', 'integer');
     *         $result = $model->load(Yii::$app->request->post(), '');
     *         $result &= $model->validate();
     *
     *         return (bool) $result;
     *     }
     * ```
     * for `board/drop-finish` value of `targetAction`
     */
    public $targetAction;

    /**
     * {@inheritdoc}
     */
    protected $clientEventMap = [
        'activate' => 'sortactivate',
        'beforeStop' => 'sortbeforestop',
        'change' => 'sortchange',
        'create' => 'sortcreate',
        'deactivate' => 'sortdeactivate',
        'out' => 'sortout',
        'over' => 'sortover',
        'receive' => 'sortreceive',
        'remove' => 'sortremove',
        'sort' => 'sort',
        'start' => 'sortstart',
        'stop' => 'sortstop',
        'update' => 'sortupdate',
    ];

    /**
     * Initializes the widget.
     */
    public function init()
    {
        if (empty($this->targetAction)) {
            throw new InvalidConfigException('TrelloLikeSortable config error: "targetAction" missing. Can be use any of Url::to() format');
        }
        $this->targetAction = Url::to($this->targetAction);

        if (!is_array($this->clientEvents['stop'])) {
            $this->clientEvents['stop'] = [$this->clientEvents['stop']];
        }
        $this->clientEvents['stop'][] = <<<JS
function (event, ui) { 
    $.ajax({
        url: '{$this->targetAction}',
        type: 'POST',
        data: {
            'item' : ui.item.data('alias'),
            'column' : ui.item.parent('.column').data('alias'),
            'next' : ui.item.next('.portlet').data('alias')
        },
        success: function(res){
            console.log(res);
        },
        error: function(){
             console.log('Error happens in drop trello-like-jui-sortable ajax request.');
        }
    });
}
JS;
        parent::init();
        echo Html::beginTag('div', $this->options) . "\n";
    }

    /**
     * https://github.com/yiisoft/yii2-jui/pull/78
     * Registers a specific jQuery UI widget events
     * @param string $name the name of the jQuery UI widget
     * @param string $id the ID of the widget
     */
    protected function registerClientEvents($name, $id)
    {
        if (!empty($this->clientEvents)) {
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                if (isset($this->clientEventMap[$event])) {
                    $eventName = $this->clientEventMap[$event];
                } else {
                    $eventName = strtolower($name . $event);
                }
                foreach (is_array($handler) ? $handler : [$handler] as $handlerItem) {
                    $js[] = "jQuery('#$id').on('$eventName', $handlerItem);";
                }
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo Html::endTag('div') . "\n";
        $this->registerWidget('sortable');

        WidgetAsset::register($this->getView());
    }
}
