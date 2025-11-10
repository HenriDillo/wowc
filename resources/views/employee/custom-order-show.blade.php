<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Employee - Review Custom Order</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui;">

	<nav class="bg-white border-b border-gray-200">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="h-16 flex items-center justify-between">
				<div class="flex items-center">
					<img src="/images/logo.png" alt="WOW Carmen" class="w-8 h-8 mr-3"/>
					<a href="{{ route('dashboard') }}" class="text-xl font-semibold text-gray-900">WOW Carmen</a>
				</div>
				<div class="hidden md:flex items-center space-x-6">
					<a href="{{ route('employee.items') }}" class="text-sm text-gray-700 hover:text-[#c59d5f]">Items</a>
					<a href="{{ route('employee.orders') }}" class="text-sm text-gray-700 hover:text-[#c59d5f]">Orders</a>
				</div>
			</div>
		</div>
	</nav>

	<section class="py-8">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<a href="{{ route('employee.orders') }}" class="text-sm text-[#c59d5f] hover:underline">Back to Orders</a>

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
									<dt class="text-gray-500">Dimensions</dt>
									<dd class="text-gray-900">
										{{ data_get($customOrder->customization_details, 'dimensions') ?: '—' }}
									</dd>
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
							<h2 class="text-lg font-semibold text-gray-900">Reference Image</h2>
							<div class="mt-3">
								@if ($customOrder->reference_image_path)
									<img src="{{ \Illuminate\Support\Facades\Storage::url($customOrder->reference_image_path) }}" alt="Reference" class="max-h-80 rounded border border-gray-200">
								@else
									<p class="text-sm text-gray-500">No image provided.</p>
								@endif
							</div>
						</div>
					</div>

					<div>
						<h2 class="text-lg font-semibold text-gray-900">Review</h2>
                        <form method="POST" action="{{ route('employee.custom-orders.update', $customOrder->id) }}" class="mt-3 space-y-4">
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
					</div>
				</div>
			</div>
		</div>
	</section>

</body>
</html>

