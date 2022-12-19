@props(['for'])

@error($for)
    <p {{ $attributes->merge(['style' => 'color:red;']) }}>{{ $message }}</p>
@enderror
