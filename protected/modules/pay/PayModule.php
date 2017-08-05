<?php

class PayModule extends CWebModule
{
    public function init(){
        $this->setImport([
            'application.extensions.pay.*'
        ]);
    }
}