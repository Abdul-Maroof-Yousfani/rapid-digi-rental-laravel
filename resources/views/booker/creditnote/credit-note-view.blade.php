@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first() . ' ' . 'Portal'))
@section('content')

Credit Note View

@endsection

@section('script')
    <script type="text/javascript">

    </script>
@endsection
