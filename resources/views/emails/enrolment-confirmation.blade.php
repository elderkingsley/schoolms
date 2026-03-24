@component('mail::message')
# Enrolment Application Received

Dear {{ $parentName }},

Thank you for submitting an enrolment application for **{{ $studentFirstName }} {{ $studentLastName }}**.

We have received your application and our admin team will review it within **1–2 business days**.

Once approved, you will receive a separate email with your **parent portal login credentials**, which will allow you to view results, fees, lesson notes and other general school information going forward.

Thanks,<br>
**The Nurtureville School**
@endcomponent
