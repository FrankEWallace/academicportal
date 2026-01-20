<x-mail::message>
# New Grade Published! 📊

Hello **{{ $studentName }}**,

Great news! Your grade for **{{ $courseName }}** ({{ $courseCode }}) has been published and is now available for viewing.

@if($grade)
## Your Grade

<x-mail::panel>
**Course**: {{ $courseName }} ({{ $courseCode }})<br>
**Grade**: <strong style="font-size: 1.2em; color: #059669;">{{ $grade }}</strong>
</x-mail::panel>
@else
## Your Grade is Ready

Your grade for **{{ $courseName }}** ({{ $courseCode }}) has been published. Click below to view your complete results.
@endif

<x-mail::button :url="$viewUrl" color="primary">
View All Results
</x-mail::button>

## What's Next?

- Review your complete academic record
- Check your GPA and degree progress
- Contact your instructor if you have questions

Keep up the great work! 🎉

---

Thanks,<br>
**{{ config('app.name') }}**<br>
*Celebrating Your Academic Success*
</x-mail::message>
