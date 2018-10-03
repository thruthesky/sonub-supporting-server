<?php

function di($o) {
    echo "<xmp>";
    print_r($o);
    echo "</xmp>";
}

function _re($k = null, $_default = null ) {
    if ( $k ) {
        if ( isset($_REQUEST[$k]) ) return $_REQUEST[$k];
        else return $_default;
    }
    return $_REQUEST;
}

function error( $code, $message ) {
    echo json_encode(['code' => $code, 'message' => $message]);
    exit;
}

function success($data) {
    $res = [
        'query' => _re(),
        'data' => $data
    ];
    echo json_encode($res);
    exit;
}



function add0( $n ) {
    if ( $n < 10 ) return "0$n";
    else return $n;
}

/**
 * Returns a string of YmdHis
 *
 * If numbers are exceeding the end of the calendar time, it will compute with next year, month, day, hour, minutes, seconds.
 *
 *
 *
 * @param $Y
 * @param int $m
 * @param int $d
 * @param int $H
 * @param int $i
 * @param int $s
 * @return string
 *
 * @example YmdHis( 2018, 100, 100, 100, 100, 100 );
 */
function YmdHis( $Y, $m=1, $d=1, $H=0, $i=0, $s=0 ) {
//    $YmdHis = $Y . add0($m) . add0($d) . add0( $H ) . add0($i) . add0($s);
    return date("YmdHis", mktime( $H, $i, $s, $m, $d, $Y) );
}

/**
 * Returns a string of Ymd
 *
 * @param $Y
 * @param int $m
 * @param int $d
 * @return string
 */
function Ymd( $Y, $m=1, $d=1 ) {
    return date("Ymd", mktime( 0, 0, 0, $m, $d, $Y) );
}


/**
 * Returns timestamp of YmdHis
 * @param $YmdHis
 * @return false|int
 *
 * @example
 *  $until_date = date("Ymd", stamp_of_YmdHis( YmdHis( YmdHis( $to_year, $to_month, $to_day, 23, 59, 59 ) ) ));
 */
function stamp_of_YmdHis( $YmdHis ) {

    $Y = substr( "$YmdHis", 0, 4 );
    $m = substr( "$YmdHis", 4, 2 );
    $d = substr( "$YmdHis", 6, 2 );
    $H = substr( "$YmdHis", 8, 2 );
    $i = substr( "$YmdHis", 10, 2 );
    $s = substr( "$YmdHis", 12, 2 );

    return mktime( $H, $i, $s, $m, $d, $Y);
}


function getFromDate() {

    $from_year = _re('from_year');
    $from_month = _re('from_month');
    $from_day = _re('from_day');

    return Ymd( $from_year, $from_month, $from_day );
}

function getUntilDate() {

    $to_year = _re('to_year');
    $to_month = _re('to_month');
    $to_day = _re('to_day');

    return  Ymd( $to_year, $to_month, $to_day );
}

/**
 *
 * Returns the next date of input date.
 *
 * @desc Get '20180505' and returns '20180506'
 *
 * @param $date
 * @return false|string
 */
function getNextDate( $date ) {
    $stamp = stamp_of_YmdHis("{$date}000000");
    return date('Ymd', $stamp + 60 * 60 * 24 );
}

/**
 *
 */
function pageView() {
    $date = getFromDate();
    $data = [];
    do {
        $conds = [];
        if ( $domain = _re('domain') ) $conds[] = "domain='$domain'";
        $conds[] = "YmdHis>={$date}000000";
        $conds[] = "YmdHis<={$date}235959";
        $where = implode(' AND ', $conds);
        $q = "SELECT COUNT(*) FROM logs WHERE $where";
        $data[$date] = db()->result($q);

        $date = getNextDate( $date );
    } while ( $date <= getUntilDate() );

    success( $data );
}
/**
 *
 */
function uniqueVisitor() {
    $date = getFromDate();
    $data = [];
    do {
        $conds = [];
        if ( $domain = _re('domain') ) $conds[] = "domain='$domain'";
        $conds[] = "YmdHis>={$date}000000";
        $conds[] = "YmdHis<={$date}235959";
        $where = implode(' AND ', $conds);
        $sub_q = "SELECT COUNT(*) FROM logs WHERE $where GROUP BY ip";
        $q = "SELECT COUNT(*) FROM ($sub_q)";
//        di($q);
        $data[$date] = db()->result($q);
        $date = getNextDate( $date );
    } while ( $date <= getUntilDate() );

//    di($data);
    success( $data );
}