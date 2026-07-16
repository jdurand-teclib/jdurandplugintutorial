<?php

namespace GlpiPlugin\Jdplugintutorial;

use NotificationTarget;

class NotificationTargetSuperAsset extends NotificationTarget
{

    public function getEvents(): array
    {
        return [
            'my_event_key' => __('My event label', 'jdplugintutorial')
        ];
    }

    public function getDatasForTemplate($event, $options = []): void
    {
        $this->datas['##superasset.phonenumber##'] = "This is a valid phone number";
        $this->datas['##jdplugintutorial.name##'] = __('Name');
    }
}
