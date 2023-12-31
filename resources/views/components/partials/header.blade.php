<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @livewireStyles
        @filamentStyles
        @vite('resources/sass/app.scss')

        {{ $slot }}
    </head>

    <body class="min-h-full bg-gradient-to-br from-teal-400 to-purple-400">
