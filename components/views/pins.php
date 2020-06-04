<?php
/**
 * Представление виджета PinsWidget
 * User: Webgeopro
 * Date: 12.07.2016
 */
use yii\Helpers\HTML;
?>
<h4>ПИНКОДЫ</h4>
<table class="table-bordered table-striped table-responsive table-pins margin-bottom-10">
    <thead><tr>
        <th>&nbsp; Пинкод &nbsp;</th>
        <th>&nbsp; Создан &nbsp;</th>
        <th>&nbsp; Устройство &nbsp;</th>
        <th>&nbsp; Наименование &nbsp;</th>
        <th>&nbsp;</th>
    </tr></thead>
    <?foreach($pins as $pin):?>
    <tr>
        <td><?=$pin['pin']?></td>
        <td><?=$pin['created']?></td>
        <td><?=empty($pin['device']['name'])?'Не активирован':$pin['device']['name']?></td>
        <td><?=$pin['name']?></td> <!--Код устройства-->
        <td>
            <?if (!empty($pin['device'])):?>
            <a href="#" class="btn btn-primary btn-flat btn-xs btnUpdatePin" data-adm="<?=$pin['id']?>" data-act="pins" title="Обновить пинкод">
                <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
            </a>
            <?endif?>
        </td>
    </tr>
    <?endforeach;?>
</table>

<a href="#" title="Создать новый ПинКод" class="btnNewPin">Создать ПИНКОД</a>

<!-- Modal New Pin -->
<div class="modal fade" id="modalNewPin" tabindex="-1" role="dialog" aria-labelledby="modalNewPinLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <?= Html::beginForm(['cabinet/pin'], 'put') ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Создать ПИНКОД</h4>
            </div>
            <div class="modal-body">
                <span class="text-danger">При создании пинкода с Вас спишется одна активация!</span>
                <p>Вы действительно хотите создать новый ПИНКОД?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="submit" class="btn btn-primary">ДА, получить</button>
            </div>
        </div>
        <?= Html::endForm() ?>

    </div>
</div>

<!-- Modal Update Pin -->
<div class="modal fade" id="modalUpdatePin" tabindex="-1" role="dialog" aria-labelledby="modalUpdatePinLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= Html::beginForm(['cabinet/pin'], 'put', ['id'=>'formUpdatePin']) ?>
            <input type="hidden" name="id" value="" id="inpPutPinId">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Обновить ПИНКОД</h4>
            </div>
            <div class="modal-body">
                <span class="text-danger">При обновлении пинкода, с Вас спишется одна активация!</span>
                <p>Вы действительно хотите обновить ПИНКОД?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="submit" class="btn btn-primary">ДА, обновить</button>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>