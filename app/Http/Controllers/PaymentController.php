<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function list(): View
    {
        return view('payment.list');
    }

    public function createPay()
    {
        return view('payment.create');
    }

    public function pay(Request $request)
    {
        $price = $request->price;
        $vnp_BankCode = '';

        $payment = Payment::create([
            'name' => $request->name ?? 'Thanh toán hóa đơn phí dich vụ',
            'price' => $price,
            'payment_id' => $this->generateCouponCode()
        ]);

        session(['cost_id' => $payment->id]);
        session(['url_prev' => url()->previous()]);
        $vnp_TmnCode = "TQFMNMC5"; //Mã website tại VNPAY
        $vnp_HashSecret = "PHGIHNPJMMAKIXJOWGLLHXKMNKRRXDBP"; //Chuỗi bí mật
//        $vnp_Url = "http://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://qr-pay.khanhdev.local/return-vnpay";
        $vnp_TxnRef = $payment->payment_id; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = "Thanh toán hóa đơn phí dich vụ";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $price * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.0.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . $key . "=" . $value;
            } else {
                $hashdata .= $key . "=" . $value;
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;

        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            // $vnpSecureHash = md5($vnp_HashSecret . $hashdata);
//            $vnpSecureHash = hash('sha256', $vnp_HashSecret . $hashdata);
            $vnp_Url .= 'vnp_SecureHashType=SHA256&vnp_SecureHash=' . $vnpSecureHash;
        }
        return redirect($vnp_Url);

    }

    private function generateCouponCode()
    {
        $code = Payment::query()->selectRaw('MAX(CAST(payment_id AS unsigned)) as payment_id')->first();
        return ($code->payment_id ?? 0) + 1;
    }

    public function returnPay(Request $request)
    {
        $vnp_SecureHash = $request->input('vnp_SecureHash');
        $inputData = [];
        $vnp_HashSecret = "PHGIHNPJMMAKIXJOWGLLHXKMNKRRXDBP";

        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);

        $i = 0;
        $hashData = "";

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        $data = [
            'vnp_TxnRef' => $request->input('vnp_TxnRef'),
            'vnp_Amount' => $request->input('vnp_Amount'),
            'vnp_OrderInfo' => $request->input('vnp_OrderInfo'),
            'vnp_ResponseCode' => $request->input('vnp_ResponseCode'),
            'vnp_TransactionNo' => $request->input('vnp_TransactionNo'),
            'vnp_BankCode' => $request->input('vnp_BankCode'),
            'vnp_PayDate' => $request->input('vnp_PayDate'),
            'secureHash' => $secureHash,
            'isValidSignature' => $secureHash == $vnp_SecureHash,
        ];

        return view('vnpay-response', compact('data'));
    }
}
