<x-app-layout>
    <post :post="{{ $post }}" :liked="{{ json_encode($liked) }}" :likes-count="{{ $likesCount }}" :saved="{{ json_encode($saved) }}"></post>
</x-app-layout>
