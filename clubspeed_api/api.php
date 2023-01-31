<?php
include_once 'conf.php';

function _request($url, $credentials=null, $params=array(), $data=null, $method='GET') {
    $params['key'] = $credentials->api_key;

    $url_params = '';
    if (!empty($params)) {
        $url_params .= '?';
        unset($tmp);
        if (!empty($params['where'])) {
            unset($tmp_where);
            $j = 0;
            foreach ($params as $key => $val) {
                if (strncmp($key,'where', strlen($key)) == 0) {
                    foreach ($val as $key_where => $val_where) {
                        if (is_array($val_where)) {
                            unset($tmpl);
                            unset($t);
                            foreach ($val_where as $k => $v) {
                                $tmpl[] = '"' . $k . '":"' . $v . '"';
                            }
                            $t = '"'.$key_where.'":{' . $tmpl[0];
                            for ($i = 1; $i < count($tmpl); $i++) $t .= ',' . $tmpl[$i];
                            $t .= '}';
                            $tmp_where[] = $t;
                        }
                        else $tmp_where[] = '"' . $key_where . '":"' . $val_where . '"';
                    }
                    $tmp[$j] = 'where={' . $tmp_where[0];
                    for ($i = 1; $i < count($tmp_where); $i++) $tmp[$j] .= ',' . $tmp_where[$i];
                    $tmp[$j] .= '}';
                    $j++;
                } else {
                    $tmp[$j] = $key . '=' . $val;
                    $j++;
                }
            }
        } else {
            foreach ($params as $key => $val) {
                $tmp[] = $key . '=' . $val;
            }
        }
        $url_params .= $tmp[0];
        for ($i = 1; $i < count($tmp); $i++) {
            $url_params .= '&' . $tmp[$i];
        }
    }

    $request_url = "https://" . $credentials->api_root . ".clubspeedtiming.com/api/index.php" . $url . $url_params;
    $ch = curl_init($request_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if (strncmp($method,'POST', strlen($method)) == 0) curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
    );

    $result = curl_exec($ch);
    $err = curl_errno($ch);
    try {
        if (!$err) {
            return json_decode($result,true);
        }
        else {
            $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
            $errmsg = curl_error($ch);
            throw new Exception($method . ' ' . $request_url . ' Error ' . $httpCode . ': ' . $errmsg);
        }
    }
    catch (Exception $e) {
        echo 'Exception: ',  $e->getMessage(), "\n";
    }
    curl_close ($ch);
}

function _day_interval($date) {
    $date->setTime(0, 0, 0);
    $start = date('Y-m-d\TH:i:s', strtotime($date));
    $date->setTime(23, 59, 59);
    $end = date('Y-m-d\TH:i:s', strtotime($date));
    return array($start, $end);
}

function _is_valid_choice($value, $choices) {
    if (is_array($value)) {
        try {
            $bool = true;
            foreach ($value as $val) {
                if (!$choices[$val]) $bool = false;
            }
            return $bool;
        }
        catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), '\n';
        }
    }
    else {
        if ($choices[$value]) return true;
    }
    return false;
}

function get_track($package) {
    if (strcasecmp($package, "package_1") == 0) return array(TRACK_1, TRACK_3);
    elseif (strcasecmp($package, "package_2") == 0) return array(TRACK_2, TRACK_3);
    elseif (strcasecmp($package, "package_3") == 0) return array(TRACK_3);
    return array(TRACK_1);
}

class Credentials
{
    public $api_root;
    public $api_key;

    function __construct($root = null, $key = null)
    {
        $this->api_root = $root;
        $this->api_key = $key;
    }
}
/* if event reservation */
class EventType
{
    /* Packages */
    static function list_event_type($credentials, $track = null, $deleted = false, $enabled = true)
    {
        $where = array(
            'trackId' => $track,
            'deleted' => $deleted,
            'enabled' => $enabled,
        );
        return _request('/eventTypes.json', $credentials, array('where' => $where));
    }

    static function get($credentials, $pk)
    {
        return _request('/eventTypes.json/' . $pk, $credentials);
    }

    static function determine($kart_type, $package, $track=0)
    {
        /* The definition of eventTypeId from the form data */
        try {
            if (EVENT_TYPES_IDS[$track][$package]) {
                return EVENT_TYPES_IDS[$track][$package];
            }
            else throw new Exception('EventType not detected');
            
        }
        catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
        }
    }
}

class EventHeatType
{
    /* EventRounds for each EventType (package) */
    static function list_event_heat_type($credentials, $event_type_id = null)
    {
        $where = array('eventTypeId' => $event_type_id);
        return _request('/eventHeatTypes.json', $credentials, array('where' => $where));
    }
     static function get($credentials, $pk)
     {
         return _request('/eventHeatTypes.json/' . $pk, $credentials);
     }
}

class EventReservationType
{
    static function list_event_res_type($credentials)
    {
        return _request('/eventReservationTypes.json', $credentials);
    }

    static function get($credentials, $pk)
    {
        return _request('/eventReservationTypes.json/' . $pk, $credentials);
    }

    static function determine($location, $track)
    {
        /* The definition of eventReservationTypeId from the form data */
        return (int)$track;
    }
}

class EventReservation
{
    static function list_event_res($credentials, $start_time = null, $end_time = null, $day = null,
                                  $event_type_id = null, $event_reservation_type_id = null, $status = null, $deleted = false)
    {
        $where = array(
            'typeId' => $event_reservation_type_id,
            'deleted' => $deleted
        );
        if ($event_type_id) $where['eventTypeId'] = $event_type_id;
        if ($day) {
            unset($interval);
            $interval = _day_interval($day);
            $start_time = $interval[0];
            $end_time = $interval[1];
        }
        if ($start_time) {
            $where['startTime'] = array('$gte' => date('Y-m-d\TH:i:s', strtotime($start_time)));
        }
        if ($end_time) {
            $where['endTime'] = array('$lte' => date('Y-m-d\TH:i:s', strtotime($end_time)));
        }
        if ($status) {
            try {
                if (!_is_valid_choice($status, EVENT_STATUS_CHOICES)){
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $where['status'] = $status;
        }
        return _request('/eventReservations.json', $credentials, array('where' => $where));
    }

    static function get($credentials, $pk)
    {
        return _request('/eventReservations.json/' . $pk, $credentials);
    }

    static function update($credentials, $pk, $status = null)
    {
        $data = array();
        if ($status) {
            try {
                if (!_is_valid_choice($status, EVENT_STATUS_CHOICES)){
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $data['status'] = $status;
        }
        return _request('/eventReservations.json/' . $pk, $credentials, $data,null,'PUT');
    }

    static function delete($credentials, $pk)
    {
        return _request('/eventReservations.json/' . $pk, $credentials, array(),null,'DELETE');
    }

    static function create($credentials, $event_type = null, $start_time = null, $end_time = null,
                           $event_reservation_type_id = null, $subject = '', $description = '',
                           $racers = null, $status = null, $rep_id = 3, $user_id = null)
    {
        if (!$user_id) $user_id = DEFAULT_USER;
        $data = array(
            'typeId' => $event_reservation_type_id,
            'isEventClosure' => false,
            'noOfRacers' => $racers,
            'noOfTotalRacers' => $racers,
            'repId' => $rep_id,
            'userId' => $user_id
        );
        if ($event_type) {
            $data['eventTypeId'] = $event_type['eventTypeId'];
        }
        if ($start_time) {
            $data['startTime'] = date('Y-m-d\TH:i:s.00', strtotime($start_time));
        }
        if ($end_time) {
            $data['endTime'] = date('Y-m-d\TH:i:s.00', strtotime($end_time));
        }
        if ($subject) $data['subject'] = $subject;
        if ($description) $data['description'] = $description;
        if ($status) {
            try {
                if (!_is_valid_choice($status, EVENT_STATUS_CHOICES)) {
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $data['status'] = $status;
        }
        return _request('/eventReservations.json', $credentials, array(), json_encode($data), 'POST');
    }
}

class Event
{
    static function list_event($credentials, $start_time = null, $end_time = null, $day = null,
                               $event_type_id = null, $reservation_id = null)
    {
        $where = array(
            'eventScheduledTime' => array(),
            'eventTypeId' => $event_type_id,
            'reservationId' => $reservation_id
        );
        if ($day) {
            unset($interval);
            $interval = _day_interval($day);
            $start_time = $interval[0];
            $end_time = $interval[1];
        }
        if ($start_time) $where['eventScheduledTime']['$gte'] = $start_time;
        if ($end_time) $where['eventScheduledTime']['$lte'] = $end_time;
        return _request('/events.json', $credentials, array('where'=> $where));
    }

    static function get($credentials, $pk) {
        return _request('/events.json/' . $pk, $credentials);
    }

    static function delete($credentials, $pk){
        return _request('/events.json/' . $pk, $credentials, array(),null,'DELETE');
    }

    static function create($credentials, $event_type, $scheduled_time = null, $duration = null,
                           $description = '', $reservation_id = null, $check_id = null,
                           $racers = null, $rounds = null)
    {
        if (!$rounds) $rounds = -1;
        $data = array(
            'eventTypeId' => $event_type['eventTypeId'],
            'eventTypeName' => $event_type['eventTypeName'],
            'eventDuration' => $duration,
            'eventTheme' => '-16776961',
            'reservationId' => $reservation_id,
            'onlineCode' => $check_id,
            'memberOnly' => $event_type['memberOnly'],
            'trackNo' => $event_type['trackId'],
            'roundNum' => $rounds,
            'totalRacers' => $racers,
            'createdHeatSpots' => $racers,
            'createdHeatTime' => date('Y-m-d\TH:i:s')
        );
        if ($scheduled_time) {
            $data['eventScheduledTime'] = date('Y-m-d\TH:i:s', strtotime($scheduled_time));
        }
        if ($description) $data['eventDesc'] = $description;
        return _request('/events.json', $credentials,  array(), json_encode($data),'POST');
    }
}
/* if event reservation */

/* if heat */
class HeatDetails
{
    static function get($credentials, $pk, $customer)
    {
        return _request('/heatDetails.json/' . $pk . '/' . $customer, $credentials);
    }
    static function update($credentials, $pk, $customer)
    {
        $data = array(
            'heatId' => $track,
            'deleted' => $deleted,
            'enabled' => $enabled,
        );
        return _request('/heatDetails.json/' . $pk . '/' . $customer, $credentials, array(), json_encode($data), 'PUT');
    }
    static function delete($credentials, $pk, $customer)
    {
        return _request('/heatDetails.json/' . $pk . '/' . $customer, $credentials, array(), null, 'DELETE');
    }
    static function create($credentials, $heat_id, $customer_id, $first_time, $proskill=1200)
    {
        $data = array(
            'heatId' => $heat_id,
            'customerId' => $customer_id,
            'lineUpPosition' => 1,
            'userId' => 2,
            'proskill' => $proskill,
            'firstTime' => $first_time,
            'timeAdded' => date('Y-m-d\TH:i:s')
        );
        return _request('/heatDetails.json', $credentials, array(), json_encode($data), 'POST');
    }
}

class HeatMain
{
    static function list_heat_main($credentials, $start_time = null, $end_time = null, $day = null, $heat_type_id = null, $track = null, $status = null, $notes=null, $type=null, $deleted = false, $enabled = true)
    {
        $where = array(
            'track' => $track,
            'scheduledTime' => array(),
            'deleted' => $deleted,
            'enabled' => $enabled
        );
        if ($heat_type_id) $where['type'] = $heat_type_id;
        if ($day) {
            unset($interval);
            $interval = _day_interval($day);
            $start_time = $interval[0];
            $end_time = $interval[1];
        }
        if ($start_time) $where['scheduledTime']['$gte'] = date('Y-m-d\TH:i:s', strtotime($start_time));
        if ($end_time) $where['scheduledTime']['$lte'] = date('Y-m-d\TH:i:s', strtotime($end_time));
        if ($notes == true) $where['notes'] = "";
        if ($type) $where['type'] = $type;
        if ($status) {
            try {
                if (!_is_valid_choice($status, HEAT_STATUS_CHOICES)) {
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $where['status'] = $status;
        }
        $order = "scheduledTime";
        return _request('/heatMain.json', $credentials, array('where' => $where, 'order' => $order));
    }
    static function get($credentials, $pk)
    {
        return _request('/heatMain.json/' . $pk, $credentials);
    }
    static function delete($credentials, $pk)
    {
        return _request('/heatMain.json/' . $pk, $credentials, array(), null, 'DELETE');
    }
    static function update($credentials, $pk, $note = '')
    {
        $data = array();
        if ($note) $data['notes'] = $note;
        return _request('/heatMain.json/' . $pk, $credentials, array(), json_encode($data), 'PUT');
    }

    static function create($credentials, $heat_type, $scheduled_time = null, $participants = 0, $notes = '', $status = null)
    {
        $data = array(
            'type' => $heat_type['heatTypesId'],
            'track' => $heat_type['trackId'],
            'lapsOrMinutes' => $heat_type['lapsOrMinutes'],
            'speedLevel' => $heat_type['speedLevel'],
            'memberOnly' => $heat_type['memberOnly'],
            'pointsNeeded' => $heat_type['cost'],
            'cadetsPerHeat' => $heat_type['cadetsPerHeat'],
            'racersPerHeat' => $participants,
            'scheduleDuration' => $heat_type['scheduleDuration']
        );
        if ($scheduled_time) {
            $data['scheduledTime'] = date('Y-m-d\TH:i:s', strtotime($scheduled_time));
        }
        if ($notes) $data['notes'] = $notes;
        if ($status) {
            try {
                if (!_is_valid_choice($status, HEAT_STATUS_CHOICES)){
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $data['status'] = $status;
        }
        return _request('/heatMain.json', $credentials, array(), json_encode($data), 'POST');
    }
}

class HeatType
{

    static function list_heat_type($credentials, $track = null, $deleted = false, $enabled = true)
    {
        $where = array(
            'trackId' => $track,
            'deleted' => $deleted,
            'enabled' => $enabled,
        );
        return _request('/heatTypes.json', $credentials, array('where' => $where));
    }

    static function get($credentials, $pk)
    {
        return _request('/heatTypes.json/' . $pk, $credentials);
    }

    static function determine($package)
    {
        /* The definition of heatTypeId from the form data */
        try {
            if (HEAT_TYPES_IDS[$package]) {
                return HEAT_TYPES_IDS[$package];
            }
            else throw new Exception('HeatType not detected');
        }
        catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
        }
    }
}
/* if heat */

class Customers
{
    static function list_customers($credentials, $firstname = '', $lastname = '', $email = '', $deleted = false)
    {
        $where = array('deleted' => $deleted);
        if ($firstname) $where['firstname'] = $firstname;
        if ($lastname) $where['lastname'] = $lastname;
        if ($email) $where['email'] = $email;
        return _request('/customers.json', $credentials, array('where' => $where));
    }

    static function get($credentials, $pk)
    {
        return _request('/customers.json/' . $pk, $credentials);
    }

    static function create($credentials, $firstname, $lastname, $phone = '', $email = '', $notes = '')
    {
        $data = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'Country' => 'Australia'
        );
        $data['racername'] = $firstname.' '.$lastname;
        if ($phone) $data['phoneNumber'] = $phone;
        if ($email) $data['email'] = $email;
        if ($notes) $data['generalNotes'] = $email;
        return _request('/customers.json', $credentials, array(), json_encode($data), 'POST');
    }
}

class Checks
{
    static function list_checks($credentials, $check_type_id = null, $status = null)
    {
        $where = array();
        if ($check_type_id) {
            try {
                if (!_is_valid_choice($check_type_id, CHECK_TYPE_CHOICES)) {
                    throw new Exception('invalid check_type');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $where['checkType'] = $check_type_id;
        }
        if ($status) {
            try {
                if (!_is_valid_choice($status, CHECK_STATUS_CHOICES)) {
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $where['checkStatus'] = $status;
        }
        return _request('/checkTotals.json', $credentials, array('where' => $where));
    }
    static function get($credentials, $pk)
    {
        return _request('/checkTotals.json/' . $pk, $credentials);
    }
    static function void($credentials, $pk)
    {
        return _request('/checks.json/'.$pk.'/void', $credentials, array(), null, 'POST');
    }
    static function virtual($credentials, $customer_id = null, $details = null, $user_id = null)
    {
        try {
            if (!is_array($details)) {
                throw new Exception('details must be an array');
            }
        }
        catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
        }
        foreach ($details as $value) {
            try {
                if (!is_array($value)) {
                    throw new Exception('invalid check detail');
                }
                if (!$value['productId']) {
                    throw new Exception('productId not found');
                }
                if (!$value['qty']) {
                    throw new Exception('qty not found');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
        }
        if (!$user_id) $user_id = DEFAULT_USER;
        if ($customer_id) $check['customerId'] = $customer_id;
        if (!empty($details)) $check['details'] = $details;
        if ($user_id) $check['userId'] = $user_id;
        $data = array('checks' => array($check));
        return _request('/checkTotals.json/virtual', $credentials, array('select' => 'customerId,checkTotal'), json_encode($data), 'POST');
    }
    static function create($credentials, $customer_id = null, $details = null, $user_id = null)
    {
        try {
            if (!is_array($details)) {
                throw new Exception('details must be an array');
            }
        }
        catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
        }
        foreach ($details as $value) {
            try {
                if (!is_array($value)) {
                    throw new Exception('invalid check detail');
                }
                if (!$value['productId']) {
                    throw new Exception('productId not found');
                }
                if (!$value['qty']) {
                    throw new Exception('qty not found');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
        }
        if (!$user_id) $user_id = DEFAULT_USER;
        $check = array(
            'customerId' => $customer_id,
            'details' => $details,
            'userId' => $user_id
        );
        $data = array('checks' => array($check));
        return _request('/checkTotals.json', $credentials, array(), json_encode($data), 'POST');
    }
    static function update($credentials, $pk, $name = '', $notes = '')
    {
        $data = array();
        if ($name) $data['name'] = $name;
        if ($notes) $data['notes'] = $notes;
        return _request('/checks.json/' . $pk, $credentials, array(), json_encode($data), 'PUT');
    }
}

class CheckDetails
{
    static function list_check_details($credentials, $check_id)
    {
        $where = array('checkId' => $check_id);
        return _request('/checkDetails.json', $credentials, array('where' => $where));
    }
    static function get($credentials, $pk)
    {
        return _request('/checkDetails.json/' . $pk, $credentials);
    }
     static function update($credentials, $pk, $status = null)
     {
        $data = array();
        if ($status) {
            try {
                if (!_is_valid_choice($status, CHECK_DETAIL_STATUS_CHOICES)) {
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $data['status'] = $status;
        }
        return _request('/checkDetails.json/' . $pk, $credentials, array(), json_encode($data), null, 'PUT');
     }
}

class Payments
{
    static function list_payments($credentials, $check_id = null, $customer_id = null, $pay_type = null, $status = null)
    {
        $where = array();
        if ($check_id ) $where['checkId'] = $check_id;
        if ($customer_id) $where['customerId'] = $customer_id;
        if ($pay_type) {
            try {
                if (!_is_valid_choice($pay_type, PAYMENT_PAYTYPE_CHOICES)) {
                    throw new Exception('invalid pay_type');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $where['payType'] = $pay_type;
        }
        if ($status) {
            try {
                if (!_is_valid_choice($status, PAYMENT_STATUS_CHOICES)) {
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $where['payStatus'] = $status;
        }
        return _request('/payments.json', $credentials, array('where' => $where));
    }
    static function get($credentials, $pk)
    {
        return _request('/payments.json/' . $pk, $credentials);
    }
    static function delete($credentials, $pk){
        return _request('/payments.json/' . $pk, $credentials, array(), null, 'DELETE');
    }
    static function create($credentials, $amount, $check_id, $customer_id = null, $pay_type = null, $status = null, $transaction = null, $user_id = null)
    {
        if (!$user_id) $user_id = DEFAULT_USER;
        $data = array(
            'payAmount' => $amount,
            'checkId' => $check_id,
            'customerId' => $customer_id,
            'extCardType' => 'Stripe_api',
            'userId' => $user_id
    );
        if ($transaction) {
            $data['troutd'] = $transaction;
            $data['transaction'] = $transaction;
        }
        if ($pay_type) {
            try {
                if (! _is_valid_choice($pay_type, PAYMENT_PAYTYPE_CHOICES)) {
                    throw new Exception('invalid pay_type');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $data['payType'] = $pay_type;
        }
        if ($status) {
            try {
                if (!_is_valid_choice($status, PAYMENT_STATUS_CHOICES)) {
                    throw new Exception('invalid status');
                }
            }
            catch (Exception $e) {
                echo 'Exception: ',  $e->getMessage(), "\n";
            }
            $data['payStatus'] = $status;
        }
        return _request('/payments.json', $credentials, array(), json_encode($data), 'POST');
    }
}

class ProcessPayment
{
    static function complete($credentials, $check_id, $number, $expiryMonth, $expiryYear, $cvv, $details = null, $firstName = null, $lastName = null, $phone = null, $email = null)
    {
        $options = array(//Обратите внимание, что эти свойства будут различаться в зависимости от выбранного обработчика платежей.
            'vendor' => 'my_sagepay_vendor_name',
            'simulatorMode' => true//режим отладки
        );
        $check = array(
            'checkId' => $check_id,
            'sendCustomerReceiptEmail' => false,
            'details' => $details
        );
        $card = array(// Не в Simple Pay
            'firstName' => $firstName,
            'lastName' => $lastName,
            'number' => $number,
            'expiryMonth' => $expiryMonth,
            'expiryYear' => $expiryYear,
            'cvv' => $cvv,
            'phone' => $phone,
            "email" => $email
        );
        $data = array(
            'name' => 'Omnipay',
            'options' => $options,
            'check' => $check,
            'card' => $card
        );
        return _request('/processPayment.json', $credentials, array(), json_encode($data), 'POST');
    }
}

?>
