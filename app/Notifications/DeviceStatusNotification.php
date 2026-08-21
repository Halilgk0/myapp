<?php

namespace App\Notifications;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Notifications\Messages\TwilioSmsMessage;
use App\Notifications\Channels\TwilioSmsChannel;

class DeviceStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The device instance.
     *
     * @var \App\Models\Device
     */
    public $device;
    
    /**
     * The status change type (online/offline).
     *
     * @var string
     */
    public $status;
    
    /**
     * The timestamp of the status change.
     *
     * @var \Illuminate\Support\Carbon
     */
    public $timestamp;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Device  $device
     * @param  string  $status  'online' or 'offline'
     * @return void
     */
    public function __construct(Device $device, string $status)
    {
        $this->device = $device;
        $this->status = $status;
        $this->timestamp = now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database', 'broadcast', 'mail'];
        
        // Add SMS channel if phone number is available
        if (method_exists($notifiable, 'routeNotificationForTwilio')) {
            $channels[] = TwilioSmsChannel::class;
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $device = $this->device;
        $status = $this->status;
        $time = $this->timestamp->format('Y-m-d H:i:s');
        
        $subject = "Device {$device->name} is now {$status}";
        $url = route('admin.devices.show', $device);
        
        return (new MailMessage)
                    ->subject($subject)
                    ->line("Device: {$device->name} (ID: {$device->device_code})")
                    ->line("Status: " . ucfirst($status))
                    ->line("Time: {$time}")
                    ->line("Location: " . ($device->latitude && $device->longitude ? 
                        "{$device->latitude}, {$device->longitude}" : 'Not available'))
                    ->action('View Device', $url)
                    ->line('Thank you for using our application!');
    }
    
    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'device_id' => $this->device->id,
            'device_name' => $this->device->name,
            'status' => $this->status,
            'timestamp' => $this->timestamp->toDateTimeString(),
            'message' => "Device {$this->device->name} is now {$this->status}",
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'device_id' => $this->device->id,
            'device_name' => $this->device->name,
            'device_code' => $this->device->device_code,
            'status' => $this->status,
            'timestamp' => $this->timestamp->toDateTimeString(),
            'message' => "Device {$this->device->name} is now {$this->status}",
            'url' => route('admin.devices.show', $this->device),
        ];
    }
    
    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    /**
     * Get the Twilio SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \App\Notifications\Messages\TwilioSmsMessage
     */
    public function toTwilioSms($notifiable)
    {
        $device = $this->device;
        $status = $this->status;
        $time = $this->timestamp->format('Y-m-d H:i:s');
        
        $message = "Device: {$device->name} ({$device->device_code})\n";
        $message .= "Status: " . ucfirst($status) . "\n";
        $message .= "Time: {$time}\n";
        
        if ($device->latitude && $device->longitude) {
            $message .= "Location: {$device->latitude}, {$device->longitude}\n";
            $message .= "Map: https://www.google.com/maps?q={$device->latitude},{$device->longitude}";
        } else {
            $message .= "Location: Not available";
        }
        
        return (new TwilioSmsMessage())
            ->content($message);
    }
    
    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    public function viaQueues()
    {
        return [
            'mail' => 'emails',
            'database' => 'database',
            'broadcast' => 'broadcasts',
            TwilioSmsChannel::class => 'sms',
        ];
    }
}
