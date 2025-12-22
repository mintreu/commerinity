@component('mail::message')
# {{ ucfirst($purpose) }} Email Verification

Hello,

Please use the following One-Time Password (OTP) to verify your email address for **{{ $purpose }}**:

@component('mail::panel')
        ## {{ $otp }}
@endcomponent

This OTP will expire in **5 minutes**.
If you did not request this, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
