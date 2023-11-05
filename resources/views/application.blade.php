<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" href="{{ $json['favicon_img'] }}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $json['name'] }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $json['name'] }}">
    <meta property="og:image" content="{{ $json['og_img'] }}" >
    <meta property="og:description" content="{{ $json['og_description'] }}">
    <meta name="author" content="purplevery">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $json['name'] }}">
    <meta name="theme-color" content="{{ $json['color'] }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="manifest" id="my-manifest" data-pwa-version="2.1" href=''>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $json['favicon_img'] }}">
    <link rel="stylesheet" type="text/css" href="/loader.css" />
    @vite(['resources/ts/main.ts'])
  </head>
  <style>
    #load-custom {
      width: 80px;
      height: 80px;
    }
    #load-default {
      width: 80px;
      height: 80px;
    }
    .hidden {
      display: none;
    }
  </style>
  <body>
    <div id="app">
      <div id="loading-bg">
        <div class="loading-logo">
          <img src="{{ $json['logo_img'] }}" alt="Logo" id='load-custom'/>
          <div class="hidden" alt="Logo" id='load-default'>
            <!-- 로고 없을때 기본 페이베리 이미지 -->
              <img src="/logo.svg" alt="Logo" />
          </div>
        </div>
        <div class="loading">
          <div class="effect-1 effects"></div>
          <div class="effect-2 effects"></div>
          <div class="effect-3 effects"></div>
        </div>
      </div>
    </div>
  </body>
</html>

<script>
  document.getElementById('load-custom').onerror = function () {
      document.getElementById('load-custom').classList.add('hidden');
      document.getElementById('load-default').classList.remove('hidden');
  }

  const loaderColor = localStorage.getItem("{{ $json['name'] }}-initial-loader-bg") || '#FFFFFF';
  const primaryColor = "{{ $json['color'] }}" || '#EA5455';

  localStorage.setItem(`{{ $json['name'] }}-lightThemePrimaryColor`, primaryColor)
  localStorage.setItem(`{{ $json['name'] }}-darkThemePrimaryColor`, primaryColor)

  window.corp = {!! json_encode($json, true) !!};
  if (loaderColor)
      document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)
  if (primaryColor)
      document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
</script>
