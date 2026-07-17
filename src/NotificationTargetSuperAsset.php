<?php

namespace GlpiPlugin\Jdplugintutorial;

use NotificationTarget;

/**
 * Extension of NotificationTarget Class
 *
 * @template T
 * @extends NotificationTarget<T>
 */
class NotificationTargetSuperAsset extends NotificationTarget
{

    public function getEvents(): array
    {
        return [
            'my_event_key' => __('My event label', 'jdplugintutorial')
        ];
    }

    /**
     * Summary of getDatasForTemplate
     * @param string $event The event key
     * @param array<string, string> $options A list of options
     * @return void
     */
    public function getDatasForTemplate(string $event, array $options = []): void
    {
        $this->data['##superasset.phonenumber##'] = "This is a valid phone number";
        $this->data['##jdplugintutorial.name##'] = __('Name');
    }
}
