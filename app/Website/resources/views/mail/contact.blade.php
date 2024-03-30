@component('mail::message')
    # Contact Form Submission

    You have received a new message from your website's contact form.

    @component('mail::panel')
        **First Name:** {{ $data['first_name'] }}<br>
        **Last Name:** {{ $data['last_name'] }}<br>
        **Email:** {{ $data['email'] }}<br>
        **Subject:** {{ $data['subject'] }}<br>
        **Message:**<br>
        {{ $data['message'] }}
    @endcomponent
@endcomponent
