<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace tljs;

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
 * ]);
 * ?>
 *
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
        parent::init();
        echo Html::beginTag('div', $this->options) . "\n";
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo Html::endTag('div') . "\n";
        $this->registerWidget('sortable');
    }
}
