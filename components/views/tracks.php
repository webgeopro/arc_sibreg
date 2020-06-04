<?php
/**
 * Представление виджета TracksWidget
 * User: Webgeopro
 * Date: 24.07.2016
 */
//use app\models\UserTrack;

?>

<div class="container">
    <h4>ЗАГРУЖЕННЫЕ ТРЕКИ</h4>
    <? foreach($tracks as $track): ?>
        <a class="thumbnail col-sm-3">
            <h3><?=$track['name']?></h3>
            <h4>Добавлен: <time><?=$track['created']?></time></h4>
            <? if ($track['created_at'] != $track['updated_at']):?>
                <h4>Обновлен: <time><?=$track['created']?></time></h4>
            <? endif ?>
            <h4>Статус: <?=$track['statusName']?></h4>
        </a>
    <? endforeach ?>
</div>