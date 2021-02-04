<?php

require_once "views.php";

use function routing\{route, find};

route("/", "index_view");

route("/objetos/consultar", "objetos_consultar_view");
route("/objetos/novo", "objetos_novo_view");
route("/objetos/importar", "objetos_importar_view");
