<?php
include_once 'conf.php';

class BookRequest
{
    public $location;
    public $api_root;
    public $api_key;
    public $package;
    public $event;
    public $kart_type;
    public $racers;
    public $hours;
    public $date;
    public $time;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $card_number;
    public $card_cvv;
    public $card_holder;
    public $card_expiry_month;
    public $card_expiry_year;
    public $price;
    public $trans_id;
    public $created;
    public $reservation_id;
    public $event_id;
    public $room_reservation_id;
    public $customer_id;
    public $check_id;
    public $payment_id;

    function __construct($post)
    {
        if (isset($post['location'])) {
            $this->location = $post['location'];
            $this->api_root = API_ROOTS[$post['location']];
            $this->api_key = API_KEYS[$post['location']];
        }
        if (isset($post['resPack'])) $this->package = $post['resPack'];
        if (isset($post['event'])) $this->event = $post['event'];
        if (isset($post['resKart'])) $this->kart_type = $post['resKart'];
        if (isset($post['number_of_attendees'])) $this->racers = $post['number_of_attendees'];
        if (isset($post['hours'])) $this->hours = $post['hours'];
        if (isset($post['resDate'])) $this->date = $post['resDate'];
        if (isset($post['intervals'])) $this->time = $post['intervals'];
        if (isset($post['your_name'])) $this->name = $post['your_name'];
        if (isset($post['your_email'])) $this->email = $post['your_email'];
        if (isset($post['your_phone'])) $this->phone = $post['your_phone'];
        if (isset($post['address'])) $this->address = $post['address'];
        if (isset($post['card_number'])) $this->card_number = $post['card_number'];
        if (isset($post['card_cvv'])) $this->card_cvv = $post['card_cvv'];
        if (isset($post['card_holder'])) $this->card_holder = $post['card_holder'];
        if (isset($post['card_expiry_month'])) $this->card_expiry_month = $post['card_expiry_month'];
        if (isset($post['card_expiry_year'])) $this->card_expiry_year = $post['card_expiry_year'];
        $micro_date = microtime();
        $date_array = explode(" ", $micro_date);
        $this->created = date('Y-m-d\TH:i:s.') . $date_array[0];
    }

    public function per_person()
    {
        if (!$this->hours) return true;
        else return false;
    }

    public function racers_count()
    {
        if (!$this->racers) return 0;
        else return $this->racers;
    }

    public function deposit()
    {
        return ceil($this->price['checkTotal'] / 2 * 100) / 100;
    }

    public function start_time()
    {
        $dates = explode('-', $this->time);
        $start = $dates[0];
        $hours = explode(':', $start);
        if ((int)$hours[0] < 5) $date_start = date('Y-m-d',strtotime($this->date . " + 1 day")) . ' ' . $start;
        else $date_start = $this->date . ' ' . $start;
        return $date_start;
    }

    public function end_time()
    {
        $dates = explode('-', $this->time);
        $end = $dates[1];
        $hours = explode(':', $end);
        if ((int)$hours[0] < 5) $date_end = date('Y-m-d',strtotime($this->date . " + 1 day")) . ' ' . $end;
        else $date_end = $this->date . ' ' . $end;
        return $date_end;
    }

    public function get_location()
    {
        return LOCATION_CHOICES[$this->location];
    }

    public function get_package()
    {
        return PACKAGE_CHOICES[$this->package];
    }

    public function get_kart_type()
    {
        return KART_CHOICES[$this->kart_type];
    }
}
?>
