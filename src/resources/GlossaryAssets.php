<?php

namespace codemonauts\glossary\resources;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class GlossaryAssets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@codemonauts/glossary/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/GlossarySwitcher.js',
        ];

        parent::init();
    }
}
