<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Library\SslCommerz\SslCommerzNotification;
use Illuminate\Support\Facades\Auth;

class RegistrationPaymentController extends Controller
{
    public function pay(Request $request)
    {
        $data = session('pending_registration');

        if (!$data) {
            return redirect()->route('register')
                ->with('error', 'Session expired. Please register again.');
        }

        $tran_id = 'REG_' . strtoupper(uniqid());

        session(['pending_registration' => array_merge($data, ['tran_id' => $tran_id])]);

        $post_data = [
            'total_amount'     => number_format(setting('register_fee', 0), 0),
            'currency'         => 'BDT',
            'tran_id'          => $tran_id,

            'success_url'      => route('registration.payment.success'),
            'fail_url'         => route('registration.payment.fail'),
            'cancel_url'       => route('registration.payment.cancel'),
            'ipn_url'          => route('registration.payment.ipn'),

            'cus_name'         => $data['admin_name'],
            'cus_email'        => $data['admin_email'],
            'cus_phone'        => $data['phone'],
            'cus_add1'         => 'Bangladesh',
            'cus_city'         => 'Dhaka',
            'cus_country'      => 'Bangladesh',

            'shipping_method'  => 'NO',
            'product_name'     => 'School Registration - ' . $data['school_name'],
            'product_category' => 'Education',
            'product_profile'  => 'general',

            'value_a'          => $tran_id,
        ];

        // meta তে সব data রাখো — session হারালেও কাজ করবে
        // school_id, invoice_no, month, year ফাঁকা থাকবে — registration এর সময় এগুলো প্রযোজ্য নয়
        Invoice::withoutGlobalScopes()->create([
            'transaction_id'  => $tran_id,
            'type'            => 'registration',
            'total_amount'    => 5000,
            'payable_amount'  => 5000,
            'status'          => 'pending',
            'meta'            => json_encode([
                'school_name' => $data['school_name'],
                'school_type' => $data['school_type'] ?? '',
                'email'       => $data['email'],
                'phone'       => $data['phone'],
                'timezone'    => $data['timezone'] ?? 'Asia/Dhaka',
                'admin_name'  => $data['admin_name'],
                'admin_email' => $data['admin_email'],
                'password'    => $data['password'],
            ]),
        ]);

        $sslc            = new SslCommerzNotification();
        $payment_options = $sslc->makePayment($post_data, 'hosted', 'json');

        if (!is_array($payment_options)) {
            $payment_options = json_decode($payment_options, true);
        }

        if (!empty($payment_options['GatewayPageURL'])) {
            return redirect($payment_options['GatewayPageURL']);
        }

        return redirect()->route('register')
            ->with('error', 'Payment gateway connection failed। আবার চেষ্টা করুন।');
    }

    public function success(Request $request)
    {
        $tran_id = $request->input('tran_id');

        // DB থেকে record খোঁজো — session এর উপর নির্ভর নয়
        $record = Invoice::withoutGlobalScopes()
            ->where('transaction_id', $tran_id)
            ->where('type', 'registration')
            ->where('status', 'pending')
            ->first();

        if (!$record) {
            return redirect()->route('register')
                ->with('error', 'Invalid বা already processed transaction।');
        }

        $sslc       = new SslCommerzNotification();
        $validation = $sslc->orderValidate(
            $request->all(),
            $tran_id,
            5000,
            'BDT'
        );

        if (!$validation) {
            return redirect()->route('register')
                ->with('error', 'Payment যাচাই করা যায়নি। Tran ID: ' . $tran_id);
        }

        $meta = json_decode($record->meta, true);

        $data = session('pending_registration');

        // duplicate check
        $existing = School::withoutGlobalScopes()
            ->where('email', $meta['email'])
            ->first();

        if ($existing) {
            session()->forget(['pending_registration', 'pending_logo']);
            return redirect()->route('login')
                ->with('success', 'School already registered। Login করুন।');
        }

        $user = null; // transaction এর বাইরে use করার জন্য আগে থেকেই declare করে রাখছি

        try {
            DB::transaction(function () use ($data, $meta, $tran_id, $request, &$user) {
                $school = School::create([
                    'name'     => $meta['school_name'],
                    'email'    => $meta['email'],
                    'phone'    => $meta['phone'],
                    'timezone' => $meta['timezone'],
                    'status'   => true,
                ]);

                if (session('pending_logo')) {
                    $school->update(['system_logo' => session('pending_logo')]);
                }

                $user = User::create([
                    'name'      => $meta['admin_name'],
                    'email'     => $meta['admin_email'],
                    'password'  => $meta['password'],
                    'role'      => 'admin',
                    'school_id' => $school->id,
                ]);

                // এখন স্কুল তৈরি হয়ে গেছে — invoice row-টাকে এই স্কুলের সাথে link করে দিচ্ছি
                Invoice::withoutGlobalScopes()
                    ->where('transaction_id', $tran_id)
                    ->update([
                        'school_id'      => $school->id,
                        'status'         => 'paid',   
                        'paid_at'        => now(),
                        'payment_method' => 'sslcommerz',
                        'val_id'         => $request->input('val_id'),
                    ]);
            });

            session()->forget(['pending_registration', 'pending_logo']);

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')->with('success', 'School setup complete!!');

        } catch (\Exception $e) {
            Log::error('Registration failed after payment', [
                'tran_id' => $tran_id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->route('register')
                ->with('error', 'Payment হয়েছে কিন্তু setup failed। Tran ID: ' . $tran_id);
        }
    }

    public function fail(Request $request)
    {
        return redirect()->route('register')
            ->with('error', 'Payment ব্যর্থ হয়েছে। আবার চেষ্টা করুন।');
    }

    public function cancel(Request $request)
    {
        return redirect()->route('register')
            ->with('error', 'Payment বাতিল করা হয়েছে।');
    }

    public function ipn(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $sslc       = new SslCommerzNotification();
        $validation = $sslc->orderValidate(
            $request->all(),
            $tran_id,
            5000,
            'BDT'
        );

        if ($validation) {
            Invoice::withoutGlobalScopes()
                ->where('transaction_id', $tran_id)
                ->update([
                    'status'         => 'paid',
                    'paid_at'        => now(),
                    'payment_method' => 'sslcommerz',
                    'val_id'         => $request->input('val_id'),
                ]);

            Log::info('Registration IPN validated', ['tran_id' => $tran_id]);
        }

        return response()->json(['status' => 'ok']);
    }
}