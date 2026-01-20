<x-mail::message>
# Verify Your Email Address ✉️

Hello **{{ $userName }}**,

Thank you for registering with Academic Nexus Portal! To complete your registration and activate your account, please verify your email address.

## Verify Your Email

Click the button below to verify your email address and get started:

<x-mail::button :url="$verificationUrl" color="success">
Verify Email Address
</x-mail::button>

This verification link will expire in **24 hours**. If you didn't create an account, no further action is required.

## Why Verify?

Email verification helps us:
- Ensure account security
- Send you important notifications
- Recover your account if needed
- Prevent unauthorized access

If you're having trouble clicking the button, copy and paste the URL below into your web browser:

{{ $verificationUrl }}

---

Thanks,<br>
**{{ config('app.name') }}**<br>
*Welcome Aboard!*
</x-mail::message>
