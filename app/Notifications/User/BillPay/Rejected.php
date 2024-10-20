<?php

namespace App\Notifications\User\BillPay;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class Rejected extends Notification
{
    use Queueable;

    public $user;
    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$data)
    {
        $this->user = $user;
        $this->data = $data;
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
        $trx_id = $this->data->trx_id;
        $date = Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');

        return (new MailMessage)
                    ->greeting(__("Hello")." ".$user->fullname." !")
                    ->subject(__("Bill Pay For")." ". $data->bill_type.' ('.$data->bill_number.' )')
                    ->line(__("Admin rejected your bill pay request")." ".$data->bill_type." ,".__("details of bill pay").":")
                  ->line(__("web_trx_id").": " .$trx_id)
                    ->line(__('request Amount').": " . getAmount($data->request_amount,4).' '.get_default_currency_code())
                    ->line(__('Fees & Charges').": " . getAmount($data->charges,4).' '.get_default_currency_code())
                    ->line(__('Total Payable Amount').": " . get_amount($data->payable,get_default_currency_code(),'4'))
                   ->line(__("Status").": " .$data->status)
                    ->line(__('Rejection Reason').": ". $data->reason)
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
