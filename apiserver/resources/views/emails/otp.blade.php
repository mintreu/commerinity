<x-mail::message>
# Your OTP Code

You requested an OTP to {{ $purposeText }}.

<x-mail::panel>
**Your OTP: {{ $otp }}**
</x-mail::panel>

This OTP is valid for **10 minutes**.

If you didn't request this code, please ignore this email.

Thanks,<br>
{{ $appName }}
</x-mail::message>
