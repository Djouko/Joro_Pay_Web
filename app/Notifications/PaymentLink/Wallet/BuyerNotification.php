<?php

namespace App\Notifications\PaymentLink\Wallet;

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

        $status = __("success");

        $date = Carbon::now();
        $dateTime = dateFormat('Y-m-d h:i:s A', $date);
        return (new MailMessage)
            ->greeting("Hello ".$user->fullname." !")
            ->subject(__("Payment Link Transaction via")." ". $data['transaction_type'])
            ->line(__("Your payment request successfully via")." ".__("rWallet")." ,".__("details of transactions").":")
            ->line(__("Request Amount").": " . get_amount($data['amount'],$data['charge_calculation']['sender_cur_code'],4))
            ->line(__("Exchange Rate").": " . get_amount(1,$data['charge_calculation']['sender_cur_code']).' = '. get_amount($data['charge_calculation']['exchange_rate'],$data['charge_calculation']['receiver_currency_code'],4))
            ->line(__("Fees & Charges").": " . get_amount($data['charge_calculation']['total_charge'], $data['charge_calculation']['sender_cur_code'],4))
            ->line(__("Payable Amount").": " . get_amount($data['charge_calculation']['sender_payable'], $data['charge_calculation']['sender_cur_code'],4))
            ->line(__("web_trx_id").": " .$trx_id)
            ->line(__("Status").": ".$status)
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
