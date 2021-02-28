<?php

namespace codemonauts\glossary\services;

use codemonauts\glossary\elements\Glossary as GlossaryElement;
use yii\base\Component;

class Glossaries extends Component
{
    /**
     * Returns the default glossary.
     *
     * @return GlossaryElement|null
     */
    public function getDefaultGlossary(): ?GlossaryElement
    {
        return GlossaryElement::find()
            ->default(true)
            ->one();
    }

    /**
     * Returns a glossary by its ID.
     *
     * @param int $glossaryId The ID of the glossary to return.
     *
     * @return GlossaryElement|null
     */
    public function getGlossaryById(int $glossaryId): ?GlossaryElement
    {
        return GlossaryElement::find()
            ->id($glossaryId)
            ->one();
    }

    /**
     * Returns a glossary by its handle.
     *
     * @param string $handle The handle of the glossary to return.
     *
     * @return GlossaryElement|null
     */
    public function getGlossaryByHandle(string $handle): ?GlossaryElement
    {
        return GlossaryElement::find()
            ->handle($handle)
            ->one();
    }

    /**
     * Returns all available glosseries.
     *
     * @return GlossaryElement[]
     */
    public function getAllGlossaries(): array
    {
        return GlossaryElement::find()
            ->orderBy('title asc')
            ->all();
    }
}
