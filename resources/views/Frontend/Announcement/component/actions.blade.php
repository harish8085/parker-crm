<a href="{{ route('announcements.show', $announcement->id) }}" title="View" style="margin-right: 8px; display: inline-block;">
    <i class="fas fa-eye" style="color: #007bff; font-size: 18px; cursor: pointer;"></i>
</a>
<a href="{{ route('announcements.logs', $announcement->id) }}" title="View Logs" style="margin-right: 8px; display: inline-block;">
    <i class="fas fa-list-alt" style="color: #28a745; font-size: 18px; cursor: pointer;"></i>
</a>
@if(auth()->user()->hasPermission('announcements','update'))
<img onclick="window.location.href='{{ url('/announcements/update/'.$announcement->id) }}'" src="{{ asset('assets/images/Edit.svg') }}" style="cursor: pointer; margin-right: 8px;" title="Edit" alt="edit">
@endif
@if(auth()->user()->hasPermission('announcements','delete'))
<img class="delete-announcement-btn" data-announcement-id="{{ $announcement->id }}" src="{{ asset('assets/images/delete-icon.svg') }}" alt="delete" title="Delete" style="cursor: pointer;">
@endif


