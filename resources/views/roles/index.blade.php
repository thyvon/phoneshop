@php($header = 'Roles')
@extends('layouts.main')

@section('content')
<div id="app">
    <!-- Pass roles as a JSON string to the Vue component -->
    <role-list :roles="{{ $roles->toJson() }}"></role-list>
</div>

@push('styles')
    <!-- Include the DataTables CSS from Smart Admin Template -->
    <link rel="stylesheet" href="{{ asset('template/css/datagrid/datatables/datatables.bundle.css') }}">
@endpush

@push('scripts')
    <!-- Include the DataTables JS from Smart Admin Template -->
    <script src="{{ asset('template/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable for the rolelist table
            $('#rolelist').DataTable({
                responsive: true
            });
        });
    </script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush
@endsection
