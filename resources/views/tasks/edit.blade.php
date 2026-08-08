<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('tasks.title_edit') }} — {{ $task->title }}</h2>
            @can('delete', $task)
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('{{ __('tasks.confirm_delete') }}');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">{{ __('app.delete') }}</button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('tasks.update', $task) }}">
                @csrf
                @method('put')
                @include('tasks._form')
            </form>
        </div>
    </div>
</x-app-layout>
