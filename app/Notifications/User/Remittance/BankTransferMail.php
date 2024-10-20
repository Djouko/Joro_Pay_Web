<?php

namespace App\Notifications\User\Remittance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class BankTransferMail extends Notification
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
        if($data->transaction_type == 'bank-transfer') {
            return (new MailMessage())
                        ->greeting(__("Hello")." ".$user->fullname." !")
                        ->subject($data->title)
                        ->line(__("Send Remittance Email Heading").":")
                        ->line($data->title)
                        ->line(__("web_trx_id").": " .$trx_id)
                        ->line(__("Transaction Type").": " . ucwords(str_replace('-', ' ', @$data->transaction_type)))
                        ->line(__("request Amount").": " .$data->request_amount)
                        ->line(__("Exchange Rate").": " . $data->exchange_rate)
                        ->line(__("Fees & Charges").": " .$data->charges)
                        ->line(__("Total Payable Amount").": " .$data->payable)
                        ->line(__("sending Country").": " . $data->sending_country)
                        ->line(__("Receiving Country").": " . $data->receiving_country)
                        ->line(__("Receiver Name").": " . $data->receiver_name)
                        ->line(__("bank Name").": " . $data->alias)
                        ->line(__("Receiver Will Get").":" . $data->receiver_get)
                        ->line(__("Status").": " .$data->status)
                        ->line(__("Date And Time").": " .$dateTime)
                        ->line(__('Thank you for using our application!'));
        }else{
            return (new MailMessage())
                    ->greeting(__("Hello")." ".$user->fullname." !")
                    ->subject($data->title)
                    ->line(__("Send Remittance Email Heading").":")
                    ->line($data->title)
                    ->line(__("web_trx_id").": " .$trx_id)
                    ->line(__("Transaction Type").": " . ucwords(str_replace('-', ' ', @$data->transaction_type)))
                    ->line(__("request Amount").": " .$data->request_amount)
                    ->line(__("Exchange Rate").": " . $data->exchange_rate)
                    ->line(__("Fees & Charges").": " .$data->charges)
                    ->line(__("Total Payable Amount").": " .$data->payable)
                    ->line(__("sending Country").": " . $data->sending_country)
                    ->line(__("Receiving Country").": " . $data->receiving_country)
                    ->line(__("Receiver Name").": " . $data->receiver_name)
                    ->line(__("Pickup Point").": " . $data->alias)
                    ->line(__("Receiver Will Get").":" . $data->receiver_get)
                    ->line(__("Status").": " .$data->status)
                    ->line(__("Date And Time").": " .$dateTime)
                    ->line(__('Thank you for using our application!'));

        }
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
