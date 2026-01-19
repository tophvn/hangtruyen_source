@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->description ?? '')

@section('content')
<main>
    <div class="container">
        <div class="page-breadcrumb">
            <span class="item"><a href="/">Trang chủ</a></span>
            <span class="item"><a href="/tin-tuc">Tin tức</a></span>
            <span class="item breadcrumb_last" aria-current="page">{{ $post->title }}</span>
        </div>
        <div class="row">
            <div class="col-12 col-xl-8">
                <article class="blog-detail">
                    <h1 class="blog-title">{{ $post->title }}</h1>
                    <div class="blog-meta mb-3">
                        <span class="author">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8.10646 7.74732C8.08646 7.74732 8.07313 7.74732 8.05313 7.74732C8.01979 7.74065 7.97313 7.74065 7.93313 7.74732C5.99979 7.68732 4.53979 6.16732 4.53979 4.29398C4.53979 2.38732 6.09313 0.833984 7.99979 0.833984C9.90646 0.833984 11.4598 2.38732 11.4598 4.29398C11.4531 6.16732 9.98646 7.68732 8.12646 7.74732C8.11979 7.74732 8.11313 7.74732 8.10646 7.74732ZM7.99979 1.83398C6.64646 1.83398 5.5398 2.94065 5.5398 4.29398C5.5398 5.62732 6.57979 6.70065 7.90646 6.74732C7.93979 6.74065 8.03313 6.74065 8.1198 6.74732C9.42646 6.68732 10.4531 5.61398 10.4598 4.29398C10.4598 2.94065 9.35313 1.83398 7.99979 1.83398Z" fill="#787978"/>
                                <path d="M8.11307 15.0327C6.80641 15.0327 5.49307 14.6993 4.49974 14.0327C3.57307 13.4193 3.06641 12.5793 3.06641 11.666C3.06641 10.7527 3.57307 9.90602 4.49974 9.28602C6.49974 7.95935 9.73974 7.95935 11.7264 9.28602C12.6464 9.89935 13.1597 10.7393 13.1597 11.6527C13.1597 12.566 12.6531 13.4127 11.7264 14.0327C10.7264 14.6993 9.41974 15.0327 8.11307 15.0327ZM5.05307 10.126C4.41307 10.5527 4.06641 11.0993 4.06641 11.6727C4.06641 12.2393 4.41974 12.786 5.05307 13.206C6.71307 14.3193 9.51307 14.3193 11.1731 13.206C11.8131 12.7793 12.1597 12.2327 12.1597 11.6593C12.1597 11.0927 11.8064 10.546 11.1731 10.126C9.51307 9.01935 6.71307 9.01935 5.05307 10.126Z" fill="#787978"/>
                            </svg>{{ $post->author ?? 'Admin' }}
                        </span>
                        <span class="date">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8.00008 15.1667C4.50675 15.1667 1.66675 12.3267 1.66675 8.83333C1.66675 5.34 4.50675 2.5 8.00008 2.5C11.4934 2.5 14.3334 5.34 14.3334 8.83333C14.3334 12.3267 11.4934 15.1667 8.00008 15.1667ZM8.00008 3.5C5.06008 3.5 2.66675 5.89333 2.66675 8.83333C2.66675 11.7733 5.06008 14.1667 8.00008 14.1667C10.9401 14.1667 13.3334 11.7733 13.3334 8.83333C13.3334 5.89333 10.9401 3.5 8.00008 3.5Z" fill="#787978"/>
                                <path d="M8 9.16732C7.72667 9.16732 7.5 8.94065 7.5 8.66732V5.33398C7.5 5.06065 7.72667 4.83398 8 4.83398C8.27333 4.83398 8.5 5.06065 8.5 5.33398V8.66732C8.5 8.94065 8.27333 9.16732 8 9.16732Z" fill="#787978"/>
                                <path d="M10 1.83398H6C5.72667 1.83398 5.5 1.60732 5.5 1.33398C5.5 1.06065 5.72667 0.833984 6 0.833984H10C10.2733 0.833984 10.5 1.06065 10.5 1.33398C10.5 1.60732 10.2733 1.83398 10 1.83398Z" fill="#787978"/>
                            </svg>{{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : '' }}
                        </span>
                    </div>
                    
                    @if($post->image)
                        <div class="blog-image mb-4">
                            <img src="{{ $post->image }}" alt="{{ $post->title }}" class="img-fluid">
                        </div>
                    @endif
                    
                    @if($post->description)
                        <div class="blog-excerpt mb-4">
                            <p class="lead">{{ $post->description }}</p>
                        </div>
                    @endif
                    
                    <div class="blog-content">
                        {!! $post->content !!}
                    </div>
                </article>
                
                @if($relatedPosts->count() > 0)
                    <div class="related-posts mt-5">
                        <h3 class="m-title title">Tin tức liên quan
                            <span class="sub">Các bài viết khác</span>
                        </h3>
                        <div class="list-wrapper">
                            @foreach($relatedPosts as $relatedPost)
                                <div class="item-card horizontal">
                                    <div class="item-image">
                                        <a href="/tin-tuc/{{ $relatedPost->slug }}" class="item-link"></a>
                                        <img src="{{ $relatedPost->image ?? asset('images/pre-load1.png') }}" alt="{{ $relatedPost->title }}">
                                    </div>
                                    <div class="item-content">
                                        <h3 class="item-title">
                                            <a href="/tin-tuc/{{ $relatedPost->slug }}">{{ $relatedPost->title }}</a>
                                        </h3>
                                        <div class="item-description">{{ $relatedPost->description ?? '' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-12 col-xl-4">
                <div class="blog-sidebar mb-5">
                    <h3 class="title">TIN TỨC NỔI BẬT<span class="sub">Tin tức được quan tâm</span></h3>
                    <div class="blog-horizontal">
                        @if($featuredPost)
                            <div class="item-card horizontal">
                                <div class="item-image">
                                    <a href="/tin-tuc/{{ $featuredPost->slug }}" class="item-link" alt=""></a>
                                    <img src="{{ $featuredPost->image ?? asset('images/pre-load1.png') }}" alt="{{ $featuredPost->title }}">
                                </div>
                                <div class="item-content">
                                    <div class="top">
                                        <div class="item-info">
                                            <span class="author">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8.10646 7.74732C8.08646 7.74732 8.07313 7.74732 8.05313 7.74732C8.01979 7.74065 7.97313 7.74065 7.93313 7.74732C5.99979 7.68732 4.53979 6.16732 4.53979 4.29398C4.53979 2.38732 6.09313 0.833984 7.99979 0.833984C9.90646 0.833984 11.4598 2.38732 11.4598 4.29398C11.4531 6.16732 9.98646 7.68732 8.12646 7.74732C8.11979 7.74732 8.11313 7.74732 8.10646 7.74732ZM7.99979 1.83398C6.64646 1.83398 5.5398 2.94065 5.5398 4.29398C5.5398 5.62732 6.57979 6.70065 7.90646 6.74732C7.93979 6.74065 8.03313 6.74065 8.1198 6.74732C9.42646 6.68732 10.4531 5.61398 10.4598 4.29398C10.4598 2.94065 9.35313 1.83398 7.99979 1.83398Z" fill="#787978"/>
                                                    <path d="M8.11307 15.0327C6.80641 15.0327 5.49307 14.6993 4.49974 14.0327C3.57307 13.4193 3.06641 12.5793 3.06641 11.666C3.06641 10.7527 3.57307 9.90602 4.49974 9.28602C6.49974 7.95935 9.73974 7.95935 11.7264 9.28602C12.6464 9.89935 13.1597 10.7393 13.1597 11.6527C13.1597 12.566 12.6531 13.4127 11.7264 14.0327C10.7264 14.6993 9.41974 15.0327 8.11307 15.0327ZM5.05307 10.126C4.41307 10.5527 4.06641 11.0993 4.06641 11.6727C4.06641 12.2393 4.41974 12.786 5.05307 13.206C6.71307 14.3193 9.51307 14.3193 11.1731 13.206C11.8131 12.7793 12.1597 12.2327 12.1597 11.6593C12.1597 11.0927 11.8064 10.546 11.1731 10.126C9.51307 9.01935 6.71307 9.01935 5.05307 10.126Z" fill="#787978"/>
                                                </svg>{{ $featuredPost->author ?? 'Admin' }}
                                            </span>
                                            <span class="date">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8.00008 15.1667C4.50675 15.1667 1.66675 12.3267 1.66675 8.83333C1.66675 5.34 4.50675 2.5 8.00008 2.5C11.4934 2.5 14.3334 5.34 14.3334 8.83333C14.3334 12.3267 11.4934 15.1667 8.00008 15.1667ZM8.00008 3.5C5.06008 3.5 2.66675 5.89333 2.66675 8.83333C2.66675 11.7733 5.06008 14.1667 8.00008 14.1667C10.9401 14.1667 13.3334 11.7733 13.3334 8.83333C13.3334 5.89333 10.9401 3.5 8.00008 3.5Z" fill="#787978"/>
                                                    <path d="M8 9.16732C7.72667 9.16732 7.5 8.94065 7.5 8.66732V5.33398C7.5 5.06065 7.72667 4.83398 8 4.83398C8.27333 4.83398 8.5 5.06065 8.5 5.33398V8.66732C8.5 8.94065 8.27333 9.16732 8 9.16732Z" fill="#787978"/>
                                                    <path d="M10 1.83398H6C5.72667 1.83398 5.5 1.60732 5.5 1.33398C5.5 1.06065 5.72667 0.833984 6 0.833984H10C10.2733 0.833984 10.5 1.06065 10.5 1.33398C10.5 1.60732 10.2733 1.83398 10 1.83398Z" fill="#787978"/>
                                                </svg>{{ $featuredPost->published_at ? $featuredPost->published_at->format('d/m/Y') : '' }}
                                            </span>
                                        </div>
                                    </div>
                                    <h3 class="item-title"><a href="/tin-tuc/{{ $featuredPost->slug }}" alt="">{{ $featuredPost->title }}</a></h3>
                                    <div class="item-description">{{ Str::limit($featuredPost->description ?? '', 100) }}</div>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">Chưa có tin tức nổi bật nào.</p>
                        @endif
                    </div>
                </div>
                
                <div class="blog-sidebar mb-5">
                    <h3 class="title">TIN TỨC LIÊN QUAN<span class="sub">Tin tức liên quan</span></h3>
                    <div class="blog-horizontal">
                        @forelse($relatedPosts as $relatedPost)
                            <div class="item-card horizontal">
                                <div class="item-image">
                                    <a href="/tin-tuc/{{ $relatedPost->slug }}" class="item-link" alt=""></a>
                                    <img src="{{ $relatedPost->image ?? asset('images/pre-load1.png') }}" alt="{{ $relatedPost->title }}">
                                </div>
                                <div class="item-content">
                                    <div class="top">
                                        <div class="item-info">
                                            <span class="author">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8.10646 7.74732C8.08646 7.74732 8.07313 7.74732 8.05313 7.74732C8.01979 7.74065 7.97313 7.74065 7.93313 7.74732C5.99979 7.68732 4.53979 6.16732 4.53979 4.29398C4.53979 2.38732 6.09313 0.833984 7.99979 0.833984C9.90646 0.833984 11.4598 2.38732 11.4598 4.29398C11.4531 6.16732 9.98646 7.68732 8.12646 7.74732C8.11979 7.74732 8.11313 7.74732 8.10646 7.74732ZM7.99979 1.83398C6.64646 1.83398 5.5398 2.94065 5.5398 4.29398C5.5398 5.62732 6.57979 6.70065 7.90646 6.74732C7.93979 6.74065 8.03313 6.74065 8.1198 6.74732C9.42646 6.68732 10.4531 5.61398 10.4598 4.29398C10.4598 2.94065 9.35313 1.83398 7.99979 1.83398Z" fill="#787978"/>
                                                    <path d="M8.11307 15.0327C6.80641 15.0327 5.49307 14.6993 4.49974 14.0327C3.57307 13.4193 3.06641 12.5793 3.06641 11.666C3.06641 10.7527 3.57307 9.90602 4.49974 9.28602C6.49974 7.95935 9.73974 7.95935 11.7264 9.28602C12.6464 9.89935 13.1597 10.7393 13.1597 11.6527C13.1597 12.566 12.6531 13.4127 11.7264 14.0327C10.7264 14.6993 9.41974 15.0327 8.11307 15.0327ZM5.05307 10.126C4.41307 10.5527 4.06641 11.0993 4.06641 11.6727C4.06641 12.2393 4.41974 12.786 5.05307 13.206C6.71307 14.3193 9.51307 14.3193 11.1731 13.206C11.8131 12.7793 12.1597 12.2327 12.1597 11.6593C12.1597 11.0927 11.8064 10.546 11.1731 10.126C9.51307 9.01935 6.71307 9.01935 5.05307 10.126Z" fill="#787978"/>
                                                </svg>{{ $relatedPost->author ?? 'Admin' }}
                                            </span>
                                            <span class="date">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8.00008 15.1667C4.50675 15.1667 1.66675 12.3267 1.66675 8.83333C1.66675 5.34 4.50675 2.5 8.00008 2.5C11.4934 2.5 14.3334 5.34 14.3334 8.83333C14.3334 12.3267 11.4934 15.1667 8.00008 15.1667ZM8.00008 3.5C5.06008 3.5 2.66675 5.89333 2.66675 8.83333C2.66675 11.7733 5.06008 14.1667 8.00008 14.1667C10.9401 14.1667 13.3334 11.7733 13.3334 8.83333C13.3334 5.89333 10.9401 3.5 8.00008 3.5Z" fill="#787978"/>
                                                    <path d="M8 9.16732C7.72667 9.16732 7.5 8.94065 7.5 8.66732V5.33398C7.5 5.06065 7.72667 4.83398 8 4.83398C8.27333 4.83398 8.5 5.06065 8.5 5.33398V8.66732C8.5 8.94065 8.27333 9.16732 8 9.16732Z" fill="#787978"/>
                                                    <path d="M10 1.83398H6C5.72667 1.83398 5.5 1.60732 5.5 1.33398C5.5 1.06065 5.72667 0.833984 6 0.833984H10C10.2733 0.833984 10.5 1.06065 10.5 1.33398C10.5 1.60732 10.2733 1.83398 10 1.83398Z" fill="#787978"/>
                                                </svg>{{ $relatedPost->published_at ? $relatedPost->published_at->format('d/m/Y') : '' }}
                                            </span>
                                        </div>
                                    </div>
                                    <h3 class="item-title"><a href="/tin-tuc/{{ $relatedPost->slug }}" alt="">{{ $relatedPost->title }}</a></h3>
                                    <div class="item-description">{{ Str::limit($relatedPost->description ?? '', 100) }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">Chưa có tin tức liên quan nào.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
