@php
  if(!isset($design)) $design = 'light';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title')</title>
</head>

  @if($design == 'dark')
    <style>
      * {
        color: #cbd5e0 !important;
      }
    </style>
  @endif

<body class="{{ $design == 'light' ? 'bg-gray-200' : 'bg-gray-800' }} my-10 font-sans {{ $design == 'light' ? 'text-gray-700' : 'text-gray-400' }}">
  <div class="grid grid-cols-6">
    <div class="col-start-2 col-span-4">
      <div class="{{ $design == 'light' ? 'bg-white' : 'bg-gray-900' }} shadow border-t-2 {{ $design == 'light' ? 'border-green-500' : 'border-gray-800' }}">
        <div class="px-10">
          {{ $header }}
        </div>
        <div id="text" class="px-10 py-6 flex flex-col">
          @isset($greeting)
            {{ $greeting }}
          @endisset
          
            {{ $slot }}
          
          @isset($signature)
            {{ $signature }}
          @endisset
        </div>
      </div>

      @isset($below_card)
      <div id="bottomText" class="text-center my-4 px-10">
        {{ $below_card }}
      </div>
      @endisset

    </div>
  </div>
</body>

</html>