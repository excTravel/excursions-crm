@extends('layouts.app')

@section('title', 'Доступ запрещён')

@section('content')
    <x-error-page
        code="403"
        title="Доступ запрещён"
        message="У вас нет прав для просмотра этой страницы." />
@endsection
