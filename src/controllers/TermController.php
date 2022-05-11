<?php

namespace codemonauts\glossary\controllers;

use codemonauts\glossary\Glossary as GlossaryPlugin;
use codemonauts\glossary\elements\Glossary;
use codemonauts\glossary\elements\Glossary as GlossaryElement;
use codemonauts\glossary\elements\Term;
use codemonauts\glossary\elements\Term as TermElement;
use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
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

    public function actionCreate(): ?Response
    {
        $user = Craft::$app->getUser()->getIdentity();

        // Create & populate the draft
        $term = Craft::createObject(Term::class);
        $term->enabled = true;

        // Glossary
        $defaultGlossary = GlossaryPlugin::$plugin->getGlossaries()->getDefaultGlossary();
        $term->glossaryId = $defaultGlossary->id;

        // Custom fields
        foreach ($term->getFieldLayout()->getCustomFields() as $field) {
            if (($value = $this->request->getQueryParam($field->handle)) !== null) {
                $term->setFieldValue($field->handle, $value);
            }
        }

        // Save it
        $term->setScenario(Element::SCENARIO_ESSENTIALS);
        if (!Craft::$app->getDrafts()->saveElementAsDraft($term, $user->getId(), null, null, false)) {
            return $this->asModelFailure($term, Craft::t('app', 'Couldn’t create {type}.', [
                'type' => Entry::lowerDisplayName(),
            ]), 'entry');
        }

        $editUrl = $term->getCpEditUrl();

        $response = $this->asModelSuccess($term, Craft::t('app', '{type} created.', [
            'type' => Entry::displayName(),
        ]), 'entry', array_filter([
            'cpEditUrl' => $this->request->isCpRequest ? $editUrl : null,
        ]));

        if (!$this->request->getAcceptsJson()) {
            $response->redirect(UrlHelper::urlWithParams($editUrl, [
                'fresh' => 1,
            ]));
        }

        return $response;
    }

    public function actionDelete(): ?Response
    {
        $this->requirePostRequest();

        // Get term to delete
        $termId = Craft::$app->getRequest()->getRequiredBodyParam('termId');
        $term = TermElement::find()
            ->status(null)
            ->id($termId)
            ->one();

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
