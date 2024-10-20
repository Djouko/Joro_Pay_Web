<?php

namespace App\Notifications\PaymentLink\Gateway;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\Constants\PaymentGatewayConst;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BuyerNotification extends Notification
{
    use Queueable;

    private $user;
    private $data;
    private $trx_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $data, $trx_id)
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
        $type = 'payment_link';


        $transaction_type = $data['transaction_type'] ?? $data['type'];
        $request_amount = $data['validated']['amount'];

        $date = Carbon::now();
        $datetime = dateFormat('Y-m-d h:i:s A', $date);
        return (new MailMessage)
            ->greeting(__("Hello")." ".$user['name']." !")
            ->subject(__("Payment Link Transaction via")." ". $transaction_type)
            ->line(__("Your payment request successfully via")." ".$data[$type]->currency." ,".__("details transactions").":")
            ->line(__("request Amount").": " . getAmount($request_amount,2).' '. $data[$type]->currency)
            ->line(__("web_trx_id").": " .$trx_id)
            ->line(__("Status").": ".__("success"))
            ->line(__("Date And Time").": " .$datetime)
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
