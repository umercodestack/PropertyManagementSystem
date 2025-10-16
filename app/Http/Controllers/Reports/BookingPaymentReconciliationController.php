<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\BookingReconciliation;
use Illuminate\Http\Request;

class BookingPaymentReconciliationController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $type = $request->input('type');
        $daterange = $request->input('daterange');
        $paymentStatus = $request->input('payment_status');
        $bookingSource = $request->input('booking_source');

        if ($paymentStatus === 'payment_unverified') {
            $paymentStatus = null;
        }

        // Date range
        $startDate = null;
        $endDate = null;
        if ($daterange) {
            $dates = explode(' - ', $daterange);
            if (count($dates) == 2) {
                $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dates[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dates[1]))->endOfDay();
            }
        }

        // Build query on the view
        $query = BookingReconciliation::query();

        if ($type && $startDate && $endDate) {
            switch ($type) {
                case 'created_at':
                    $query->whereBetween('created_on', [$startDate, $endDate]);
                    break;
                case 'checkin':
                    $query->whereBetween('start_date', [$startDate, $endDate]);
                    break;
                case 'checkout':
                    $query->whereBetween('end_date', [$startDate, $endDate]);
                    break;
            }
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($bookingSource) {
            $query->where('source', $bookingSource);
        }

        $bookings = $query->orderByDesc('created_on')->get()->map(function ($item, $index) {
            $item->s_no = $index + 1;

            // Listing title
            $listing = \App\Models\Listing::find($item->apartment);
            $listing_json = $listing && $listing->listing_json ? json_decode($listing->listing_json) : null;
            $item->listing_title = $listing_json->title ?? '';

            // Route
            $isOta = str_contains($item->booking_id, 'O');
            $item->route = $isOta
                ? route('booking.editOtaBooking', $item->id)
                : route('booking-management.edit', $item->id);

            return $item;
        });

        // Dropdown filters
        $allPaymentStatuses = BookingReconciliation::distinct()
            ->pluck('payment_status')->unique()->filter()->sort()->values();

        $allBookingSources = BookingReconciliation::distinct()
            ->pluck('source')->unique()->filter()->sort()->values();

        // Summaries
        $paymentStatusCounts = $bookings->groupBy('payment_status')->map->count();
        $bookingSourceCounts = $bookings->groupBy('source')->map->count();

        $totals = [
            'total_amount' => $bookings->sum('amount'),
            'total_amount_to_be_received' => $bookings->sum('amount_to_be_received'),
            'total_amount_received' => $bookings->sum('amount_received'),
            'total_discount' => $bookings->sum('discount'),
            'total_ota_commission' => $bookings->sum('ota_commission'),
            'total_cleaning_fee' => $bookings->sum('cleaning_fee'),
            'total_forex_adjustment' => $bookings->sum('forex_adjustement'),
        ];

        return view('Admin.reports.finance.booking_payment_reconciliation', [
            'bookings' => $bookings,
            'paymentStatusCounts' => $paymentStatusCounts,
            'bookingSourceCounts' => $bookingSourceCounts,
            'allPaymentStatuses' => $allPaymentStatuses,
            'allBookingSources' => $allBookingSources,
            'totals' => $totals,
            'filters' => [
                'type' => $type,
                'daterange' => $daterange,
                'payment_status' => $paymentStatus,
                'booking_source' => $bookingSource
            ]
        ]);
    }
}
