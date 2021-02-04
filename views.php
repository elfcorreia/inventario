<?php

use function templating\{render};
use function pdo\get_pdo;

require_once "forms.php";
require_once "models.php";

function index_view() {
    echo render('index.tpl.php');
}

function objetos_consultar_view() {
    $q = new Query(Objeto::class);
    if (isset($_GET['s'])) {
        $q->filterByNomeOrTag($_GET['s']);
    }
    $stmt = get_pdo()->prepare($q->getSQL());
    $stmt->execute($q->getArgs());
    echo render('objeto/list.tpl.php', [
        'objetos' => $stmt
    ]);
}

function objetos_novo_view() {
    $form = new ObjetoNovoForm();
    if (isset($_POST) && $form->bound($_POST) && $form->isValid()) {
        $o = new Objeto($form->getCleanedData());
        $o->setCreatedAt(new \DateTime());
        $o->setModifiedAt(new \DateTime());
        $o->save(get_pdo());
        header('Location: /');
        return;
    }
    echo render('objeto/create.tpl.php', [
        'form' => $form
    ]);
}

function objetos_importar_view() {
        echo render('objeto/importar.tpl.php');
}