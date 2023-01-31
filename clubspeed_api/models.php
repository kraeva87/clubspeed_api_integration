<?php
include_once 'conf.php';

class BookRequest
{
    public $api_root;
    public $api_key;
    public $package;
    public $participants;
    public $date;
    public $time;
    public $name;
    public $email;
    public $phone;
    public $card_number;
    public $card_cvv;
    public $card_holder;
    public $card_expiry;
    public $price;
    public $trans_id;
    public $created;
    public $reservation_id;
    public $event_id;
    public $heat_id;
    public $customer_id;
    public $check_id;
    public $payment_id;

    function __construct($post)
    {
        $this->api_root = API_ROOTS;
        $this->api_key = API_KEYS;
        if (isset($post['package'])) $this->package = $post['package'];
        if (isset($post['participants'])) $this->participants = $post['participants'];
        if (isset($post['date'])) $this->date = $post['date'];
         if (isset($post['time'])) $this->time = $post['time'];
        if (isset($post['racer_name'])) $this->name = $post['racer_name'];
        if (isset($post['racer_email'])) $this->email = $post['racer_email'];
        if (isset($post['racer_phone'])) $this->phone = $post['racer_phone'];
        if (isset($post['card_number'])) $this->card_number = $post['card_number'];
        if (isset($post['card_cvv'])) $this->card_cvv = $post['card_cvv'];
        if (isset($post['card_holder'])) $this->card_holder = $post['card_holder'];
        if (isset($post['card_expiry'])) $this->card_expiry = $post['card_expiry'];
        $micro_date = microtime();
        $date_array = explode(" ", $micro_date);
        $this->created = date('Y-m-d\TH:i:s.') . $date_array[0];
    }
}
?>
