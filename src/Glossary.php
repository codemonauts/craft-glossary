<?php

namespace codemonauts\glossary;

use codemonauts\glossary\fieldlayoutelements\CaseSensitivityField;
use codemonauts\glossary\fieldlayoutelements\MatchSubstringField;
use codemonauts\glossary\fieldlayoutelements\SynonymsField;
use codemonauts\glossary\services\Glossaries;
use codemonauts\glossary\elements\Glossary as GlossaryElement;
use codemonauts\glossary\fieldlayoutelements\TermField;
use codemonauts\glossary\twigextensions\GlossaryFilter;
use codemonauts\glossary\variables\GlossaryVariable;
use Craft;
use \craft\base\Plugin;
use craft\events\DefineFieldLayoutFieldsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\models\FieldLayout;
use craft\services\Gc;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use yii\base\Event;

/**
 * Class Glossary
 *
 * @property Glossaries $glossaries
 */
class Glossary extends Plugin
{
    /**
     * @inheritDoc
     */
    public $hasCpSettings = false;

    /**
     * @inheritDoc
     */
    public $schemaVersion = '1.0.0';

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();

        // Register services as components
        $this->setComponents([
            'glossaries' => Glossaries::class,
        ]);

        // Register Twig extension
        if (Craft::$app->request->getIsSiteRequest()) {
            Craft::$app->view->registerTwigExtension(new GlossaryFilter());
        }

        // Register variables
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;
            $variable->set('glossary', GlossaryVariable::class);
        });

        // Register garbage collection routine
        Event::on(Gc::class, Gc::EVENT_RUN, function() {
            Craft::$app->gc->hardDelete([
                '{{%glossary_glossaries}}',
                '{{%glossary_terms}}',
            ]);
        });

        // Register things only needed in CP requests
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_cpInit();
        }
    }

    /**
     * Initialize plugin for CP requests
     */
    private function _cpInit()
    {
        // Register permissions
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['glossary'] = [
                'glossary:glossaryEdit' => ['label' => 'Edit Glossaries'],
                'glossary:termEdit' => ['label' => 'Edit Terms'],
            ];
        });

        // Register CP routes
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['glossary/glossaries'] = 'glossary/glossary/index';
            $event->rules['glossary/glossary/new'] = 'glossary/glossary/edit';
            $event->rules['glossary/glossary/<glossaryId:\d+>'] = 'glossary/glossary/edit';
            $event->rules['glossary/terms'] = 'glossary/term/index';
            $event->rules['glossary/term/new'] = 'glossary/term/edit';
            $event->rules['glossary/term/<termId:\d+>'] = 'glossary/term/edit';
        });

        // Register field layout
        Event::on(FieldLayout::class, FieldLayout::EVENT_DEFINE_STANDARD_FIELDS, function(DefineFieldLayoutFieldsEvent $event) {
            /** @var FieldLayout $fieldLayout */
            $fieldLayout = $event->sender;

            if ($fieldLayout->type == GlossaryElement::class) {
                $event->fields[] = TermField::class;
                $event->fields[] = SynonymsField::class;
                $event->fields[] = CaseSensitivityField::class;
                $event->fields[] = MatchSubstringField::class;
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function getCpNavItem(): array
    {
        $navItem = parent::getCpNavItem();
        $subNavs = [];

        $currentUser = Craft::$app->getUser()->getIdentity();

        if ($currentUser->can('glossary:glossaryEdit')) {
            $subNavs['glossaries'] = [
                'url' => 'glossary/glossaries',
                'label' => 'Glossaries',
            ];
        }

        if ($currentUser->can('glossary:termEdit')) {
            $subNavs['terms'] = [
                'url' => 'glossary/terms',
                'label' => 'Terms',
            ];
        }

        $navItem['subnav'] = $subNavs;

        return $navItem;
    }

    /**
     * Returns the glossaries component.
     *
     * @return Glossaries
     */
    public function getGlossaries()
    {
        return $this->get('glossaries');
    }
}
