<?php

namespace codemonauts\glossary\resources;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class GlossaryFrontend extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = '@codemonauts/glossary/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Glossary.js',
        ];

        $this->css = [
            'css/tippy.css',
            'css/light.css',
        ];

        parent::init();
    }
}
