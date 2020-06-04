<?php /* @var $this yii\web\View */ $this->title = 'sibreg.org | Картографический сервис'; app\assets\MapAsset::register($this); ?> <input type="hidden" id="inpLastTrack" value="<?=$lastTrack?>"> <input type="hidden" id="inpTracks" value="<?=$tracks?>"><div id="sidebar"><ul><li><a href="/maps">Карты</a></li><li><a href="/tracks">Треки</a></li><li><a href="/cabinet">Личный кабинет</a></li><li><a href="/faq">Что делать</a></li><li><a href="/contacts">Обратная связь</a></li></ul><a href="/" data-toggle="#sidebar" class="btn-close" title="Закрыть">&#9776;</a></div><div class="main-container"><div id="map"></div><div id="sidebar-toggle-container"><a href="#" data-toggle="#sidebar" id="sidebar-toggle" title="Открыть меню"><span class="bar"></span> <span class="bar"></span> <span class="bar"></span></a></div><!--<div class="row">
        <h1>Главная страница</h1>
        <p class="pull-right">
            <a href="/cabinet">Вход</a>
        </p>
    </div>--></div>