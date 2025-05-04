@php($header = 'Roles')
@extends('layouts.main')

@section('content')
    <div id="app">
        <datatable
            :headers='["Name", "Guard Name"]'
            :rows='@json($roles)'
            :actions="['edit', 'delete']"
            :options='{
                responsive: true, 
                pageLength: {{ $pageLength ?? 20 }}, 
                lengthMenu: [[20, 50, 100, 1000], [20, 50, 100, 1000]]
            }'
        />
    </div>
@endsection

@push('vite')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('template/css/datagrid/datatables/datatables.bundle.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('template/js/datagrid/datatables/datatables.bundle.js') }}"></script>
@endpush
