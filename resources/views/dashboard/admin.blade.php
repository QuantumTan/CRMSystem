@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    @include('dashboard._content', ['data' => $data])
@endsection