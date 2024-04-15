@component('mail::message')
# Contact Form Submission

You have received a new message from your website's contact form.

**First Name:** {{ $data['first_name'] }}

**Last Name:** {{ $data['last_name'] }}

**Email:** {{ $data['email'] }}

**Subject:** {{ $data['subject'] }}

**Message:**

{{ $data['message'] }}

@endcomponent
