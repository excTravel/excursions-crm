@extends('layouts.app')

@section('title', 'Страница не найдена')

@section('content')
    <x-error-page
        code="404"
        title="Страница не найдена"
        message="Упс! Похоже, такой страницы не существует." />
@endsection
