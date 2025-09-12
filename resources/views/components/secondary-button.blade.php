<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-[#c49b6e] rounded-md font-semibold text-xs text-[#6b4f2f] uppercase tracking-widest shadow-sm hover:bg-[#fff8f0] focus:outline-none focus:ring-2 focus:ring-[#c49b6e] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
