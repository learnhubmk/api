@component('mail::message')
# Порака од контакт формата на LearnHub.mk

**Name:** {{ $data['name'] }}

**Email:** {{ $data['email'] }}

**Message:**

{{ $data['message'] }}

@endcomponent
