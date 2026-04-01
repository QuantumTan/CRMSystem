@extends('layouts.app')

@section('title', 'Sales Dashboard')

@section('content')
    @include('dashboard._content', ['data' => $data])
@endsection
