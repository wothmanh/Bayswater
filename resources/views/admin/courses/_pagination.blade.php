@if ($courses->hasPages())
    <div class="mt-4">
        {{ $courses->links() }}
    </div>
@endif