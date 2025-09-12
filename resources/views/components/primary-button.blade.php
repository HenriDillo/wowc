<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#c49b6e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#b08a5c] focus:bg-[#b08a5c] active:bg-[#9f7a52] focus:outline-none focus:ring-2 focus:ring-[#c49b6e] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
