<?php

const API_ROOTS = 'your-root',
    API_KEYS = 'secret-api-key';

const PACKAGE_1 = 'p1', PACKAGE_2 = 'p2', PACKAGE_3 = 'p3', PACKAGE_4 = 'p4';
define('PACKAGE_CHOICES', array(
    PACKAGE_1 => 'PACKAGE 1',
    PACKAGE_2 => 'PACKAGE 2',
    PACKAGE_3 => 'PACKAGE 3',
    PACKAGE_4 => 'PACKAGE 4'
));

const DEFAULT_USER = 2;

# =======================
#  Hardcode from API
# =======================

const TRACK_1 = 1, TRACK_2 = 2, TRACK_3 = 3;
define('TRACK_CHOICES', array(
    TRACK_1 => 'Track 1',
    TRACK_2 => 'Track 2',
    TRACK_3 => 'Track 3'
));

define('BOOKING_PRODUCT_IDS', array(
    PACKAGE_1 => 100,
    PACKAGE_2 => 101,
    PACKAGE_3 => 102,
    PACKAGE_4 => 103
));

//if event reservation
define('EVENT_TYPES_IDS', array(
    TRACK_1 => array(
        PACKAGE_1 => 10,
        PACKAGE_4 => 11
    ),
    TRACK_2 => array(
        PACKAGE_2 => 20
    ),
    TRACK_3 => array(
        PACKAGE_1 => 30,
        PACKAGE_2 => 31,
        PACKAGE_3 => 32
    )
));

//if heat
define('HEAT_TYPES_IDS', array(
    PACKAGE_1 => array(TRACK_1 => 1, TRACK_3 => 3),
    PACKAGE_2 => array(TRACK_2 => 2, TRACK_3 => 3),
    PACKAGE_3 => array(TRACK_3 => 3),
    PACKAGE_4 => array(TRACK_1 => 1)
));

const EVENT_STATUS_UNPAID = 1, EVENT_STATUS_DEPOSIT = 2, EVENT_STATUS_PAIDINFULL = 3, EVENT_STATUS_IMPORTANT = 4, EVENT_STATUS_HOLIDAY = 5, EVENT_STATUS_TENTATIVE = 7;
define('EVENT_STATUS_CHOICES', array(
    EVENT_STATUS_UNPAID => 'Unpaid',
    EVENT_STATUS_DEPOSIT => 'Deposit',
    EVENT_STATUS_PAIDINFULL => 'Paid in Full',
    EVENT_STATUS_IMPORTANT => 'Important Note',
    EVENT_STATUS_HOLIDAY => 'Holiday',
    EVENT_STATUS_TENTATIVE => 'Tentative'
));

const PRODUCT_TYPE_REGULAR = 1, PRODUCT_TYPE_POINT = 2, PRODUCT_TYPE_FOOD = 3, PRODUCT_TYPE_RESERVATION = 4,
PRODUCT_TYPE_GAMECARD = 5, PRODUCT_TYPE_MEMBERSHIP = 6, PRODUCT_TYPE_GIFTCARD = 7, PRODUCT_TYPE_ENTITLE = 8;
define('PRODUCT_TYPE_CHOICES', array(
    PRODUCT_TYPE_REGULAR => 'Regular',
    PRODUCT_TYPE_POINT => 'Point',
    PRODUCT_TYPE_FOOD => 'Food',
    PRODUCT_TYPE_RESERVATION => 'Reservation',
    PRODUCT_TYPE_GAMECARD => 'GameCard',
    PRODUCT_TYPE_MEMBERSHIP => 'Membership',
    PRODUCT_TYPE_GIFTCARD => 'Gift Card',
    PRODUCT_TYPE_ENTITLE => 'Entitle'
));

const HEAT_STATUS_OPEN = 0, HEAT_STATUS_RACING = 1, HEAT_STATUS_FINISHED = 2, HEAT_STATUS_ABORTED = 3, HEAT_STATUS_CLOSED = 4;
define('HEAT_STATUS_CHOICES', array(
    HEAT_STATUS_OPEN => 'Open',
    HEAT_STATUS_RACING => 'Racing',
    HEAT_STATUS_FINISHED => 'Finished',
    HEAT_STATUS_ABORTED => 'Aborted',
    HEAT_STATUS_CLOSED => 'Closed'
));

const CHECK_TYPE_REGULAR = 1, CHECK_TYPE_EVENT = 2;
define('CHECK_TYPE_CHOICES', array(
    CHECK_TYPE_REGULAR => 'Regular',
    CHECK_TYPE_EVENT => 'Event'
));

const CHECK_STATUS_OPEN = 0, CHECK_STATUS_CLOSED = 1;
define('CHECK_STATUS_CHOICES', array(
    CHECK_STATUS_OPEN => 'Open',
    CHECK_STATUS_CLOSED => 'Closed'
));

const CHECK_DETAIL_STATUS_NEW = 1, CHECK_DETAIL_STATUS_VOIDED = 2, CHECK_DETAIL_STATUS_PERMANENT = 3;
define('CHECK_DETAIL_STATUS_CHOICES', array(
    CHECK_DETAIL_STATUS_NEW => 'New',
    CHECK_DETAIL_STATUS_VOIDED => 'Voided',
    CHECK_DETAIL_STATUS_PERMANENT => 'Permanent'
));

const PAYMENT_PAYTYPE_CASH = 1, PAYMENT_PAYTYPE_CREDIT = 2, PAYMENT_PAYTYPE_EXTERNAL = 3, PAYMENT_PAYTYPE_GIFTCARD = 4,
PAYMENT_PAYTYPE_VOUCHER = 5, PAYMENT_PAYTYPE_COMPLEMENTARY = 6;
define('PAYMENT_PAYTYPE_CHOICES', array(
    PAYMENT_PAYTYPE_CASH => 'Cash',
    PAYMENT_PAYTYPE_CREDIT => 'Credit',
    PAYMENT_PAYTYPE_EXTERNAL => 'External / Third Party Processor',
    PAYMENT_PAYTYPE_GIFTCARD => 'Gift Card',
    PAYMENT_PAYTYPE_VOUCHER => 'Voucher',
    PAYMENT_PAYTYPE_COMPLEMENTARY => 'Complementary'
));

const PAYMENT_STATUS_PAID = 1, PAYMENT_STATUS_VOID = 2;
define('PAYMENT_STATUS_CHOICES', array(
    PAYMENT_STATUS_PAID => 'Paid',
    PAYMENT_STATUS_VOID => 'Void'
));
