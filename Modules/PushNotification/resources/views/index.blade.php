@extends('pushnotification::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('pushnotification.name') !!}</p>
@endsection
