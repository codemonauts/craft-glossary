<?php

namespace codemonauts\glossary\variables;

use codemonauts\glossary\elements\Glossary;

class GlossaryVariable
{
    public function glossaries()
    {
        return Glossary::find();
    }
}

