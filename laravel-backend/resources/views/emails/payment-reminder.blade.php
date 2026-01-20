<x-mail::message>
# Payment Reminder ⏰

Hello **{{ $studentName }}**,

This is a friendly reminder that you have an outstanding payment due soon.

## Payment Details

<x-mail::panel>
**Amount Due**: ${{ $amountDue }}<br>
**Fee Type**: {{ $feeType }}<br>
**Due Date**: <strong style="color: #DC2626;">{{ $dueDate }}</strong>
</x-mail::panel>

## Make a Payment

To avoid late fees and ensure uninterrupted access to academic services, please make your payment as soon as possible.

<x-mail::button :url="$paymentUrl" color="primary">
Pay Now
</x-mail::button>

## Payment Options

- Credit/Debit Card
- Bank Transfer
- Mobile Money
- Cash (at Finance Office)

## Already Paid?

If you've already made this payment, please disregard this reminder. It may take 24-48 hours for payments to reflect in our system.

---

Thanks,<br>
**{{ config('app.name') }}**<br>
*Finance Department*
</x-mail::message>
