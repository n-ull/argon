@component('mail::message')
# You have been invited to be a promoter!

You have been invited to promote **{{ $companyName }}** events.

@component('mail::button', ['url' => route('promoters.invitations.show', $invitation->token)])
Accept Invitation
@endcomponent

If you did not expect this invitation, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent