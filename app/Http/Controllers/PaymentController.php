<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;

class PaymentController extends Controller
{
    public function pay(Invoice $invoice)
    {
        if ($invoice->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized');
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('billing.show')
                ->with('error', 'এই Invoice ইতিমধ্যে পরিশোধ করা হয়েছে।');
        }

        $school  = $invoice->school;
        $tran_id = uniqid('INV_');

        $post_data = [
            'total_amount'  => $invoice->payable_amount,
            'currency'      => 'BDT',
            'tran_id'       => $tran_id,

            'success_url'   => route('billing.payment.success'),
            'fail_url'      => route('billing.payment.fail'),
            'cancel_url'    => route('billing.payment.cancel'),
            'ipn_url'       => route('billing.payment.ipn'),

            'cus_name'      => $school->name,
            'cus_email'     => auth()->user()->email ?? 'noemail@example.com',
            'cus_add1'      => $school->address ?? 'Dhaka',
            'cus_city'      => 'Dhaka',
            'cus_country'   => 'Bangladesh',
            'cus_phone'     => auth()->user()->phone ?? '01700000000',

            'shipping_method'  => 'NO',
            'product_name'     => 'School Monthly Invoice - ' . $invoice->invoice_no,
            'product_category' => 'Service',
            'product_profile'  => 'general',

            'value_a' => $invoice->id,
            'value_b' => $invoice->school_id,
        ];

        $sslc = new SslCommerzNotification();
        $payment_options = $sslc->makePayment($post_data, 'hosted', 'json');

        if (! is_array($payment_options)) {
            $payment_options = json_decode($payment_options, true);
        }

        if (isset($payment_options['GatewayPageURL']) && $payment_options['GatewayPageURL'] != '') {
            return redirect($payment_options['GatewayPageURL']);
        }

        return redirect()->route('billing.show')
            ->with('error', 'Payment gateway connection failed. আবার চেষ্টা করুন।');
    }

    public function success(Request $request)
    {
        $tran_id   = $request->input('tran_id');
        $invoiceId = $request->input('value_a');

        $sslc = new SslCommerzNotification();
        $validation = $sslc->orderValidate($request->all(), $tran_id, $request->input('amount'), $request->input('currency'));

        if ($validation) {
            $invoice = Invoice::findOrFail($invoiceId);

            $invoice->update([
                'status'         => 'paid',
                'paid_at'        => now(),
                'payment_method' => 'sslcommerz',
                'transaction_id' => $tran_id,
                'val_id'         => $request->input('val_id'),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($invoice)
                ->withProperties(['icon' => 'payments', 'type' => 'invoice'])
                ->log('Invoice paid via SSLCommerz: ' . $invoice->invoice_no);

            return redirect()->route('billing.payment.result')
                ->with('success', 'পেমেন্ট সফল! Invoice #' . $invoice->invoice_no . ' পরিশোধ হয়েছে।');
        }

        return redirect()->route('billing.payment.result')
            ->with('error', 'পেমেন্ট যাচাই করা যায়নি। আবার চেষ্টা করুন।');
    }

    public function fail(Request $request)
    {
        return redirect()->route('billing.payment.result')
            ->with('error', 'পেমেন্ট ব্যর্থ হয়েছে। আবার চেষ্টা করুন।');
    }

    public function cancel(Request $request)
    {
        return redirect()->route('billing.payment.result')
            ->with('error', 'পেমেন্ট বাতিল করা হয়েছে।');
    }

    public function ipn(Request $request)
    {
        $tran_id   = $request->input('tran_id');
        $invoiceId = $request->input('value_a');

        $sslc = new SslCommerzNotification();
        $validation = $sslc->orderValidate($request->all(), $tran_id, $request->input('amount'), $request->input('currency'));

        if ($validation) {
            $invoice = Invoice::find($invoiceId);

            if ($invoice && $invoice->status !== 'paid') {
                $invoice->update([
                    'status'         => 'paid',
                    'paid_at'        => now(),
                    'payment_method' => 'sslcommerz',
                    'transaction_id' => $tran_id,
                    'val_id'         => $request->input('val_id'),
                ]);

                activity()
                    ->performedOn($invoice)
                    ->withProperties(['icon' => 'payments', 'type' => 'invoice'])
                    ->log('Invoice paid via SSLCommerz (IPN): ' . $invoice->invoice_no);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}