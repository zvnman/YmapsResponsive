<?php

/**
 * Yandex maps responsive widget
 * @author ZVNIC
 * @version 2.1 beta
 * @link http://api.yandex.ru/maps/jsbox/placemark_balloon
 */
class YmapsResponsive extends CWidget {

    private $cs = null;
    public $id = 'YMapsID';
    public $width = 350;
    public $height = 350;
    public $zoom = 12;
    public $placemark;  // координаты точки
    private $latitude;  // широта
    private $longitude; // долгота

    // определяем координаты места

    protected function getYmapsPlacemark() {
        if (!$this->placemark)
            return Yii::app()->settings->get('yandex', 'ymaps.Placemark');
        return $this->placemark;
    }

    // устанавливаем широту и долготу
    protected function setLatitudeLongitude() {
        $latlon = explode(",", $this->getYmapsPlacemark());
        $this->latitude = $latlon[0];
        $this->longitude = $latlon[1];
    }

    // получаем широту
    public function getLatitude() {
        return $this->latitude;
    }

    // получаем долготу
    public function getLongitude() {
        return $this->longitude;
    }

    protected function registerClientScript() {
        
        $css = <<<EOD
#{$this->id} {
            width: {$this->width}px;
            height: {$this->height}px;
        }
EOD;
        $js = <<<EOD
ymaps.ready(function () {
    var myMap = new ymaps.Map('{$this->id}', {
        center: [{$this->getLatitude()}, {$this->getLongitude()}],
        zoom: {$this->zoom},
        // Обратите внимание, что в API 2.1 по умолчанию карта создается с элементами управления.
        // Если вам не нужно их добавлять на карту, в ее параметрах передайте пустой массив в поле controls.
        controls: []
    });

    var myPlacemark = new ymaps.Placemark(myMap.getCenter(), {
        balloonContentBody: [
            '<address>',
            '<strong>Офис Яндекса в Москве</strong>',
            '<br/>',
            'Адрес: 119021, Москва, ул. Льва Толстого, 16',
            '<br/>',
            'Подробнее: <a href="http://company.yandex.ru/">http://company.yandex.ru/<a>',
            '</address>'
        ].join('')
    }, {
        preset: 'islands#redDotIcon'
    });

    myMap.geoObjects.add(myPlacemark);
});
EOD;
        $this->cs->registerCss($this->id, $css);
        $this->cs->registerScriptFile('http://api-maps.yandex.ru/2.1-dev/?lang=ru-RU&load=package.full', CClientScript::POS_HEAD);
        $this->cs->registerScript($this->id, $js, CClientScript::POS_END);
    }

    public function init() {
        if (!$this->getYmapsPlacemark())
            throw new CException('Укажите параметр "YmapsPlacemark" для YmapsResponsive!');
        $this->cs = Yii::app()->clientScript;
        // подготавливаем координаты
        $this->setLatitudeLongitude();
        // регистрируем JS и  CSS
        $this->registerClientScript();
    }

    public function run() {
        echo '<div id="YMapsID"></div>';
    }

}
