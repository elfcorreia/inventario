<?php $inherits('base.tpl.php'); ?>

<?php $block('content') ?>
    <h1>Consultar Objetos</h1>

    <?php foreach ($objetos as $objeto): ?>
        <div>
            <p><?= $objeto['nome'] ?></p>
            <p><?= $objeto['tag'] ?></p>            
        </div>
    <?php endforeach ?>
<?php $end_block() ?>