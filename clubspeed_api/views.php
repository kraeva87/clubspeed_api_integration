<?php
include_once 'api.php';
include_once 'conf.php';
include_once 'models.php';

function get_customer($instance, $credentials){
    $tmp = explode(' ', $instance->name);
    $list_customers = Customers::list_customers($credentials, $tmp[0], $tmp[1], $instance->email);
    if ($list_customers) {
        return $list_customers[0]['customerId'];
    }
    else {
        $notes = 'Created from API at ' . date('Y-m-d') . 'T' . date('H:i:s');
        $list_customers = Customers::create($credentials, $tmp[0], $tmp[1], $instance->phone, $instance->email);
        return $list_customers[0]['customerId'];
    }
}

function make_check($instance, $credentials, $customer_id) {
    $details = array();
    $product_id = 0;
    foreach ($instance->package as $pack) {
        $product_id = BOOKING_PRODUCT_IDS[$pack];
        $qty = $instance->participants[$pack];
        $details[] = array(
            'productId' => $product_id,
            'qty' => $qty
        );
    }

    $check = Checks::create($credentials, $customer_id, $details);
    $check_id = $check['checkId'];
    if ($check_id) {
        Checks::update($credentials, $check_id, $instance->name, 'Online Reservation');
        $details = CheckDetails::list_check_details($credentials, $check_id);
        if (!$details['checkDetails']) $details = array();
        foreach ($details as $detail) {
            CheckDetails::update($credentials, $detail['checkDetailId'], CHECK_DETAIL_STATUS_PERMANENT);
        }
    }
    return $check_id;
}

class BookEventView
{
    static function done($post) {
        $instance = new BookRequest($post);

        // ClubSpeed API
        $credentials = new Credentials($instance->api_root, $instance->api_key);
        $track_num = get_track($instance);

        $customer_id = get_customer($instance, $credentials);
        $check_id = make_check($instance, $credentials, $customer_id);

        // get the total cost
        $check = Checks::get($credentials, $check_id);
        $instance->price = $check['checks'][0];
        $instance->customer_id = $customer_id;
        $instance->check_id = $check_id;
        unset($transaction_id);

        if ($post) {
            //Make a payment
            //$transaction_id = return id transaction;
        }
        if (!$transaction_id) {
            // Payment failed
            Checks::void($credentials, $check_id);
            return false;// return to the page
        }
        $instance->trans_id = $transaction_id;

        $payment_id = Payments::create($credentials, $instance->price, $check_id, $customer_id, PAYMENT_PAYTYPE_CREDIT, PAYMENT_STATUS_PAID, $transaction_id);
        $instance->payment_id = $payment_id;

        /* if event reservation */
        $event_type_id = EventType::determine($instance->package, $track_num);
        $event_type = EventType::get($credentials, $event_type_id);
        $event_heat_types = EventHeatType::list_event_heat_type($credentials, $event_type_id);
        $event_reservation_type_id = EventReservationType::determine($track_num);
        
        foreach ($instance->package as $pack) {
            $track_num = get_track($pack);
            for ($i = 0; $i < count($track_num); $i++) {
                $reservation_id[$i] = EventReservation::create($credentials, $event_type[$i], $instance->start_time(), $instance->end_time(),
                $event_reservation_type_id[$i], $name_reserv . ' Online reservation', 'gst. ' . $instance->participants, $instance->participants, EVENT_STATUS_UNPAID);
                if ($event_heat_types[$i]) $rounds = count($event_heat_types[$i]);
                else $rounds = null;
                $duration = abs(strtotime($instance->end_time()) - strtotime($instance->start_time())) / 60;
                $event_id[$i] = Event::create($credentials, $event_type[$i], $instance->start_time(), (int)$duration,
                $instance->name . ' Online reservation', $reservation_id[$i], $check_id, $instance->participants, $rounds);
                $instance->reservation_id[$i] = $reservation_id[$i];
                $instance->event_id[$i] = $event_id[$i];
            }
        }
        /* if event reservation */
        
        /* if heat */
        $note_check = '';
        foreach ($instance->package as $pack) {
            $track_num = get_track($pack);
            for ($i = 0; $i < count($track_num); $i++) {
                $note_heat = '';
                $heat[$i] = HeatMain::get($credentials, $instance->heat_id[$pack][$track_num[$i]]);
                $note_check .= 'Heat #' . $instance->heat_id[$pack][$track_num[$i]] . ' scheduled at ' . date('d-m-y H:i:A', $heat[$i]['scheduledTime']);//02-11-19 10:18:AM
                $note_heat = $instance->name . ' ReservationID #' . $instance->check_id . ' Qty: ' . $instance->participants;
                HeatMain::update($credentials, $instance->heat_id[$pack][$track_num[$i]], $note_heat);
                HeatDetails::create($credentials, $instance->heat_id[$pack][$track_num[$i]], $instance->customer_id, $customer['proskill']);
            }
        }
        update_check($instance, $credentials, $instance->check_id, $note_check);
        /* if heat */

        return true;
    }
}
