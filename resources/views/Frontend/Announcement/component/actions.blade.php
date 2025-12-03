<img onclick="window.location.href='{{ url('/announcements/update/'.$announcement->id) }}'" src="{{ asset('assets/images/Edit.svg') }}">
<img class="delete-announcement-btn" data-announcement-id="{{ $announcement->id }}" src="{{ asset('assets/images/delete-icon.svg') }}" alt="delete">


