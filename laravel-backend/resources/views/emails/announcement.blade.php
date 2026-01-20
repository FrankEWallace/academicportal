<x-mail::message>
@if($priority === 'high')
# 🔴 Important Announcement
@elseif($priority === 'urgent')
# 🚨 URGENT Announcement
@else
# 📢 Announcement
@endif

{{ $title }}

{{ $message }}

@if($actionUrl && $actionText)
<x-mail::button :url="$actionUrl" color="{{ $priority === 'urgent' ? 'error' : 'primary' }}">
{{ $actionText }}
</x-mail::button>
@endif

<x-mail::panel>
**Date**: {{ $date }}<br>
@if($priority === 'urgent' || $priority === 'high')
**Priority**: {{ strtoupper($priority) }}
@endif
</x-mail::panel>

---

Thanks,<br>
**{{ config('app.name') }}**<br>
*Administration*
</x-mail::message>
