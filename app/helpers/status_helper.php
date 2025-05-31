<?php
// app/helpers/status_helper.php

if (!function_exists('translate_order_status')) {
    function translate_order_status($status_en) {
        $translations = [
            'pending_confirmation' => 'در انتظار تایید',
            'processing' => 'در حال پردازش',
            'shipped' => 'ارسال شده',
            'delivered' => 'تحویل داده شده',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            'refunded' => 'مرجوع شده',
            'failed' => 'ناموفق'
            // Add other statuses as needed
        ];
        return $translations[strtolower($status_en)] ?? ucfirst($status_en);
    }
}

if (!function_exists('get_order_status_class')) {
    function get_order_status_class($status_en) {
        switch (strtolower($status_en)) {
            case 'pending_confirmation': return 'bg-warning text-dark';
            case 'processing': return 'bg-info text-dark';
            case 'shipped': return 'bg-primary';
            case 'delivered':
            case 'completed': return 'bg-success text-white';
            case 'cancelled':
            case 'refunded':
            case 'failed': return 'bg-danger text-white';
            default: return 'bg-secondary text-white';
        }
    }
}

if (!function_exists('translate_payment_status')) {
    function translate_payment_status($status_en) {
        $translations = [
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'failed' => 'ناموفق',
            'refunded' => 'بازپرداخت شده',
            'pending_on_delivery' => 'پرداخت هنگام تحویل',
            'partially_paid' => 'بخشی پرداخت شده'
            // Add other statuses as needed
        ];
        return $translations[strtolower($status_en)] ?? ucfirst($status_en);
    }
}

if (!function_exists('get_payment_status_class')) {
    function get_payment_status_class($status_en) {
        switch (strtolower($status_en)) {
            case 'pending':
            case 'pending_on_delivery': return 'bg-warning text-dark';
            case 'paid': return 'bg-success text-white';
            case 'failed': return 'bg-danger text-white';
            case 'refunded': 
            case 'partially_paid': return 'bg-info text-dark';
            default: return 'bg-secondary text-white';
        }
    }
}

if (!function_exists('translate_commission_status')) {
    function translate_commission_status($status_en) {
        $translations = [
            'pending' => 'در انتظار تایید',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            'paid' => 'پرداخت شده',
            'cancelled' => 'لغو شده',
            'payout_requested' => 'درخواست تسویه'
        ];
        return $translations[strtolower($status_en)] ?? ucfirst($status_en);
    }
}

if (!function_exists('translate_payout_status')) {
    function translate_payout_status($status_en) {
        $translations = [
            'requested' => 'درخواست شده',
            'processing' => 'در حال پردازش',
            'completed' => 'تکمیل شده', // (پرداخت شده)
            'rejected' => 'رد شده',
            'cancelled' => 'لغو شده',
        ];
        return $translations[strtolower($status_en)] ?? ucfirst($status_en);
    }
}

// Add more helper functions as needed, for example, for styling payout statuses
if (!function_exists('get_payout_status_class')) {
    function get_payout_status_class($status_en) {
        switch (strtolower($status_en)) {
            case 'requested': return 'bg-warning text-dark';
            case 'processing': return 'bg-info text-dark';
            case 'completed': return 'bg-success text-white';
            case 'rejected':
            case 'cancelled': return 'bg-danger text-white';
            default: return 'bg-secondary text-white';
        }
    }
}

?>
