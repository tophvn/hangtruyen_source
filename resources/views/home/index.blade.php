@extends('layouts.app')

@section('content')
    @include('home.components.manga-slider')
    @include('home.components.manga-trend')
    @include('home.components.manga-suggest')

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

    @include('home.components.top-comments')
    @include('home.components.manga-feature-genres')
    @include('home.components.manga-select')
    @include('home.components.manga-finish')
    @include('home.components.blog-section')

    <script>
        async function getNewlyUpdatedHot() {
            const response = await $.ajax({
                type: 'GET',
                xhrFields: {
                    withCredentials: true
                },
                url: `https://api.hangtruyen.vip/mangas/newly-updated/hot`,
                contentType: 'application/json',
            }).catch(() => {
                return null;
            });

            if (response && response.status === 200) {
                return response.data;
            }
            return null;
        }

        async function getSuggestionMangas() {
            const response = await $.ajax({
                type: 'GET',
                xhrFields: {
                    withCredentials: true
                },
                url: `https://api.hangtruyen.vip/users/suggestion-mangas?q=16`,
                contentType: 'application/json',
            }).catch(() => {
                return null;
            });

            if (response) {
                return response.data.html;
            }
            return null;
        }

        async function followManga(mangaId) {
            const response = await $.ajax({
                type: 'POST',
                xhrFields: {
                    withCredentials: true
                },
                url: 'https://api.hangtruyen.vip/mangas/' + mangaId + '/follow',
                contentType: 'application/json',
            }).catch(() => {
                return null;
            });

            if (response && response.status === 200) {
                return response.data;
            }

            return null;
        }
    </script>
@endsection
