<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeUser extends Notification
{
    use Queueable;

    /**
     * @var
     */
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('You recently created an account on the AOD Division Tracker, a tool for managing members assigned to gaming divisions in the AOD clan.')
            ->line("Your default role on the tracker is: **User**. If you are an NCO in your division, you will need to have someone in your leadership update your account permissions to the appropriate level before you can access the member recruitment tools and other management areas.")
            ->action('Visit the Tracker', route('home'))
            ->line("If you have any questions, visit the [Tracker Documentation](" . route('help') . ") section.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
