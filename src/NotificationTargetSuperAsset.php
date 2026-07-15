<?php

namespace GlpiPlugin\Jdplugintutorial;

use NotificationTarget;

class NotificationTargetSuperasset extends NotificationTarget
{

    function getEvents()
    {
        return [
            'my_event_key' => __('My event label', 'jdplugintutorial')
        ];
    }

    function getDatasForTemplate($event, $options = [])
    {
        $this->datas['##myplugin.name##'] = __('Name');
    }
}
