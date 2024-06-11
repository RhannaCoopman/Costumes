<x-app-layout>
    <post :post="{{ $post }}" :liked="{{ json_encode($liked) }}" :likes-count="{{ $likesCount }}"></post>
</x-app-layout>
