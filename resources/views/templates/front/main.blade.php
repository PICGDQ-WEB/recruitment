<!DOCTYPE html>
<html lang="en">

<head>
@if(!empty($pageseo[0]->description))
<title>{{ $pageseo[0]->title }}</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{{ $pageseo[0]->description }}">
<meta name="keywords" content="{{ $pageseo[0]->keywords }}">
<meta name="author" content="{{ $pageseo[0]->author }}">
@if($pageseo[0]->slug == 'home')
<link rel="canonical" href="{{ url('/') }}" />
@else
<link rel="canonical" href="{{ url('/') }}/{{$pageseo[0]->slug}}" />
@endif
<meta property="og:locale" content="en_US" />
<meta property="og:type" content="website" />
<meta property="og:title" content="{{ $pageseo[0]->title }}" />
<meta property="og:description" content="{{ $pageseo[0]->description }}" />
@if($pageseo[0]->slug == 'home')
<meta property="og:url" content="{{ url('/') }}" />
@else
<meta property="og:url" content="{{ url('/') }}/{{$pageseo[0]->slug}}" />
@endif
<meta property="og:site_name" content="{{ $gseo->sitename }}" />
<meta property="article:publisher" content="{{ $gseo->facebook }}" />
<meta property="article:published_time" content="{{ $pageseo[0]->created_at }}" />
<meta property="article:modified_time" content="{{ $pageseo[0]->updated_at }}" />
<meta property="og:image" content="{{ url('/') }}/pages/{{$pageseo[0]->file}}" />
<meta property="og:image:width" content="1280" />
<meta property="og:image:height" content="720" />
<meta property="og:image:type" content="image/webp" />
@endif
@if(!empty($pageseo[0]->smarkup))
{!! $pageseo[0]->smarkup !!}
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="/assets/css/front/header.css">
<link rel="stylesheet" href="/assets/css/front/footer.css">
@yield('customcss')
<link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
<link href="/assets/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="/assets/css/front/style.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
{!! $gseo->globalheader !!}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <x-header />
    @yield('body')
    <x-footer />
    {!! $gseo->gfbs !!}

    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    @yield('customjs')
    {!! $gseo->gfas !!}

</body>

</html>