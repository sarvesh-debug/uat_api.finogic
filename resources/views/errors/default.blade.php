@extends('errors.layout')

@section('content')
@php
    $code = $exception->getStatusCode() ?? 'Error';
    $message = $exception->getMessage() ?: 'Something went wrong';
    $description = "Please try again or contact support if the problem persists.";
@endphp
