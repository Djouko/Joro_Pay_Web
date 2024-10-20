<?php

namespace App\Notifications\User\AddMoney;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ApprovedMail extends Notification
{
    use Queueable;

    public $user;
    public $data;
    public $trx_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$data,$trx_id)
    {
        $this->user = $user;
        $this->data = $data;
        $this->trx_id = $trx_id;
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
        $user = $this->user;
        $data = $this->data;

        $trx_id = $this->trx_id;
        $date = Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');
        return (new MailMessage)
                    ->greeting(__("Hello")." ".$user->fullname." !")
                    ->subject(__("Add Money Via")." ". $data['currency']['name'])
                    ->line(__("Your add money request successful via")." ".$data['currency']['name']." ,".__("details of add money").":")
                    ->line(__("request Amount").": " . getAmount($data['amount']->requested_amount,2).' '. $data['amount']->default_currency)
                    ->line(__("Exchange Rate").": " ." 1 ". $data['amount']->default_currency.' = '. getAmount($data['amount']->sender_cur_rate,2).' '.$data['amount']->sender_cur_code)
                    ->line(__("Fees & Charges").": " . $data['amount']->total_charge.' '. $data['amount']->sender_cur_code)
                    ->line(__("Will Get").": " . getAmount($data['amount']->will_get,2).' '. $data['amount']->default_currency)
                    ->line(__("Total Payable Amount").": " . getAmount($data['amount']->total_amount,2).' '. $data['amount']->sender_cur_code)
                    ->line(__("web_trx_id").": " .$trx_id)
                    ->line(__("Status").": ".__("Success"))
                    ->line(__("Date And Time").": " .$dateTime)
                    ->line(__('Thank you for using our application!'));
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
