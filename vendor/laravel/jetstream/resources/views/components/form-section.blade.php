@props(['submit'])

<div {{ $attributes->merge(['class' => '']) }}>

        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>


    <div class="">
        <form wire:submit.prevent="{{ $submit }}">
            <div class="">
                <div class="">
                    <div class="">
                        {{ $form }}
                    </div>
                </div>

                @if (isset($actions))
                    <div class="">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>
