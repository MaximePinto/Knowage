<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\ContainerXaqebtt\appDevDebugProjectContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/ContainerXaqebtt/appDevDebugProjectContainer.php') {
    touch(__DIR__.'/ContainerXaqebtt.legacy');

    return;
}

if (!\class_exists(appDevDebugProjectContainer::class, false)) {
    \class_alias(\ContainerXaqebtt\appDevDebugProjectContainer::class, appDevDebugProjectContainer::class, false);
}

return new \ContainerXaqebtt\appDevDebugProjectContainer(array(
    'container.build_hash' => 'Xaqebtt',
    'container.build_id' => 'd08aa749',
    'container.build_time' => 1526489559,
), __DIR__.\DIRECTORY_SEPARATOR.'ContainerXaqebtt');
