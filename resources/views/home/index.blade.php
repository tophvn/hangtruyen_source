@extends('layouts.app')

@section('content')
    @include('home.components.manga-slider')
    @include('home.components.manga-trend')

    <div class="container">
        <div class="row">
            <div class="col-12 col-xl-8">
                @include('home.components.manga-new-update')
            </div>
            <div class="col-12 col-xl-4">
                @include('components.top-follow')
            </div>
        </div>
    </div>

    @include('home.components.manga-suggest')
    @include('home.components.top-comments')
    @include('home.components.manga-feature-genres')
    @include('home.components.manga-select')
    @include('home.components.manga-finish')
    @include('home.components.blog-section')

    <script>
        async function getNewlyUpdatedHot() {
            return null;
        }

        async function getSuggestionMangas() {
            return null;
        }

        async function followManga(mangaId) {
            return null;
        }
    </script>
@endsection
