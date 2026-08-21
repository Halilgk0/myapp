<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendActivationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The activation token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('device.activate.form', ['token' => $this->token]);
        $expires = config('activation.expires');
        
        return (new MailMessage)
            ->subject(config('activation.subject') . ' - ' . config('app.name'))
            ->greeting('Merhaba ' . $notifiable->name . '!')
            ->line('Hesabınızı etkinleştirmek için lütfen aşağıdaki bağlantıya tıklayın.')
            ->action('Hesabımı Etkinleştir', $url)
            ->line('Eğer bu işlemi siz yapmadıysanız, bu e-postayı dikkate almayınız.')
            ->line('Bu bağlantı ' . $expires . ' saat boyunca geçerlidir.')
            ->salutation('Saygılarımızla,\n' . config('app.name') . ' Ekibi');
    }
    
    /**
     * Determine which queues should be used for this notification.
     *
     * @return array
     */
    public function viaQueues()
    {
        return [
            'mail' => config('activation.queue') ? 'mail' : 'sync',
        ];
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
            //
        ];
    }
}
