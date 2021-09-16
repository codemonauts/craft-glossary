<?php

namespace codemonauts\glossary\variables;

use codemonauts\glossary\elements\Glossary;
use codemonauts\glossary\elements\Term;

class GlossaryVariable
{
    public function glossaries()
    {
        return Glossary::find();
    }

    public function terms()
    {
        return Term::find();
    }
}

