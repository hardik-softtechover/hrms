<?php

namespace App\Notifications;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveApproved extends Notification
{
    use Queueable;

    public function __construct(public Leave $leave) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = ucfirst($this->leave->pay_type ?? 'paid');

        return (new MailMessage)
            ->subject('Your leave has been approved')
            ->greeting("Hi {$notifiable->name},")
            ->line("Your leave from {$this->leave->from_date->format('d M Y')} to {$this->leave->to_date->format('d M Y')} has been approved.")
            ->line("Total days: {$this->leave->total_days}")
            ->line("Type: {$type}")
            ->line($this->leave->admin_note ? "Note from HR: {$this->leave->admin_note}" : 'Have a good break!')
            ->salutation('— HR Team');
    }
}
