<?php
namespace wonail\nestable;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Nestable widget
 */
class Nestable extends InputWidget
{

    /**
     * @var array the HTML attributes for the widget container tag.
     * @see Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];

    /**
     * @var string the theme name to be used for styling the Nestable.
     */
    public $theme = '';

    /**
     * @var array
     */
    public $ddOptions = [];

    /**
     * @var array
     *
     *  - group
     *  - title
     *  - items
     *      - id
     *      - title
     *      - name
     *      - ...
     *      - children
     *          - ...
     */
    public $items;

    /**
     * @var string
     */
    public $childrenName = 'children';

    /**
     * @var bool
     */
    public $draggableHandles = false;

    /**
     * @var array widget plugin options.
     *
     * - maxDepth: number of levels an item can be nested (default 5)
     * - group: group ID to allow dragging between lists (default 0)
     * - listNodeName: The HTML element to create for lists (default 'ol')
     * - itemNodeName: The HTML element to create for list items (default 'li')
     * - rootClass: The class of the root element .nestable() was used on (default 'dd')
     * - itemClass: The class of all list item elements (default 'dd-item')
     * - dragClass: The class applied to the list element that is being dragged (default 'dd-dragel')
     * - listClass: The class of all list elements (default 'dd-list')
     * - handleClass: The class of the content element inside each list item (default 'dd-handle')
     * - collapsedClass: The class applied to lists that have been collapsed (default 'dd-collapsed')
     * - placeClass: The class of the placeholder element (default 'dd-placeholder')
     * - emptyClass: The class used for empty list placeholder elements (default 'dd-empty')
     * - expandBtnHTML: The HTML text used to generate a list item expand button (default '<button data-action="expand">Expand></button>')
     * - collapseBtnHTML: The HTML text used to generate a list item collapse button (default '<button data-action="collapse">Collapse</button>')
     */
    public $pluginOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Html::addCssClass($this->options, ['nestable-lists']);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();
        $this->registerPluginAssets();
        $this->renderWidget();
    }

    /**
     * Initializes and renders the widget
     */
    public function renderWidget()
    {
        // BEGIN:nestable-box
        echo Html::beginTag('div', ['class' => 'nestable-box']);
        foreach ($this->items as $item) {
            $this->renderGroup($item);
        }
        // END:nestable-box
        echo Html::endTag('div');
        echo Html::activeHiddenInput($this->model, $this->attribute);
    }

    protected function renderGroup($item)
    {
        // BEGIN:nestable-lists
        $options['data-group'] = $item['group'];
        $options['data-title'] = $item['title'];
        $options = array_merge($this->options, $options);
        unset($options['id']);
        echo Html::beginTag('div', $options);
        $this->id = static::$autoIdPrefix . static::$counter++;
        // BEGIN:dd
        $ddOptions = [
            'class' => 'dd',
            'id' => $this->id,
        ];
        $this->ddOptions = array_merge($this->ddOptions, $ddOptions);
        echo Html::beginTag('div', $this->ddOptions);
        echo Html::tag('div', $item['title'], ['class' => 'nestable-title']);
        // BEGIN:dd-list
        echo $this->renderList($item['items']);
        // END:dd-list
        // END:dd
        echo Html::endTag('div');
        // END:nestable-lists
        echo Html::endTag('div');
    }

    private function renderList($level)
    {
        if (empty($level)) {
            echo Html::tag('div', '', ['class' => 'dd-empty']);
        } else {
            $options['class'] = 'dd-list';
            if (!empty($this->theme)) {
                Html::addCssClass($options, 'dd-' . $this->theme);
            }
            echo Html::beginTag('ol', $options);
            foreach ($level as $item) {
                $this->renderItem($item);
            }
            echo Html::endTag('ol');
        }
    }

    private function renderItem($item)
    {
        if ($this->draggableHandles) {
            $options = ['class' => 'dd-item dd3-item'];
        } else {
            $options = ['class' => 'dd-item'];
        }
        foreach ($item as $k => $v) {
            if ($k !== $this->childrenName) {
                $options['data'][$k] = $v;
            }
        }

        echo Html::beginTag('li', $options);

        if ($this->draggableHandles) {
            echo Html::tag('div', Html::tag('span', 'Drag', ['class' => 'sr-only']), ['class' => 'dd-handle dd3-handle']);
            echo Html::tag('div', $item['title'], ['class' => 'dd3-content']);
        } else {
            echo Html::tag('div', $item['title'], ['class' => 'dd-handle']);
        }

        if (isset($item[$this->childrenName]) && count($item[$this->childrenName])) {
            $this->printLevel($item[$this->childrenName]);
        }

        echo Html::endTag('li');
    }

    /**
     * Register Asset manager
     */
    private function registerPluginAssets()
    {
        $view = $this->getView();
        NestableAsset::register($view);

        $pluginOptions = Json::encode($this->pluginOptions);
        $name = Html::getInputName($this->model, $this->attribute);
        $js = <<<JS
$(document).ready(function () {

    $('.dd').nestable({$pluginOptions}).on('change', function () {
        var obj = $(this).parents('.nestable-box'),
            nestable = new Array();
        obj.find('.nestable-lists').each(function (index, element) {
            if ($(element).data('group')) {
                nestable[index] = new Object();
                nestable[index]['group'] = $(element).data('group');
                nestable[index]['title'] = $(element).data('title');
                nestable[index]['items'] = $(element).find('.dd').nestable('serialize');
            }
        });

        $("[name=\"{$name}\"]").val(JSON.stringify(nestable));
    });

});
JS;
        $view->registerJs($js);
    }

}
