@props(['action', 'message' => 'Hapus catatan ini?'])
<form action="{{ $action }}" method="POST" data-confirm="{{ $message }}" class="inline">@csrf
    @method('DELETE')<button class="danger" type="submit">Hapus</button></form>
