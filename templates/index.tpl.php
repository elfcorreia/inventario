<?php $inherits('base.tpl.php'); ?>

<?php $block('content') ?>
    <h1>Inventário</h1>
    <div class="dashboard">
        <h1>Objetos</h1>
        <div style="display: flex; flex-direction: row; flex-wrap: wrap">
            <div class="item"><a href="/objetos/consultar">Consultar</a></div>
            <div class="item"><a href="/objetos/novo">Novo</a></div>
            <div class="item"><a href="/objetos/importar">Importar</a></div>            
        </div>
        <h1>Listagens</h1>
        <div style="display: flex; flex-direction: row; flex-wrap: wrap">
            <div class="item"><a href="lista/consultar">Consultar</a></div>
            <div class="item"><a href="lista/nova">Nova</a></div>
            <div class="item"><a href="lista/importar">Importar</a></div>
            <div class="item"><a href="lista/conferir">Conferir</a></div>
        </div>
        <h1>Relatórios</h1>
        <div style="display: flex; flex-direction: row; flex-wrap: wrap">
            <div class="item">Consultar</div>
            <div class="item">Cadastrar</div>
        </div>
        <h1>Sincronização</h1>
        <div style="display: flex; flex-direction: row; flex-wrap: wrap">
            <div class="item">Importar</div>
            <div class="item">Exportar</div>
        </div>
    </div>
<?php $end_block() ?>