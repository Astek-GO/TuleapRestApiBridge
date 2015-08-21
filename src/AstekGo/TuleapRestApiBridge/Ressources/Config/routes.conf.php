<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */

/** @noinspection PhpUndefinedVariableInspection */
if (!$controller instanceof AstekGo\TuleapRestApiBridge\Controller) {
    throw new \Exception(__NAMESPACE__.'::'.__CLASS__.' : Invalid routes configuration (invalid controller)');
}
// When there is a redirect base, add a prefix to routes definitions
$pathPrefix = '';
/** @noinspection PhpUndefinedVariableInspection */
if ('' != $redirectBase) {
    $pathPrefix = '/'.$redirectBase;
}
return array(
    array(
        'name' => 'getUserToken',
        'httpMethod' => array('POST', 'GET'),
        'path' => '@^'.$pathPrefix.'/token/(?:(?P<username>[^/]++))/(?:(?P<password>[^/]+?)?)/?$',
        'callback' => function (Klein\Request $request) use ($controller) {
            $controller->getUserToken($request->param('username'), $request->param('password'));
        },
    ),
    array(
        'name' => 'getArtifactsList',
        'httpMethod' => array('POST', 'GET'),
        'path' => '@^'.$pathPrefix.'/tracker/(?:(?P<trackerId>[0-9]++))/summary-id/(?:(?P<summaryFieldId>[0-9]++))/description-id/(?:(?P<descriptionFieldId>[0-9]+?))?/created-id/(?:(?P<createdFieldId>[0-9]+?))?/updated-id/(?:(?P<updatedFieldId>[0-9]+?))?/closed-id/(?:(?P<closedFieldId>[0-9]+?))?/tasks-list/?$',
        'callback' => function (Klein\Request $request) use ($controller) {
            $trackerFields = array(
                'summaryFieldId' => $request->param('summaryFieldId'),
                'descriptionFieldId' => $request->param('descriptionFieldId'),
                'createdFieldId' => $request->param('createdFieldId'),
                'updatedFieldId' => $request->param('updatedFieldId'),
                'closedFieldId' => $request->param('closedFieldId'),
            );
            $controller->getArtifactsList($request->param('trackerId'), $trackerFields);
        },
    ),
    array(
        'name' => 'getArtifact',
        'httpMethod' => array('POST', 'GET'),
        'path' => '@^'.$pathPrefix.'/artifact/(?:(?P<id>[0-9]++))/summary-id/(?:(?P<summaryFieldId>[0-9]++))/description-id/(?:(?P<descriptionFieldId>[0-9]+?))?/created-id/(?:(?P<createdFieldId>[0-9]+?))?/updated-id/(?:(?P<updatedFieldId>[0-9]+?))?/closed-id/(?:(?P<closedFieldId>[0-9]+?))?/?$',
        'callback' => function (Klein\Request $request) use ($controller) {
            $trackerFields = array(
                'summaryFieldId' => $request->param('summaryFieldId'),
                'descriptionFieldId' => $request->param('descriptionFieldId'),
                'createdFieldId' => $request->param('createdFieldId'),
                'updatedFieldId' => $request->param('updatedFieldId'),
                'closedFieldId' => $request->param('closedFieldId'),
            );
            $controller->getArtifact($request->param('id'), $trackerFields);
        },
    ),
    array(
        'name' => 'getIDEConfiguration',
        'httpMethod' => array('POST', 'GET'),
        'path' => '@^'.$pathPrefix.'(?:/?(?:(?:/config/?)|(?:/doc(?:umentation)?/?))?)?$',
        'callback' => function () use ($controller) {
            $controller->getIDEConfiguration();
        },
    ),
);
