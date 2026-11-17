@extends('layouts.app')

@section('title', 'Ошибка сервера')

@section('content')
    <x-error-page
        code="500"
        title="Ошибка сервера"
        message="Произошла внутренняя ошибка сервера. Мы уже работаем над её устранением." />
@endsection
