<?php return array(
    'root' => array(
        'name' => 'wedevs/wp-user-frontend-pro',
        'pretty_version' => 'dev-develop',
        'version' => 'dev-develop',
        'reference' => '6fd09de0171498e7cf696d4f06da0d1e48affde3',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '2a9170263fcd9cc4fd0b50917293c21d6c1a5bfe',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(
                0 => '2.x-dev',
            ),
            'dev_requirement' => false,
        ),
        'wedevs/wp-user-frontend-pro' => array(
            'pretty_version' => 'dev-develop',
            'version' => 'dev-develop',
            'reference' => '6fd09de0171498e7cf696d4f06da0d1e48affde3',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wedevs/wp-utils' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => 'e5d072e9ed80b8af8fcd3cb0ca7a8a749568fa5f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wedevs/wp-utils',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
    ),
);
