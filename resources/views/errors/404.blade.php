@extends('errors.layout')

@section('content')
@php
    $code = 404;
    $message = "Page Not Found";
    $description = "The page you are looking for might have been removed, renamed, or is temporarily unavailable.";
@endphp
