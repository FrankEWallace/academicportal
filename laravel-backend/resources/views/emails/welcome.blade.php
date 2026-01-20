<x-mail::message>
# Welcome to Academic Nexus Portal! 🎓

Hello **{{ $userName }}**,

We're excited to have you join the Academic Nexus Portal community! Your account has been successfully created.

## Your Account Details

- **Email**: {{ $userEmail }}
- **Role**: {{ ucfirst($role) }}
@if($temporaryPassword)
- **Temporary Password**: `{{ $temporaryPassword }}`
@endif

## Next Steps

1. Log in to your account using the button below
@if($temporaryPassword)
2. **Important**: Change your temporary password immediately after your first login
3. Complete your profile information
@else
2. Complete your profile information
3. Explore the features available to you
@endif

<x-mail::button :url="$loginUrl" color="primary">
Login to Your Account
</x-mail::button>

## Need Help?

If you have any questions or need assistance, please don't hesitate to contact our support team.

---

Thanks,<br>
**{{ config('app.name') }}**<br>
*Your Academic Success Partner*
</x-mail::message>
