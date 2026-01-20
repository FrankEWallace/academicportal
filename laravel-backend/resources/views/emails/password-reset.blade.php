<x-mail::message>
# Password Reset Request 🔐

Hello **{{ $userName }}**,

We received a request to reset your password for your Academic Nexus Portal account. If you didn't make this request, you can safely ignore this email.

## Reset Your Password

Click the button below to create a new password. This link will expire in **1 hour** ({{ $validUntil }}).

<x-mail::button :url="$resetUrl" color="primary">
Reset Password
</x-mail::button>

## Security Tips

- Never share your password with anyone
- Use a strong, unique password
- Change your password regularly
- Enable two-factor authentication when available

If you're having trouble clicking the button, copy and paste the URL below into your web browser:

{{ $resetUrl }}

---

Thanks,<br>
**{{ config('app.name') }}**<br>
*Keeping Your Account Secure*
</x-mail::message>
