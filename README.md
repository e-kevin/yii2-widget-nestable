# yii2-widget-nestable
Nestable 小部件

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist wonail/yii2-widget-nestable "*"
```

or add

```
"wonail/yii2-widget-nestable": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
// 数据库里保存的数据，这里用json格式保存，部件读取时，直接decode解码即可
$value = '[{"group":"disable","title":"禁用","items":[]},{"group":"enable","title":"启用","items":[{"title":"修改头像","name":"change_avatar","id":1},{"title":"选择个人标签","name":"set_tag","id":3},{"title":"填写扩展资料","name":"expand_info","id":2}]}]';
echo $form->field($model, 'name')->widget(\wonail\nestable\Nestable::className(), [
    'items' => \yii\helpers\Json::decode($value),
    'pluginOptions' => [
        'group' => 1,
        'maxDepth' => 1
    ]
]);
```
