@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
    @include('dashboard._content', ['data' => $data])
@endsection
