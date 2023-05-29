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
  <link rel="apple-touch-icon" sizes="180x180" href="{{ $json['favicon_img'] }}">
  <link rel="stylesheet" type="text/css" href="/loader.css" />
  @vite(['resources/ts/main.ts'])
</head>
<style>
  #load-custom {
    max-height: 80px;
    color : {{ $json['color'] }};
  }
</style>
<body>
  <div id="app">
    <div id="loading-bg">
      <div class="loading-logo">
        <img src="{{ $json['logo_img'] }}" alt="Logo" onerror="loadRenderLoadingSvg()" id='load-custom'/>
        <div style="display:none;" alt="Logo" id='load-default'>
            <svg width="34" height="24" viewBox="0 0 34 24" fill="none" xmlns="http://www.w3.org/2000/svg" style='width: 100%;height: 100%;'>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00183571 0.3125V7.59485C0.00183571 7.59485 -0.141502 9.88783 2.10473 11.8288L14.5469 23.6837L21.0172 23.6005L19.9794 10.8126L17.5261 7.93369L9.81536 0.3125H0.00183571Z" fill="currentColor" style='opacity: 0.68;'></path>
                <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.17969 17.7762L13.3027 3.75173L17.589 8.02192L8.17969 17.7762Z" fill="#161616"></path>
                <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.58203 17.2248L14.8129 5.24231L17.6211 8.05247L8.58203 17.2248Z" fill="#161616"></path>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.25781 17.6914L25.1339 0.3125H33.9991V7.62657C33.9991 7.62657 33.8144 10.0645 32.5743 11.3686L21.0179 23.6875H14.5487L8.25781 17.6914Z" fill="currentColor" style='opacity: 0.68;'></path>
          </svg>
        </div>
      </div>
      <div class="loading">
        <div class="effect-1 effects"></div>
        <div class="effect-2 effects"></div>
        <div class="effect-3 effects"></div>
      </div>
    </div>
  </div>
  
    <script>
        const loadRenderLoadingSvg = () => {
            document.querySelector('#load-custom').style['display'] = 'none';
            document.querySelector('#load-default').style['display'] = 'block';
        }
        //const primaryColor = localStorage.getItem('Vuexy-initial-loader-color') || '#EA5455';
        //const loaderColor = "{{ $json['color'] }}" || '#FFFFFF';

        const loaderColor = localStorage.getItem('-initial-loader-bg') || '#FFFFFF';
        const primaryColor = "{{ $json['color'] }}" || '#EA5455';

        window.corp = {!! json_encode($json, true) !!};
        if (loaderColor)
            document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)
        if (primaryColor)
            document.documentElement.style.setProperty('--initial-loader-color', primaryColor)

    </script>
</body>

</html>
