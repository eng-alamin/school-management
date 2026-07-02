<?php

namespace App\Http\Controllers;

use App\Models\Institution;
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

        $fee = (float) setting('register_fee', 5000);

        $post_data = [
            'total_amount'     => number_format($fee, 0, '.', ''),
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
            'product_name'     => 'Institution Registration - ' . $data['institution_name'],
            'product_category' => 'Education',
            'product_profile'  => 'general',

            'value_a'          => $tran_id,
        ];

        // meta তে সব data রাখো — session হারালেও কাজ করবে
        // institution_id, month, year ফাঁকা থাকবে — registration এর সময় এগুলো প্রযোজ্য নয়
        Invoice::withoutGlobalScopes()->create([
            'invoice_no'     => 'REG_' . strtoupper(uniqid()),
            'transaction_id' => $tran_id,
            'type'           => 'registration',
            'total_amount'   => $fee,
            'payable_amount' => $fee,
            'status'         => 'pending',
            'meta'           => json_encode([
                'institution_name' => $data['institution_name'],
                'institution_type' => $data['institution_type'] ?? '',
                'email'            => $data['email'],
                'phone'            => $data['phone'],
                'timezone'         => $data['timezone'] ?? 'Asia/Dhaka',
                'admin_name'       => $data['admin_name'],
                'admin_email'      => $data['admin_email'],
                'password'         => $data['password'],
                'system_logo'             => session('pending_logo'),
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

        $record = Invoice::withoutGlobalScopes()
            ->where('transaction_id', $tran_id)
            ->where('type', 'registration')
            ->whereIn('status', ['pending', 'paid'])
            ->first();

        if (!$record) {
            Log::error('Record not found', ['tran_id' => $tran_id]);
            return redirect()->route('register')
                ->with('error', 'Invalid transaction।');
        }

        $meta = json_decode($record->meta, true);

        $existing = Institution::withoutGlobalScopes()
            ->where('email', $meta['email'])
            ->first();

        if ($existing) {
            // Institution আছে মানে IPN আগেই সব করে দিয়েছে
            // শুধু login করিয়ে দাও
            $user = User::withoutGlobalScopes()
                ->where('institution_id', $existing->id)
                ->where('role', 'admin')
                ->first();

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();
            }

            session()->forget(['pending_registration', 'pending_logo']);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Institution setup complete!');
        }

        // Institution নেই — validation করে তৈরি করো
        $sslc = new SslCommerzNotification();
        $validation = $sslc->orderValidate(
            $request->all(),
            $tran_id,
            $record->payable_amount,
            'BDT'
        );

        Log::info('Validation result', ['result' => $validation]);

        if (!$validation) {
            return redirect()->route('register')
                ->with('error', 'Payment যাচাই করা যায়নি।');
        }

        $user = null;

        try {
            DB::transaction(function () use ($meta, $tran_id, $request, &$user) {
                $institution = Institution::create([
                    'name'     => $meta['institution_name'],
                    'type'     => $meta['institution_type'] ?? null,
                    'email'    => $meta['email'],
                    'phone'    => $meta['phone'],
                    'timezone' => $meta['timezone'],
                    'status'   => true,
                ]);

                if (!empty($meta['system_logo'])) {
                    $institution->update(['system_logo' => $meta['system_logo']]);
                }

                $user = User::create([
                    'name'           => $meta['admin_name'],
                    'email'          => $meta['admin_email'],
                    'password'       => $meta['password'],
                    'role'           => 'admin',
                    'institution_id' => $institution->id,
                ]);

                Invoice::withoutGlobalScopes()
                    ->where('transaction_id', $tran_id)
                    ->update([
                        'institution_id' => $institution->id,
                        'status'         => 'paid',
                        'paid_at'        => now(),
                        'payment_method' => 'sslcommerz',
                        'val_id'         => $request->input('val_id'),
                    ]);
            });

            session()->forget(['pending_registration', 'pending_logo']);
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Institution setup complete!');

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

        $record = Invoice::withoutGlobalScopes()
            ->where('transaction_id', $tran_id)
            ->first();

        if (!$record) {
            Log::warning('IPN: Invoice not found', ['tran_id' => $tran_id]);
            return response()->json(['status' => 'not_found']);
        }

        $sslc       = new SslCommerzNotification();
        $validation = $sslc->orderValidate(
            $request->all(),
            $tran_id,
            $record->payable_amount,
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