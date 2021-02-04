<?php $inherits('base.tpl.php'); ?>

<?php $block('content') ?>
    <h1>Novo Objeto</h1>

    <form method="post">
        <?= $form ?>
        <button type="submit">Salvar</button>
    </form>

<?php $end_block() ?>