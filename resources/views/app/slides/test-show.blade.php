<x-sandboxed-frame-layout :isTest="true">
    <x-slot:path_url>{{ $slidePublicPath }}</x-slot>
    <x-slot:author_name>{{ $slide->name }}</x-slot>
</x-sandboxed-frame-layout>
