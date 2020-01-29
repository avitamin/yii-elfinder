<?php

class ServerFileInput extends CInputWidget
{

    use FileInputTrait;

    public $settings = array();
    public $connectorRoute = false;
    private $assetsDir;

    public function init()
    {
        $this->defaultInit();
    }

    public function run()
    {
        list($name, $id) = $this->resolveNameID();
        if (isset($this->htmlOptions['id'])) {
            $id = $this->htmlOptions['id'];
        } else {
            $this->htmlOptions['id'] = $id;
        }
        if (isset($this->htmlOptions['name'])) {
            $name = $this->htmlOptions['name'];
        } else {
            $this->htmlOptions['name'] = $name;
        }

        $contHtmlOptions = $this->htmlOptions;
        $contHtmlOptions['id'] = $id . 'container';
        echo CHtml::openTag('div', $contHtmlOptions);
        $inputOptions = array('id' => $id, 'style' => 'float:left;' /*, 'readonly' => 'readonly'*/);
        if ($this->hasModel()) {
            echo CHtml::activeTextField($this->model, $this->attribute, $inputOptions);
        } else {
            echo CHtml::textField($name, $this->value, $inputOptions);
        }

        echo CHtml::button('Browse', array('id' => $id . 'browse', 'class' => 'btn'));
        echo CHtml::closeTag('div');

        $settings = array_merge(
            array(
                'places' => "",
                'rememberLastDir' => false,
            ),
            $this->settings
        );

        $settings['dialog'] = array(
            'zIndex' => 400001,
            'width' => 900,
            'modal' => true,
            'title' => "Files",
        );
        $settings['editorCallback'] = 'js:function(url) {
        $(\'#\'+aFieldId).attr(\'value\',url);
        }';
        $settings['closeOnEditorCallback'] = true;
        $connectorUrl = CJavaScript::encode($this->settings['url']);
        $settings = CJavaScript::encode($settings);
        $script = <<<EOD
        window.elfinderBrowse = function(field_id, connector) {
            var aFieldId = field_id, aWin = this;
            if($("#elFinderBrowser").length == 0) {
                $("body").append($("<div/>").attr("id", "elFinderBrowser"));
                var settings = $settings;
                settings["url"] = connector;
                $("#elFinderBrowser").elfinder(settings);
            }
            else {
                $("#elFinderBrowser").elfinder("open", connector);
            }
        }
EOD;
        $cs = Yii::app()->getClientScript();
        $cs->registerScript('ServerFileInput#global', $script);

        $js = //'$("#'.$id.'").focus(function(){window.elfinderBrowse("'.$name.'")});'.
            '$("#' . $id . 'browse").click(function(){window.elfinderBrowse("' . $id . '", ' . $connectorUrl . ')});';


        $cs->registerScript('ServerFileInput#' . $id, $js);
    }

}
