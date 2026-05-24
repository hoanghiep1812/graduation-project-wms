@if(isset($notifications) && $notifications->count() > 0)
    @foreach($notifications as $noti)
        <div class="d-flex flex-stack py-4 border-bottom border-gray-300 {{ !$noti->is_read ? 'bg-light rounded px-3' : '' }}">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-35px me-4">
                    <span class="symbol-label bg-light-{{ $noti->type }}">
                        <i class="ki-duotone ki-bell fs-2 text-{{ $noti->type }}">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </span>
                </div>
                <div class="mb-0 me-2">
                    <a href="{{ route('notifications.read', $noti->id) }}"
                        class="fs-6 text-gray-800 text-hover-primary fw-bold">{{ $noti->title }}</a>
                    <div class="text-gray-400 fs-7">{{ $noti->content }}</div>
                </div>
            </div>
            <span class="badge badge-light fs-8">{{ $noti->created_at->diffForHumans() }}</span>
        </div>
    @endforeach
@else
    <div class="text-center text-muted py-5">Chưa có thông báo mới</div>
@endif