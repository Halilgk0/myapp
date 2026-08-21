<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client as TwilioClient;

class TwilioSmsChannel
{
    /**
     * The Twilio client instance.
     *
     * @var \Twilio\Rest\Client
     */
    protected $twilio;

    /**
     * Create a new Twilio channel instance.
     *
     * @param  \Twilio\Rest\Client  $twilio
     * @return void
     */
    public function __construct(TwilioClient $twilio)
    {
        $this->twilio = $twilio;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('twilio', $notification)) {
            return;
        }

        $message = $notification->toTwilioSms($notifiable);

        return $this->twilio->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => trim($message->content),
        ]);
    }
}
