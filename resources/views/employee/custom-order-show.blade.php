<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Employee - Review Custom Order</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<style>[x-cloak]{ display: none !important; }</style>
</head>
<body class="bg-gray-50" x-data="{ dropdownOpen: false }">

<div class="flex min-h-screen bg-gray-50">
	<!-- Sidebar -->
	<div class="w-64 bg-[#c49b6e] flex flex-col shadow-lg">
		<div class="p-6 border-b border-[#b08a5c]">
			<span class="text-white font-semibold text-lg">Wow Carmen</span>
		</div>
		<nav class="flex-1 p-4 space-y-1">
			<a href="{{ route('dashboard') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('dashboard') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
				<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
					<path d="M10 2l8 6v9a1 1 0 01-1 1h-5v-5H8v5H3a1 1 0 01-1-1V8l8-6z"></path>
				</svg>
				<span class="font-medium">Dashboard</span>
			</a>
			<a href="{{ route('employee.raw-materials') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.raw-materials') || request()->routeIs('employee.materials.*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
				<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
					<path d="M4 3h12a1 1 0 011 1v3H3V4a1 1 0 011-1z"></path>
					<path d="M3 8h14v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"></path>
				</svg>
				<span class="font-medium">Raw Materials</span>
			</a>
			<a href="{{ route('employee.items') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.items*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
				<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
					<path d="M6 3a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V4a1 1 0 00-1-1H6z"></path>
				</svg>
				<span class="font-medium">Production</span>
			</a>
			<a href="{{ route('employee.orders') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.orders*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
				<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
					<path d="M3 3h14a1 1 0 011 1v3H2V4a1 1 0 011-1z"></path>
					<path d="M2 8h16v8a1 1 0 01-1 1H3a1 1 0 01-1-1V8z"></path>
				</svg>
				<span class="font-medium">Orders</span>
			</a>
			<a href="{{ route('employee.reports') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.reports*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
				<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
					<path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
				</svg>
				<span class="font-medium">Reports</span>
			</a>
		</nav>
	</div>

	<!-- Main Content -->
	<div class="flex-1 bg-white">
		<!-- Header -->
		<div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
			<h1 class="text-2xl font-semibold text-gray-800">Custom Order Review</h1>
			<div class="relative">
				<button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800">
					<span>Hello, {{ Auth::user()->name ?? 'Employee' }}</span>
					<div class="w-8 h-8 bg-gray-300 rounded-full"></div>
				</button>
				<div x-show="dropdownOpen" x-cloak x-transition @click.outside="dropdownOpen=false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
					<a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
						Profile
					</a>
					<form action="{{ route('logout') }}" method="POST">
						@csrf
						<button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">Log Out</button>
					</form>
				</div>
			</div>
		</div>

		<!-- Page Content -->
		<div class="p-6">

			<div class="mt-4 bg-white border border-gray-100 rounded-xl shadow-sm p-6 md:p-8">
				@if (session('success'))
					<div class="mb-4 p-3 rounded bg-green-50 border border-green-100 text-green-700 text-sm">{{ session('success') }}</div>
				@endif

				<h1 class="text-2xl font-bold text-gray-900">Custom Order Review</h1>
				<p class="text-sm text-gray-500 mt-1">Status: <span class="font-medium">{{ ucfirst(str_replace('_',' ', $customOrder->status)) }}</span></p>

				<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
					<div class="md:col-span-2 space-y-6">
						<div>
							<h2 class="text-lg font-semibold text-gray-900">Submitted Details</h2>
							<dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
								<div>
									<dt class="text-gray-500">Customer</dt>
									<dd class="text-gray-900">{{ $customOrder->order->user->name ?? 'N/A' }}</dd>
								</div>
								<div>
									<dt class="text-gray-500">Product Name</dt>
									<dd class="text-gray-900">{{ $customOrder->custom_name }}</dd>
								</div>
								<div>
									<dt class="text-gray-500">Status</dt>
									<dd class="text-gray-900">
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
											{{ str_replace('_',' ', ucfirst($customOrder->status)) }}
										</span>
									</dd>
								</div>
								<div class="sm:col-span-2">
									<dt class="text-gray-500">Description</dt>
									<dd class="text-gray-900">{{ $customOrder->description }}</dd>
								</div>
								<div>
									<dt class="text-gray-500">Quantity</dt>
									<dd class="text-gray-900">{{ $customOrder->quantity }}</dd>
								</div>
								<div>
									<dt class="text-gray-500">Current Price</dt>
									<dd class="text-gray-900">
										@if(!is_null($customOrder->price_estimate))
											₱{{ number_format((float)$customOrder->price_estimate, 2) }}
										@else
											—
										@endif
									</dd>
								</div>
								<div>
									<dt class="text-gray-500">Estimated Completion</dt>
									<dd class="text-gray-900">{{ optional($customOrder->estimated_completion_date)->format('M d, Y') ?? '—' }}</dd>
								</div>
								@if($customOrder->admin_notes)
									<div class="sm:col-span-2">
										<dt class="text-gray-500">Internal Notes</dt>
										<dd class="text-gray-900 whitespace-pre-line">{{ $customOrder->admin_notes }}</dd>
									</div>
								@endif
							</dl>
						</div>

						<div>
							<h2 class="text-lg font-semibold text-gray-900">Reference Images</h2>
							<div class="mt-3">
								@php
									$images = data_get($customOrder->customization_details, 'images', []);
									// Fallback to single image for backward compatibility
									if (empty($images) && $customOrder->reference_image_path) {
										$images = [$customOrder->reference_image_path];
									}
								@endphp
								@if(!empty($images))
									<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
										@foreach($images as $imagePath)
											<div class="relative">
												<img src="{{ \Illuminate\Support\Facades\Storage::url($imagePath) }}" alt="Reference Image {{ $loop->iteration }}" class="w-full h-auto max-h-80 rounded border border-gray-200 shadow-sm object-cover">
											</div>
										@endforeach
									</div>
								@else
									<p class="text-sm text-gray-500">No images provided.</p>
								@endif
							</div>
						</div>
					</div>

					<div>
						<h2 class="text-lg font-semibold text-gray-900">Review</h2>
						
						@if($customOrder->status === \App\Models\CustomOrder::STATUS_PENDING_REVIEW)
							<!-- Accept Order Form -->
							<form method="POST" action="{{ route('employee.custom-orders.accept', $customOrder->id) }}" class="mt-3 space-y-4 border-b border-gray-200 pb-6">
								@csrf
								<h3 class="text-md font-semibold text-green-700 mb-3">Accept Order</h3>
								
								<div>
									<label for="accept_price" class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
									<input type="number" step="0.01" min="0" id="accept_price" name="price_estimate" value="{{ old('price_estimate', $customOrder->price_estimate) }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
									@error('price_estimate')
										<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
									@enderror
								</div>

								<div>
									<label for="accept_completion_date" class="block text-sm font-medium text-gray-700">Expected Completion Date <span class="text-red-500">*</span></label>
									<input type="date" id="accept_completion_date" name="estimated_completion_date" value="{{ old('estimated_completion_date', optional($customOrder->estimated_completion_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
									@error('estimated_completion_date')
										<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
									@enderror
								</div>

								<div>
									<label for="accept_admin_notes" class="block text-sm font-medium text-gray-700">Internal Notes (Optional)</label>
									<textarea id="accept_admin_notes" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('admin_notes', $customOrder->admin_notes) }}</textarea>
								</div>

								<button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95" style="background:#2f855a;">
									✓ Accept Order
								</button>
								<p class="text-xs text-gray-500 mt-1">Status will update to "Accepted / Pending Payment"</p>
							</form>

							<!-- Reject Order Form -->
							<form method="POST" action="{{ route('employee.custom-orders.reject', $customOrder->id) }}" class="mt-6 space-y-4">
								@csrf
								<h3 class="text-md font-semibold text-red-700 mb-3">Reject Order</h3>
								
								<div>
									<label for="rejection_note" class="block text-sm font-medium text-gray-700">Rejection Reason <span class="text-red-500">*</span></label>
									<textarea id="rejection_note" name="rejection_note" rows="4" class="mt-1 block w-full rounded-md border-gray-300 focus:border-red-500 focus:ring-red-500" placeholder="Please explain why this order is being rejected..." required>{{ old('rejection_note', $customOrder->rejection_note) }}</textarea>
									<p class="mt-1 text-xs text-gray-500">This note will be visible to the customer.</p>
									@error('rejection_note')
										<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
									@enderror
								</div>

								<button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95 bg-red-600 hover:bg-red-700">
									✗ Reject Order
								</button>
								<p class="text-xs text-gray-500 mt-1">Status will update to "Rejected"</p>
							</form>
						@elseif($customOrder->status === \App\Models\CustomOrder::STATUS_APPROVED)
							<div class="mt-3 p-4 bg-green-50 border border-green-200 rounded-md">
								<p class="text-sm text-green-800 font-medium">✓ Order Accepted</p>
								<p class="text-xs text-green-700 mt-1">This order has been accepted and is awaiting payment.</p>
							</div>
						@elseif($customOrder->status === \App\Models\CustomOrder::STATUS_REJECTED)
							<div class="mt-3 p-4 bg-red-50 border border-red-200 rounded-md">
								<p class="text-sm text-red-800 font-medium">✗ Order Rejected</p>
								@if($customOrder->rejection_note)
									<p class="text-xs text-red-700 mt-2"><strong>Reason:</strong> {{ $customOrder->rejection_note }}</p>
								@endif
							</div>
						@endif

						<!-- Legacy forms for backward compatibility (only show if not pending_review) -->
						@if($customOrder->status !== \App\Models\CustomOrder::STATUS_PENDING_REVIEW)
							<form method="POST" action="{{ route('employee.custom-orders.update', $customOrder->id) }}" class="mt-6 space-y-4 border-t border-gray-200 pt-6">
								@csrf
								@method('PUT')

								<div>
									<label for="price_estimate" class="block text-sm font-medium text-gray-700">Final Price</label>
									<input type="number" step="0.01" min="0" id="price_estimate" name="price_estimate" value="{{ old('price_estimate', $customOrder->price_estimate) }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
									@error('price_estimate')
										<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
									@enderror
								</div>

								<div>
									<label for="admin_notes" class="block text-sm font-medium text-gray-700">Internal Notes</label>
									<textarea id="admin_notes" name="admin_notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('admin_notes', $customOrder->admin_notes) }}</textarea>
									@error('admin_notes')
										<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
									@enderror
								</div>

								<button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95" style="background:#c59d5f;">
									Save Review (Keep Pending)
								</button>
							</form>

							<form method="POST" action="{{ route('employee.custom-orders.confirm', $customOrder->id) }}" class="mt-6 space-y-4 border-t border-gray-200 pt-6">
								@csrf
								@method('PUT')

								<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
									<div>
										<label for="confirm_price" class="block text-sm font-medium text-gray-700">Confirmed Price</label>
										<input type="number" step="0.01" min="0" id="confirm_price" name="price_estimate" value="{{ old('price_estimate', $customOrder->price_estimate) }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
										@error('price_estimate')
											<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
										@enderror
									</div>
									<div>
										<label for="estimated_completion_date" class="block text-sm font-medium text-gray-700">Estimated Completion Date</label>
										<input type="date" id="estimated_completion_date" name="estimated_completion_date" value="{{ old('estimated_completion_date', optional($customOrder->estimated_completion_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]" required>
										@error('estimated_completion_date')
											<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
										@enderror
									</div>
								</div>

								<div>
									<label for="confirm_admin_notes" class="block text-sm font-medium text-gray-700">Internal Notes (Optional)</label>
									<textarea id="confirm_admin_notes" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#c59d5f] focus:ring-[#c59d5f]">{{ old('admin_notes', $customOrder->admin_notes) }}</textarea>
								</div>

								<button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-white font-semibold shadow-sm hover:opacity-95" style="background:#2f855a;">
									Confirm &amp; Start Production
								</button>
								<p class="text-xs text-gray-500">Sets status to In Progress and notifies dashboards.</p>
							</form>
						@endif
					</div>
				</div>
				</div>
			</div>
		</div>
	</div>
</div>

</body>
</html>
