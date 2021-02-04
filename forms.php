<?php

use Forms\Form;
use Forms\Fields\CharField;

class ObjetoNovoForm extends Form {
    public function makeFields(): void {
        $this->nome = new CharField();
        $this->nome->setLabel('Nome');
        $this->tag = new CharField();
        $this->tag->setLabel('Tag');
        $this->tag->setRequired(false);
    }
}