<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WOW Carmen - Handicrafts</title>

  <meta name="description" content="WOW Carmen Handicrafts - Handmade water hyacinth crafts for sustainable living.">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>

<body class="bg-white overflow-x-hidden">

  <!-- Navbar -->
  <nav class="fixed top-0 left-0 w-full z-50 px-6 md:px-16 py-4 flex justify-between items-center bg-white shadow-md">
    
    <!-- Logo -->
    <div class="flex items-center space-x-3">
      <img src="{{ asset('images/logo.png') }}" alt="WOW Carmen Logo" class="h-12 w-auto object-contain">
      <span class="text-2xl font-bold text-[#A9793E]">Wow Carmen</span>
    </div>

    <!-- Links -->
    <div class="space-x-6 text-sm font-semibold text-gray-800">
      <a href="{{ route('login') }}" class="hover:text-[#A9793E] transition">Login</a>
      <a href="{{ route('register') }}" class="hover:text-[#A9793E] transition">Register</a>
    </div>
  </nav>

  <!-- Hero -->
  <section class="w-full min-h-screen bg-cover bg-center flex items-center justify-center pt-20"
           style="background-image: url('{{ asset('images/welcome-bg.jpg') }}');">
    
    <div class="bg-white bg-opacity-80 backdrop-blur-md p-10 rounded-md text-center shadow-lg max-w-xl mx-auto">
      <h1 class="text-3xl md:text-4xl font-semibold text-gray-900 mb-4 tracking-wide">
        Wow Carmen Handicrafts
      </h1>
      <p class="text-gray-700 text-sm md:text-base mb-6 leading-relaxed">
        Handmade water hyacinth crafts — crafted with purpose, sustainability, and love.
      </p>

      <a href="{{ route('login') }}"
         class="inline-block bg-[#A9793E] hover:bg-[#8F6532] text-white font-semibold px-6 py-3 rounded-full transition">
        Shop Now
      </a>
    </div>
  </section>

</body>
</html>
