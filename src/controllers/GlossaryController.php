<?php

namespace codemonauts\glossary\controllers;

use codemonauts\glossary\elements\Glossary as GlossaryElement;
use codemonauts\glossary\Glossary as GlossaryPlugin;
use codemonauts\glossary\services\providers\Custom;
use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;

class GlossaryController extends Controller
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requirePermission('glossary:glossaryEdit');

        return true;
    }

    public function actionIndex()
    {
        return $this->renderTemplate('glossary/glossary/index');
    }

    public function actionEdit(int $glossaryId = null, GlossaryElement $glossary = null)
    {
        // Find or create new glossary to edit
        if ($glossaryId !== null) {
            if ($glossary === null) {
                $glossary = GlossaryElement::findOne(['id' => $glossaryId]);
                if (!$glossary) {
                    throw new NotFoundHttpException();
                }
            }
        } else {
            if ($glossary === null) {
                $glossary = new GlossaryElement();
            }
        }

        // Set variables
        $variables['glossary'] = $glossary;
        $variables['title'] = $glossary->id ? 'Edit glossary' : 'Create glossary';
        $variables['continueEditingUrl'] = 'glossary/glossary/{glossaryId}';
        $variables['isNew'] = !$glossary->id;
        $variables['fieldLayout'] = $glossary->getFieldLayout();

        $this->renderTemplate('glossary/glossary/_edit', $variables);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // Find or create new glossary to save
        $glossaryId = $request->getBodyParam('glossaryId');
        if ($glossaryId) {
            $glossary = GlossaryElement::findOne(['id' => $glossaryId]);
        } else {
            $glossary = new GlossaryElement();
        }

        // Set element fields
        $glossary->title = $request->getBodyParam('title');
        $glossary->handle = $request->getBodyParam('handle');
        $glossary->provider = Custom::class;
        $glossary->default = (bool)$request->getBodyParam('default');
        $glossary->template = $request->getBodyParam('template');
        $glossary->css = $request->getBodyParam('css');
        $glossary->script = $request->getBodyParam('script');

        // Set custom field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = GlossaryElement::class;

        // Save field layout
        if (Craft::$app->getFields()->saveLayout($fieldLayout)) {
            // Save glossary with field layout
            $glossary->fieldLayoutId = $fieldLayout->id;
            if (Craft::$app->getElements()->saveElement($glossary)) {
                Craft::$app->getSession()->setNotice(Craft::t('glossary', 'Glossary saved.'));

                return $this->redirectToPostedUrl($glossary);
            }
        }

        Craft::$app->getSession()->setError(Craft::t('glossary', 'Glossary not saved.'));

        Craft::$app->getUrlManager()->setRouteParams([
            'glossary' => $glossary,
        ]);

        return null;
    }

    public function actionDelete()
    {
        $this->requirePostRequest();

        // Get glossary to delete
        $glossaryId = Craft::$app->getRequest()->getRequiredBodyParam('glossaryId');
        $glossary = GlossaryElement::findOne(['id' => $glossaryId]);
        if ($glossary === null) {
            throw new NotFoundHttpException(Craft::t('glossary', 'Glossary not found.'));
        }

        // Delete glossary
        if (!Craft::$app->getElements()->deleteElement($glossary)) {
            Craft::$app->getSession()->setError(Craft::t('glossary', 'Couldn’t delete glossary.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'glossary' => $glossary,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('glossary', 'Glossary deleted.'));

        return $this->redirectToPostedUrl($glossary);
    }
}
