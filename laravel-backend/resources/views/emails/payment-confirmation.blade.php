<x-mail::message>
# Payment Received! ✅

Hello **{{ $studentName }}**,

Thank you! We have successfully received your payment.

## Payment Details

<x-mail::panel>
**Amount Paid**: ${{ $amount }}<br>
**Payment Method**: {{ $paymentMethod }}<br>
**Transaction ID**: {{ $transactionId }}<br>
**Payment For**: {{ $paymentFor }}<br>
**Date**: {{ $date }}
</x-mail::panel>

## Receipt

This email serves as your official payment receipt. Please keep it for your records. You can also download a PDF receipt from your student portal.

<x-mail::button :url="$viewUrl" color="success">
View Payment History
</x-mail::button>

## Questions?

If you have any questions about this payment or need additional documentation, please contact our finance office.

---

Thanks,<br>
**{{ config('app.name') }}**<br>
*Finance Department*
</x-mail::message>
