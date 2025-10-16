<?php

namespace App\Http\Controllers\Admin\InvoiceManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\Bookings;
use App\Models\Listings;

use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function getBookingIds(Request $request)
    {

        if (empty($request->daterange)) {
            return response()->json([]);
        }

        $dateRange = $request->input('daterange');
        [$startDate, $endDate] = explode(' - ', $dateRange);

        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');

        $listing = Listings::where('listing_id', $request->listing_id)->first();

        $bookingIds = Bookings::where('listing_id', $listing->id)
            ->where('booking_date_start', '>=', $startDate)
            ->where('booking_date_start', '<=', $endDate)
            ->where('booking_status', 'confirmed')
            ->pluck('id');

        return response()->json($bookingIds);
    }

    public function downloadInvoice($id)
    {
        $booking = Bookings::findOrFail($id);
        $listing = Listings::where('id', $booking->listing_id)->first();

        $listing = Listings::where('id', $booking->listing_id)->first();
        $listing_json = json_decode($listing->listing_json);
        $listing_name = !empty($listing_json->title) ? $listing_json->title : '';

        // $pdf = Pdf::loadView('admin.invoice.download', [
        //     'booking' => $booking,
        //     'listing_name' => $listing_name
        // ]);

        // return response($pdf->output(), 200, [
        //     'Content-Type' => 'application/pdf',
        //     'Content-Disposition' => 'inline; filename="invoice_' . $booking->id . '.pdf"',
        // ]);

        // return $pdf->stream("invoice_{$booking->id}.pdf");

        return view('Admin.invoice.download', ['booking' => $booking, 'listing_name' => $listing_name]);

        $pdf = Pdf::loadView('Admin.invoice.download', ['booking' => $booking, 'listing_name' => $listing_name]);

        return $pdf->download("invoice_{$booking->id}.pdf");
    }
}
