<!-- resources/views/groups/recommendations.blade.php -->
<x-app-layout>
    <h1>Recommended Groups</h1>
    <ul>
        @foreach($groups as $group)
            <li>
                <a href="{{ route('groups.show', $group->id) }}">{{ $group->name }}</a>
                <p>Location: {{ $group->location }}</p>
                <p>Members: {{ $group->users_count }}</p>
            </li>
        @endforeach
    </ul>
</x-app-layout>
