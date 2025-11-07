<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>WOW Carmen - Cart</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ dropdownOpen:false, mobileMenuOpen:false, scrolled:false, cart:{items:[], subtotal:0, total:0}, loading:true }" @scroll.window="scrolled = window.scrollY > 4" class="bg-white" style="font-family:'Poppins','Inter',ui-sans-serif,system-ui;">

	<!-- Navbar -->
	<nav :class="scrolled ? 'shadow-sm' : ''" class="fixed top-0 inset-x-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex items-center justify-between h-16">
				<div class="flex items-center">
					<img src="/images/logo.png" alt="WOW Carmen" class="w-8 h-8 mr-3"/>
					<a href="/" class="text-lg sm:text-xl font-semibold text-gray-900 tracking-wide">WOW Carmen</a>
				</div>
				<div class="hidden md:flex items-center space-x-8">
					<a href="/dashboard" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Home</a>
					<a href="/products" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Products</a>
					<a href="/contact" class="text-gray-700 hover:text-[#c59d5f] text-sm font-medium transition-colors">Contact us</a>
				</div>
				<div class="hidden md:flex items-center space-x-4">
					<a href="#" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Search">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z"/></svg>
					</a>
					<a href="/cart" class="text-gray-700 hover:text-[#c59d5f] transition-colors" aria-label="Cart">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m2.6 8L6 5H3m4 8a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
					</a>
				</div>
			</div>
		</div>
	</nav>

	<!-- Content -->
	<section class="pt-24 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-init="fetch('/api/v1/cart', {credentials:'same-origin'}).then(r=>r.json()).then(d=>{cart=d; loading=false})">
			<a href="/products" class="text-sm text-[#c59d5f] hover:underline">Back to shopping</a>

			<h1 class="mt-4 text-2xl md:text-3xl font-bold text-gray-900">Your cart items</h1>

			<!-- Table header -->
			<!-- Section Headers -->
			<div class="mt-6 hidden md:grid grid-cols-12 text-xs font-medium text-gray-500">
				<div class="col-span-6">Product</div>
				<div class="col-span-2 text-right">Price</div>
				<div class="col-span-2 text-center">Quantity</div>
				<div class="col-span-2 text-right">Total</div>
			</div>

			<!-- Custom Order Notice -->
			<div class="mt-3" x-show="cart.items.some(it => it.type === 'custom')">
				<div class="p-4 bg-yellow-50 border border-yellow-100 rounded-xl">
					<h3 class="font-medium text-yellow-800">Custom Orders</h3>
					<p class="text-sm text-yellow-700 mt-1">Your custom order requests will be reviewed by our team. We'll contact you with pricing details and confirm availability.</p>
				</div>
			</div>

			<!-- Back Order Notice -->
			<div class="mt-3" x-show="cart.items.some(it => it.is_backorder)">
				<div class="p-4 bg-blue-50 border border-blue-100 rounded-xl">
					<h3 class="font-medium text-blue-800">Back Order Items</h3>
					<p class="mt-1 text-sm text-blue-700">Your cart contains back ordered items. These will be shipped as soon as they're back in stock.</p>
				</div>
			</div>

			<div class="mt-3 space-y-4">
				<template x-for="it in cart.items" :key="it.cart_item_id">
					<div class="grid grid-cols-12 items-center bg-white border border-gray-100 rounded-xl shadow-sm p-4" :class="{'border-blue-200': it.is_backorder}">
						<!-- Product -->
						<div class="col-span-12 md:col-span-6 flex items-center gap-4">
							<div class="w-20 h-20 rounded-md bg-gray-100 overflow-hidden flex items-center justify-center">
								<img :src="it.photo" alt="" class="w-full h-full object-cover"/>
							</div>
                            <div>
								<p class="text-sm font-medium text-gray-900" x-text="it.type === 'custom' ? it.custom_name : it.name"></p>
                                <template x-if="it.is_backorder">
                                    <span class="inline-flex mt-1 ml-1 px-2 py-0.5 text-[11px] rounded bg-blue-100 text-blue-800">Back-Order</span>
                                </template>
                                <template x-if="it.is_backorder && it.restock_date">
                                    <div class="text-[11px] text-blue-700">Ships after <span x-text="it.restock_date"></span></div>
                                </template>
								<button @click="if(it.type === 'custom'){ fetch('/api/v1/cart/custom/'+it.cart_item_id+'/remove',{method:'POST',credentials:'same-origin',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(r=>r.json()).then(d=>cart=d) } else { fetch('/api/v1/cart/'+it.cart_item_id+'/remove',{method:'POST',credentials:'same-origin',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(r=>r.json()).then(d=>cart=d) }" class="text-xs text-red-600 hover:underline mt-1">Remove</button>
							</div>
						</div>
						<!-- Price -->
                        	<div class="col-span-6 md:col-span-2 text-right md:text-right mt-3 md:mt-0">
                        		<span class="text-sm text-gray-700">₱<span x-text="it.type === 'custom' ? 'TBD' : Number(it.price).toFixed(2)"></span></span>
                        	</div>
						<!-- Qty -->
						<div class="col-span-6 md:col-span-2 mt-3 md:mt-0">
							<div class="flex items-center justify-center">
								<div class="inline-flex items-center border border-gray-300 rounded-md overflow-hidden">
									<button @click="(function(){ const q=Math.max(1,(it.quantity-1)); if(it.type === 'custom'){ fetch('/api/v1/cart/custom/'+it.cart_item_id+'/quantity',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({quantity:q})}).then(r=>r.json()).then(d=>cart=d); } else { fetch('/api/v1/cart/'+it.cart_item_id+'/quantity',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({quantity:q})}).then(r=>r.json()).then(d=>cart=d); } })()" class="px-3 py-2 text-gray-600 hover:bg-gray-50">-</button>
									<span class="w-10 text-center text-sm" x-text="it.quantity"></span>
									<button @click="(function(){ const q=(it.quantity+1); if(it.type === 'custom'){ fetch('/api/v1/cart/custom/'+it.cart_item_id+'/quantity',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({quantity:q})}).then(r=>r.json()).then(d=>cart=d); } else { fetch('/api/v1/cart/'+it.cart_item_id+'/quantity',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({quantity:q})}).then(r=>r.json()).then(d=>cart=d); } })()" class="px-3 py-2 text-gray-600 hover:bg-gray-50">+</button>
								</div>
							</div>
						</div>
						<!-- Total -->
                        	<div class="col-span-6 md:col-span-2 text-right mt-3 md:mt-0">
                        		<span class="text-sm text-gray-900 font-medium">₱<span x-text="it.type === 'custom' ? 'TBD' : Number(it.subtotal).toFixed(2)"></span></span>
                        	</div>
					</div>
				</template>
			</div>

			<!-- Summary -->
			<div class="mt-8 flex flex-col md:flex-row md:items-center md:justify-end gap-4">
                @if (session('success'))
                    <div class="text-sm text-green-700">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="text-sm text-red-600">{{ session('error') }}</div>
                @endif
                <div class="text-xs text-gray-500">Tax and shipping cost will be calculated later.</div>
				<div class="bg-white border border-gray-100 rounded-xl shadow-sm p-4 w-full md:w-auto">
					<div class="flex items-center justify-between gap-8">
						<span class="text-sm text-gray-600">Sub-total</span>
						<span class="text-base font-semibold">₱<span x-text="Number(cart.subtotal).toFixed(2)"></span></span>
					</div>
					<div class="mt-4 text-right">
						<a href="<?php echo e(route('checkout.page')); ?>" class="inline-flex items-center justify-center px-6 py-3 rounded-md text-white font-medium shadow-sm hover:shadow transition" style="background:#c59d5f;">Check-out</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Footer -->
	<footer class="bg-[#1a1a1a] text-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="border-t" style="border-color:#c59d5f"></div>
			<div class="py-12 grid grid-cols-1 md:grid-cols-2 gap-10">
				<div>
					<h4 class="text-lg font-semibold mb-4">About</h4>
					<ul class="space-y-2 text-gray-300">
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">Help</a></li>
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">Shipping</a></li>
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">FAQ</a></li>
					</ul>
				</div>
				<div>
					<h4 class="text-lg font-semibold mb-4">Info</h4>
					<ul class="space-y-2 text-gray-300">
						<li><a href="/contact" class="hover:text-[#c59d5f] transition-colors">Contact Us</a></li>
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">Privacy Policy</a></li>
						<li><a href="#" class="hover:text-[#c59d5f] transition-colors">Terms &amp; Conditions</a></li>
					</ul>
				</div>
			</div>
		</div>
	</footer>

</body>
</html>


