<x-app-layout>
    <div class="flex_column">
        <div class="flex_between">
            <h1>Hi {{ $user->name }}</h1>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a class="rounded_corners_container white_color" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    Log out
                </a>
            </form>
        </div>


        {{-- Saved items --}}
        <div class="image-container">
            <div class="image-navigation">
                <div>
                    <img src="/images/saved.svg" />
                    <p>Saved items</p>
                </div>
                {{-- <div>
                    <p>See all</p>
                    <img src="/images/arrow-right.svg" />
                </div> --}}
            </div>
            <div class="scroll-container">
                <div class="grid">
                    @foreach ($user->savedPosts as $post)
                        <a class="item" href="{{ route('post.detail', ['post' => $post->uuid]) }}">
                            <img src="{{ Storage::disk('public')->url($post->firstImage->path) }}" />
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Uploaded items --}}
        <div class="image-container">
            <div class="image-navigation">
                <div>
                    <img src="/images/saved.svg" />
                    <p>Your uploads</p>
                </div>
                {{-- <div>
                    <p>See all</p>
                    <img src="/images/arrow-right.svg" />
                </div> --}}
            </div>
            <div class="scroll-container">
                <div class="grid">
                    @foreach ($user->uploadedPosts as $post)
                        <a class="item" href="{{ route('post.detail', ['post' => $post->uuid]) }}">
                            <img src="{{ Storage::disk('public')->url($post->firstImage->path) }}" />
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>




    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot> --}}




</x-app-layout>
