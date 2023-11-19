@if($authenticated)@component('scribe::components.badges.base', ['colour' => "darkred", 'text' => '인증 필요'])
@endcomponent
@endif
