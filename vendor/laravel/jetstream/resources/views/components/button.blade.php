<button {{ $attributes->merge(['type' => 'submit', 'style' => 'float:right; margin-top:10px;', 'class' => 'btn btn-success ']) }}>
    {{ $slot }}
</button>
