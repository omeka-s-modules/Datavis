<?php
namespace Datavis;

use Laminas\Router\Http;

return [
    'datavis_dataset_types' => [
        'invokables' => [
            'count_items_item_sets' => DatasetType\CountItemsItemSets::class,
            'count_items_classes' => DatasetType\CountItemsClasses::class,
            'count_items_properties' => DatasetType\CountItemsProperties::class,
            'count_items_property_values' => DatasetType\CountItemsPropertyValues::class,
            'count_property_values' => DatasetType\CountPropertyValues::class,
        ],
    ],
    'datavis_diagram_types' => [
        'invokables' => [
            'column_chart' => DiagramType\ColumnChart::class,
            'bar_chart' => DiagramType\BarChart::class,
            'pie_chart' => DiagramType\PieChart::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => sprintf('%s/../language', __DIR__),
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            sprintf('%s/../view', __DIR__),
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            sprintf('%s/../src/Entity', __DIR__),
        ],
        'proxy_paths' => [
            sprintf('%s/../data/doctrine-proxies', __DIR__),
        ],
    ],
    'service_manager' => [
        'factories' => [
            'Datavis\DatasetTypeManager' => Service\DatasetTypeManagerFactory::class,
            'Datavis\DiagramTypeManager' => Service\DiagramTypeManagerFactory::class,
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'datavis_visualizations' => Api\Adapter\DatavisVisAdapter::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Datavis\Controller\SiteAdmin\Index' => Controller\SiteAdmin\IndexController::class,
            'Datavis\Controller\Site\Index' => Controller\Site\IndexController::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'datavis' => Service\ControllerPlugin\DatavisFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            'Datavis\Form\DatasetTypeForm' => Service\Form\DatasetTypeFormFactory::class,
            'Datavis\Form\DatavisForm' => Service\Form\DatavisFormFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'datavis' => Service\ViewHelper\DatavisFactory::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'datavis' => BlockLayout\Datavis::class,
        ],
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Data Visualization', // @translate
                'route' => 'admin/site/slug/datavis',
                'action' => 'index',
                'useRouteMatch' => true,
                'pages' => [
                    [
                        'route' => 'admin/site/slug/datavis',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'site' => [
                        'child_routes' => [
                            'slug' => [
                                'child_routes' => [
                                    'datavis' => [
                                        'type' => Http\Segment::class,
                                        'options' => [
                                            'route' => '/data-visualization[/:action[/:id]]',
                                            'constraints' => [
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                'id' => '\d+',
                                            ],
                                            'defaults' => [
                                                '__NAMESPACE__' => 'Datavis\Controller\SiteAdmin',
                                                'controller' => 'index',
                                                'action' => 'browse',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'site' => [
                'child_routes' => [
                    'datavis' => [
                        'type' => Http\Segment::class,
                        'options' => [
                            'route' => '/data-visualization/:action/:id',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Datavis\Controller\Site',
                                'controller' => 'index',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
