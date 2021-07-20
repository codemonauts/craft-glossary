<?php

namespace codemonauts\glossary\controllers;

use codemonauts\glossary\elements\Glossary as GlossaryElement;
use codemonauts\glossary\elements\Term as TermElement;
use codemonauts\glossary\resources\GlossarySwitcher;
use Craft;
use craft\base\Element;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TermController extends Controller
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requirePermission('glossary:termEdit');

        return true;
    }

    public function actionIndex(): Response
    {
        return $this->renderTemplate('glossary/term/index');
    }

    public function actionEdit(int $termId = null, TermElement $term = null): Response
    {
        // Find or create new term to edit
        if ($termId !== null) {
            if ($term === null) {
                $term = TermElement::findOne(['id' => $termId, 'status' => null]);
                if (!$term) {
                    throw new NotFoundHttpException();
                }
            }
        } else if ($term === null) {
            $term = new TermElement();
            $term->id = 0;
        }

        // Register JS to switch glossary
        $this->getView()->registerAssetBundle(GlossarySwitcher::class);
        $this->getView()->registerJs('new Craft.GlossarySwitcher();');

        // Set variables
        $variables['term'] = $term;
        $variables['title'] = $term->id ? 'Edit term' : 'Create term';
        $variables['continueEditingUrl'] = 'glossary/term/{termId}';
        $variables['isNew'] = !$term->id;

        // Get all glossaries and prepare for switcher
        $variables['glossaries'] = [];
        $glossaries = GlossaryElement::findAll();
        foreach ($glossaries as $glossary) {
            $variables['glossaries'][] = [
                'label' => Craft::t('site', $glossary->title),
                'value' => $glossary->id,
            ];
        }

        // Create custom field layout
        $fieldLayout = $term->getFieldLayout();
        $form = $fieldLayout->createForm($term);
        $variables['tabs'] = $form->getTabMenu();
        $variables['fieldsHtml'] = $form->render();

        return $this->renderTemplate('glossary/term/_edit', $variables);
    }

    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // Find or create term to save
        $termId = $request->getBodyParam('termId');
        if ($termId) {
            $term = TermElement::findOne(['id' => $termId, 'status' => null]);
        } else {
            $term = new TermElement();
        }

        // Set element fields
        $term->term = $request->getBodyParam('term');
        $term->synonyms = $request->getBodyParam('synonyms');
        $term->enabled = (bool)$request->getBodyParam('enabled');
        $term->glossaryId = $request->getBodyParam('glossaryId');
        $term->caseSensitive = $request->getBodyParam('caseSensitive');
        $term->matchSubstring = $request->getBodyParam('matchSubstring');

        // Set custom fields
        $term->setFieldValuesFromRequest('fields');
        $term->setScenario(Element::SCENARIO_LIVE);

        // Save term
        if (Craft::$app->getElements()->saveElement($term)) {
            Craft::$app->getSession()->setNotice('Term saved.');

            return $this->redirectToPostedUrl($term);
        }

        Craft::$app->getSession()->setError('Term not saved.');

        Craft::$app->getUrlManager()->setRouteParams([
            'term' => $term,
        ]);

        return null;
    }

    public function actionSwitchGlossary(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $view = $this->getView();

        // Create new term and set element fields to posted values
        $term = new TermElement();
        $term->id = $request->getBodyParam('termId');
        $term->enabled = (bool)$request->getBodyParam('enabled');
        $term->term = $request->getBodyParam('term');
        $term->synonyms = $request->getBodyParam('synonyms');
        $term->glossaryId = $request->getBodyParam('glossaryId');
        $term->caseSensitive = $request->getBodyParam('caseSensitive');
        $term->matchSubstring = $request->getBodyParam('matchSubstring');
        $term->setFieldValuesFromRequest('fields');

        // Get new glossary to switch to
        $glossary = GlossaryElement::findOne(['id' => $term->glossaryId]);

        // Create new custom field layout based on new glossary
        $form = $glossary->getFieldLayout()->createForm($term);
        $tabs = $form->getTabMenu();

        return $this->asJson([
            'tabsHtml' => count($tabs) > 1 ? $view->renderTemplate('_includes/tabs', [
                'tabs' => $tabs,
            ]) : null,
            'fieldsHtml' => $form->render(),
            'headHtml' => $view->getHeadHtml(),
            'bodyHtml' => $view->getBodyHtml(),
        ]);
    }

    public function actionDelete(): ?Response
    {
        $this->requirePostRequest();

        // Get term to delete
        $termId = Craft::$app->getRequest()->getRequiredBodyParam('termId');
        $term = TermElement::findOne(['id' => $termId]);
        if ($term === null) {
            throw new NotFoundHttpException(Craft::t('glossary', 'Term not found.'));
        }

        // Delete term
        if (!Craft::$app->getElements()->deleteElement($term)) {
            Craft::$app->getSession()->setError(Craft::t('glossary', 'Could not delete term.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'term' => $term,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('glossary', 'Term deleted.'));

        return $this->redirectToPostedUrl($term);
    }
}
